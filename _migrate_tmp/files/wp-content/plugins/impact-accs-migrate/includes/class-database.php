<?php
/**
 * Database export / import.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SQL dump via $wpdb (no shell required).
 */
class IASM_Database {

	/**
	 * @return array<int,string>
	 */
	public static function table_list() {
		global $wpdb;
		$tables = $wpdb->get_col( 'SHOW TABLES', 0 );
		if ( ! is_array( $tables ) ) {
			return array();
		}
		$prefix = $wpdb->prefix;
		return array_values(
			array_filter(
				$tables,
				static function ( $table ) use ( $prefix ) {
					return 0 === strpos( (string) $table, $prefix );
				}
			)
		);
	}

	/**
	 * Export all WP tables to SQL file.
	 *
	 * @param string $dest_file Destination .sql path.
	 * @return true|WP_Error
	 */
	public static function export_to_file( $dest_file ) {
		global $wpdb;

		$handle = fopen( $dest_file, 'wb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $handle ) {
			return new WP_Error( 'iasm_sql_open', 'Не удалось создать database.sql.' );
		}

		$header = "-- Impact Site Migrate\n"
			. '-- Generated: ' . gmdate( 'c' ) . "\n"
			. '-- Site: ' . home_url( '/' ) . "\n"
			. "SET NAMES utf8mb4;\n"
			. "SET foreign_key_checks = 0;\n\n";
		fwrite( $handle, $header ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

		foreach ( self::table_list() as $table ) {
			$create = $wpdb->get_row( "SHOW CREATE TABLE `{$table}`", ARRAY_N ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( empty( $create[1] ) ) {
				continue;
			}
			fwrite( $handle, "DROP TABLE IF EXISTS `{$table}`;\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			fwrite( $handle, $create[1] . ";\n\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

			$offset = 0;
			$batch  = 200;
			do {
				$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` LIMIT %d OFFSET %d", $batch, $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				if ( empty( $rows ) ) {
					break;
				}
				foreach ( $rows as $row ) {
					$values = array();
				foreach ( $row as $value ) {
					if ( null === $value ) {
						$values[] = 'NULL';
					} else {
						$values[] = "'" . $wpdb->_real_escape( (string) $value ) . "'";
					}
				}
					$sql = 'INSERT INTO `' . $table . '` VALUES (' . implode( ',', $values ) . ");\n";
					fwrite( $handle, $sql ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				}
				$offset += $batch;
			} while ( count( $rows ) === $batch );
			fwrite( $handle, "\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		}

		fwrite( $handle, "SET foreign_key_checks = 1;\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return true;
	}

	/**
	 * Import SQL file (statement by statement).
	 *
	 * @param string $file SQL file path.
	 * @return true|WP_Error
	 */
	public static function import_from_file( $file ) {
		global $wpdb;

		if ( ! is_readable( $file ) ) {
			return new WP_Error( 'iasm_sql_missing', 'database.sql не найден.' );
		}

		$wpdb->query( 'SET foreign_key_checks = 0' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$handle = fopen( $file, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $handle ) {
			return new WP_Error( 'iasm_sql_read', 'Не удалось прочитать database.sql.' );
		}

		$buffer = '';
		while ( ! feof( $handle ) ) {
			$line = fgets( $handle );
			if ( false === $line ) {
				break;
			}
			$trim = trim( $line );
			if ( '' === $trim || 0 === strpos( $trim, '--' ) ) {
				continue;
			}
			$buffer .= $line;
			if ( ';' !== substr( rtrim( $line ), -1 ) ) {
				continue;
			}
			$wpdb->query( $buffer ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
			$buffer = '';
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		$wpdb->query( 'SET foreign_key_checks = 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return true;
	}
}
