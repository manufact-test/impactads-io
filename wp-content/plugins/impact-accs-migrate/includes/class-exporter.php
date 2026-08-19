<?php
/**
 * Site export orchestrator.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build migration package.
 */
class IASM_Exporter {

	const OPTION_JOB = 'iasm_export_job';

	/**
	 * Start new export job.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public static function start() {
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		$job_id = 'export-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false );
		if ( ! IASM_Package::ensure_job_dir( $job_id ) ) {
			return new WP_Error( 'iasm_job', 'Не удалось создать рабочую папку.' );
		}

		$manifest = array(
			'version'      => IASM_VERSION,
			'created'      => gmdate( 'c' ),
			'site_url'     => home_url( '/' ),
			'home_url'     => get_option( 'home' ),
			'site_url_raw' => untrailingslashit( home_url() ),
			'wp_version'   => get_bloginfo( 'version' ),
			'php_version'  => PHP_VERSION,
			'table_prefix' => $GLOBALS['wpdb']->prefix,
			'charset'      => get_option( 'blog_charset' ),
			'language'     => get_locale(),
			'active_theme' => get_stylesheet(),
		);

		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			IASM_Package::manifest_path( $job_id ),
			wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
		);

		$sql = IASM_Package::job_dir( $job_id ) . 'database.sql';
		$db  = IASM_Database::export_to_file( $sql );
		if ( is_wp_error( $db ) ) {
			IASM_Package::cleanup_job( $job_id );
			return $db;
		}

		$files = IASM_Files::stage_wp_content( $job_id );
		if ( is_wp_error( $files ) ) {
			IASM_Package::cleanup_job( $job_id );
			return $files;
		}

		$zip = IASM_Package::build_archive( $job_id );
		if ( is_wp_error( $zip ) ) {
			return $zip;
		}

		$path = IASM_Package::archive_path( $job_id );
		$job  = array(
			'id'       => $job_id,
			'path'     => $path,
			'size'     => file_exists( $path ) ? filesize( $path ) : 0,
			'manifest' => $manifest,
			'files'    => $files,
			'done'     => true,
		);

		update_option( self::OPTION_JOB, $job, false );

		return $job;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function last_job() {
		$job = get_option( self::OPTION_JOB, array() );
		return is_array( $job ) ? $job : array();
	}
}
