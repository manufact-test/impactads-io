<?php
/**
 * Backup snapshots for restore.
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup helper.
 */
class IACCE_Backup {

	/**
	 * Create JSON snapshot of editor state + plugin file checksums.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function create_snapshot( $label = 'manual' ) {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'iacce_upload', $upload['error'] );
		}

		$dir = trailingslashit( $upload['basedir'] ) . 'iacce-backups';
		if ( ! wp_mkdir_p( $dir ) ) {
			return new WP_Error( 'iacce_dir', 'Cannot create backup directory.' );
		}

		$id   = gmdate( 'Y-m-d_H-i-s' ) . '_' . sanitize_key( $label );
		$data = array(
			'id'        => $id,
			'label'     => $label,
			'created'   => gmdate( 'c' ),
			'version'   => IACCE_VERSION,
			'overrides' => get_option( IACCE_OPTION_OVERRIDES, array() ),
			'links'     => get_option( IACCE_OPTION_LINKS, array() ),
			'registry'  => get_option( IACCE_OPTION_REGISTRY, array() ),
			'plugins'   => self::plugin_checksums(),
		);

		$file = $dir . '/' . $id . '.json';
		$ok   = file_put_contents( $file, wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_put_contents_file_put_contents
		if ( false === $ok ) {
			return new WP_Error( 'iacce_write', 'Cannot write backup file.' );
		}

		$index   = get_option( IACCE_OPTION_BACKUPS, array() );
		$index[] = array(
			'id'      => $id,
			'label'   => $label,
			'created' => $data['created'],
			'file'    => $file,
		);
		update_option( IACCE_OPTION_BACKUPS, $index, false );

		return $data;
	}

	/**
	 * Restore snapshot by id.
	 *
	 * @param string $id Backup id.
	 * @return true|WP_Error
	 */
	public static function restore_snapshot( $id ) {
		$id = sanitize_file_name( $id );
		$upload = wp_upload_dir();
		$file   = trailingslashit( $upload['basedir'] ) . 'iacce-backups/' . $id . '.json';

		if ( ! is_readable( $file ) ) {
			return new WP_Error( 'iacce_missing', 'Backup not found.' );
		}

		$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'iacce_invalid', 'Invalid backup file.' );
		}

		IACCE_Backup::create_snapshot( 'before-restore-' . $id );

		if ( isset( $data['overrides'] ) && is_array( $data['overrides'] ) ) {
			update_option( IACCE_OPTION_OVERRIDES, $data['overrides'], false );
		}
		if ( isset( $data['links'] ) && is_array( $data['links'] ) ) {
			update_option( IACCE_OPTION_LINKS, $data['links'], false );
		}
		if ( isset( $data['registry'] ) && is_array( $data['registry'] ) ) {
			update_option( IACCE_OPTION_REGISTRY, $data['registry'], false );
		}

		return true;
	}

	/**
	 * @return array<int,array<string,string>>
	 */
	public static function list_snapshots() {
		$index = get_option( IACCE_OPTION_BACKUPS, array() );
		return is_array( $index ) ? $index : array();
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private static function plugin_checksums() {
		$plugins = array( 'impact-accs-chrome', 'impact-accs-homepage', 'impact-accs-preloader' );
		$out     = array();

		foreach ( $plugins as $slug ) {
			$main = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
			if ( ! is_readable( $main ) ) {
				$alt = glob( WP_PLUGIN_DIR . '/' . $slug . '/*.php' );
				$main = is_array( $alt ) && ! empty( $alt[0] ) ? $alt[0] : '';
			}
			if ( $main && is_readable( $main ) ) {
				$out[ $slug ] = array(
					'main'     => $main,
					'checksum' => md5_file( $main ),
					'size'     => (string) filesize( $main ),
				);
			}
		}

		return $out;
	}
}
