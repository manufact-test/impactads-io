<?php
/**
 * Serialized-safe URL replacement.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace URLs in DB after migration.
 */
class IASM_Replace {

	/**
	 * @param string $from Old URL (no trailing slash).
	 * @param string $to   New URL (no trailing slash).
	 * @return array{rows:int,cells:int}
	 */
	public static function run( $from, $to ) {
		global $wpdb;

		$from = untrailingslashit( (string) $from );
		$to   = untrailingslashit( (string) $to );
		if ( '' === $from || '' === $to || $from === $to ) {
			return array(
				'rows'  => 0,
				'cells' => 0,
			);
		}

		$pairs = self::url_variants( $from, $to );
		$rows  = 0;
		$cells = 0;

		foreach ( IASM_Database::table_list() as $table ) {
			$columns = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}`", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( empty( $columns ) ) {
				continue;
			}
			$text_cols = array();
			foreach ( $columns as $col ) {
				$type = strtolower( (string) ( $col['Type'] ?? '' ) );
				if ( preg_match( '/char|text|blob/i', $type ) ) {
					$text_cols[] = $col['Field'];
				}
			}
			if ( empty( $text_cols ) ) {
				continue;
			}

			$pk = self::primary_key( $table );
			$offset = 0;
			$batch  = 100;
			do {
				$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` LIMIT %d OFFSET %d", $batch, $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				if ( empty( $items ) ) {
					break;
				}
				foreach ( $items as $row ) {
					$changed = false;
					$update  = array();
					foreach ( $text_cols as $col ) {
						if ( ! array_key_exists( $col, $row ) ) {
							continue;
						}
						$original = $row[ $col ];
						$new      = self::replace_value( $original, $pairs );
						if ( $new !== $original ) {
							$update[ $col ] = $new;
							++$cells;
							$changed = true;
						}
					}
					if ( $changed && $pk && isset( $row[ $pk ] ) ) {
						$wpdb->update( $table, $update, array( $pk => $row[ $pk ] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						++$rows;
					}
				}
				$offset += $batch;
			} while ( count( $items ) === $batch );
		}

		return array(
			'rows'  => $rows,
			'cells' => $cells,
		);
	}

	/**
	 * @param string $table Table.
	 * @return string
	 */
	private static function primary_key( $table ) {
		global $wpdb;
		$keys = $wpdb->get_results( "SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( empty( $keys[0]['Column_name'] ) ) {
			return '';
		}
		return (string) $keys[0]['Column_name'];
	}

	/**
	 * @param mixed                $value Value.
	 * @param array<string,string> $pairs Replace pairs.
	 * @return mixed
	 */
	private static function replace_value( $value, $pairs ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return $value;
		}
		if ( self::is_serialized( $value ) ) {
			$data = @unserialize( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			if ( false === $data && 'b:0;' !== $value ) {
				return self::str_replace_pairs( $value, $pairs );
			}
			$data = self::replace_recursive( $data, $pairs );
			return serialize( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		}
		return self::str_replace_pairs( $value, $pairs );
	}

	/**
	 * @param mixed                $data  Data.
	 * @param array<string,string> $pairs Pairs.
	 * @return mixed
	 */
	private static function replace_recursive( $data, $pairs ) {
		if ( is_string( $data ) ) {
			return self::str_replace_pairs( $data, $pairs );
		}
		if ( is_array( $data ) ) {
			foreach ( $data as $k => $v ) {
				$data[ $k ] = self::replace_recursive( $v, $pairs );
			}
			return $data;
		}
		if ( is_object( $data ) ) {
			foreach ( get_object_vars( $data ) as $k => $v ) {
				$data->$k = self::replace_recursive( $v, $pairs );
			}
			return $data;
		}
		return $data;
	}

	/**
	 * @param string               $value Value.
	 * @param array<string,string> $pairs Pairs.
	 * @return string
	 */
	private static function str_replace_pairs( $value, $pairs ) {
		uksort(
			$pairs,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);
		return str_replace( array_keys( $pairs ), array_values( $pairs ), $value );
	}

	/**
	 * @param string $from Old base URL.
	 * @param string $to   New base URL.
	 * @return array<string,string>
	 */
	private static function url_variants( $from, $to ) {
		$pairs = array();
		$froms = array( $from, trailingslashit( $from ) );
		$tos   = array( $to, trailingslashit( $to ) );

		foreach ( $froms as $i => $f ) {
			$t = $tos[ $i ];
			$pairs[ $f ] = $t;
			$pairs[ str_replace( 'https://', 'http://', $f ) ] = str_replace( 'https://', 'http://', $t );
			$pairs[ str_replace( 'http://', 'https://', $f ) ] = str_replace( 'http://', 'https://', $t );
			$pairs[ rawurlencode( $f ) ] = rawurlencode( $t );
			$pairs[ str_replace( '/', '\\/', $f ) ] = str_replace( '/', '\\/', $t );
		}

		return $pairs;
	}

	/**
	 * @param string $data Data.
	 * @return bool
	 */
	private static function is_serialized( $data ) {
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' === $data ) {
			return true;
		}
		if ( ! preg_match( '/^(a|O|s|i|d|b):/', $data ) ) {
			return false;
		}
		return @unserialize( $data ) !== false || 'b:0;' === $data; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
	}
}
