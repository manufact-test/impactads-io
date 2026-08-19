<?php
/**
 * Site import orchestrator.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restore migration package.
 */
class IASM_Importer {

	const OPTION_JOB = 'iasm_import_job';

	/**
	 * Handle uploaded package.
	 *
	 * @param array<string,mixed> $file $_FILES item.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function upload( $file ) {
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@ini_set( 'memory_limit', '512M' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( empty( $file['tmp_name'] ) ) {
			$code = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
			if ( UPLOAD_ERR_NO_FILE === $code ) {
				return new WP_Error( 'iasm_upload', 'Файл не выбран.' );
			}
			return new WP_Error( 'iasm_upload', self::upload_error_message( $code ) );
		}

		if ( ! is_uploaded_file( $file['tmp_name'] ) && ! is_readable( $file['tmp_name'] ) ) {
			return new WP_Error( 'iasm_upload', 'Файл не загружен на сервер.' );
		}

		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		if ( ! preg_match( '/\.iamigrate\.zip$/i', $name ) && ! preg_match( '/\.zip$/i', $name ) ) {
			return new WP_Error( 'iasm_type', 'Нужен файл .iamigrate.zip (или .zip от этого плагина).' );
		}

		$job_id = 'import-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false );
		wp_mkdir_p( IASM_PACKAGES_DIR );
		$stored = IASM_PACKAGES_DIR . sanitize_file_name( $job_id ) . '.upload.zip';

		$moved = is_uploaded_file( $file['tmp_name'] )
			? move_uploaded_file( $file['tmp_name'], $stored )
			: copy( $file['tmp_name'], $stored );

		if ( ! $moved ) {
			return new WP_Error( 'iasm_move', 'Не удалось сохранить файл. Проверьте права на wp-content/plugins/impact-accs-migrate/packages/' );
		}

		return self::extract_job( $job_id, $stored );
	}

	/**
	 * Import package already on server (FTP → packages/).
	 *
	 * @param string $path Absolute path to zip.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function upload_from_path( $path ) {
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'iasm_upload', 'Файл на сервере не найден.' );
		}

		$job_id = 'import-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false );
		wp_mkdir_p( IASM_PACKAGES_DIR );
		$stored = IASM_PACKAGES_DIR . sanitize_file_name( $job_id ) . '.upload.zip';

		if ( ! copy( $path, $stored ) ) {
			return new WP_Error( 'iasm_move', 'Не удалось скопировать файл пакета.' );
		}

		return self::extract_job( $job_id, $stored );
	}

	/**
	 * @param string $job_id Job id.
	 * @param string $stored Zip path.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function extract_job( $job_id, $stored ) {
		$extract = IASM_Package::extract_archive( $stored, $job_id );
		wp_delete_file( $stored );
		if ( is_wp_error( $extract ) ) {
			IASM_Package::cleanup_job( $job_id );
			return $extract;
		}

		$manifest = IASM_Package::read_manifest( $job_id );
		if ( is_wp_error( $manifest ) ) {
			IASM_Package::cleanup_job( $job_id );
			return $manifest;
		}

		$job = array(
			'id'       => $job_id,
			'manifest' => $manifest,
			'ready'    => true,
		);
		update_option( self::OPTION_JOB, $job, false );

		return $job;
	}

	/**
	 * @param int $code PHP upload error code.
	 * @return string
	 */
	public static function upload_error_message( $code ) {
		$max = ini_get( 'upload_max_filesize' );
		$post = ini_get( 'post_max_size' );
		switch ( $code ) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return "Файл слишком большой для PHP (лимит upload: {$max}, post: {$post}). Залейте .zip через FTP — см. подсказку ниже.";
			case UPLOAD_ERR_PARTIAL:
				return 'Файл загрузился не полностью. Попробуйте ещё раз или залейте через FTP.';
			default:
				return 'Ошибка загрузки файла (код ' . $code . ').';
		}
	}

	/**
	 * .iamigrate.zip files sitting in packages/ (FTP upload).
	 *
	 * @return array<int,string>
	 */
	public static function server_packages() {
		$out = array();
		if ( ! is_dir( IASM_PACKAGES_DIR ) ) {
			return $out;
		}
		foreach ( glob( IASM_PACKAGES_DIR . '*' . IASM_PACKAGE_EXT ) as $path ) {
			if ( is_file( $path ) ) {
				$out[] = $path;
			}
		}
		foreach ( glob( IASM_PACKAGES_DIR . '*.iamigrate.zip' ) as $path ) {
			if ( is_file( $path ) && ! in_array( $path, $out, true ) ) {
				$out[] = $path;
			}
		}
		return $out;
	}

	/**
	 * Run import: files → DB → URL replace.
	 *
	 * @param string $old_url Optional override old URL.
	 * @param string $new_url Optional override new URL.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function run( $old_url = '', $new_url = '' ) {
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$job = get_option( self::OPTION_JOB, array() );
		if ( empty( $job['id'] ) || empty( $job['ready'] ) ) {
			return new WP_Error( 'iasm_no_job', 'Сначала загрузите файл пакета.' );
		}

		$job_id   = (string) $job['id'];
		$manifest = is_array( $job['manifest'] ?? null ) ? $job['manifest'] : array();

		$old = '' !== $old_url ? untrailingslashit( $old_url ) : untrailingslashit( (string) ( $manifest['site_url_raw'] ?? $manifest['site_url'] ?? '' ) );
		$new = '' !== $new_url ? untrailingslashit( $new_url ) : untrailingslashit( home_url() );

		$sql = IASM_Package::job_dir( $job_id ) . 'database.sql';
		if ( ! is_readable( $sql ) ) {
			return new WP_Error( 'iasm_sql', 'database.sql не найден в пакете.' );
		}

		$files = IASM_Files::restore_wp_content( $job_id );
		if ( is_wp_error( $files ) ) {
			return $files;
		}

		$db = IASM_Database::import_from_file( $sql );
		if ( is_wp_error( $db ) ) {
			return $db;
		}

		$replace = IASM_Replace::run( $old, $new );

		// Reconnect after import (options table replaced).
		wp_cache_flush();
		global $wpdb;
		$wpdb->db_connect();

		update_option( 'home', $new );
		update_option( 'siteurl', $new );

		flush_rewrite_rules( true );

		IASM_Package::cleanup_job( $job_id );
		delete_option( self::OPTION_JOB );

		return array(
			'old_url' => $old,
			'new_url' => $new,
			'files'   => $files,
			'replace' => $replace,
			'done'    => true,
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function pending_job() {
		$job = get_option( self::OPTION_JOB, array() );
		return is_array( $job ) ? $job : array();
	}
}
