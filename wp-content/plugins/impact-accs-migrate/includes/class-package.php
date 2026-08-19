<?php
/**
 * ZIP package helpers.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Package archive operations.
 */
class IASM_Package {

	/**
	 * @param string $job_id Job id.
	 * @return string
	 */
	public static function job_dir( $job_id ) {
		return trailingslashit( IASM_PACKAGES_DIR . sanitize_file_name( $job_id ) );
	}

	/**
	 * @param string $job_id Job id.
	 * @return string
	 */
	public static function archive_path( $job_id ) {
		return IASM_PACKAGES_DIR . sanitize_file_name( $job_id ) . IASM_PACKAGE_EXT;
	}

	/**
	 * @param string $job_id Job id.
	 * @return string
	 */
	public static function manifest_path( $job_id ) {
		return self::job_dir( $job_id ) . 'manifest.json';
	}

	/**
	 * @param string $job_id Job id.
	 * @return bool
	 */
	public static function ensure_job_dir( $job_id ) {
		$dir = self::job_dir( $job_id );
		if ( is_dir( $dir ) ) {
			return true;
		}
		return wp_mkdir_p( $dir );
	}

	/**
	 * @param string $job_id Job id.
	 */
	public static function cleanup_job( $job_id ) {
		$dir = self::job_dir( $job_id );
		if ( is_dir( $dir ) ) {
			self::delete_dir( $dir );
		}
	}

	/**
	 * @param string $path Path.
	 */
	public static function delete_dir( $path ) {
		if ( ! is_dir( $path ) ) {
			return;
		}
		$items = scandir( $path );
		if ( ! is_array( $items ) ) {
			return;
		}
		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$full = $path . DIRECTORY_SEPARATOR . $item;
			if ( is_dir( $full ) ) {
				self::delete_dir( $full );
			} else {
				wp_delete_file( $full );
			}
		}
		rmdir( $path );
	}

	/**
	 * Build final ZIP from staged job folder.
	 *
	 * @param string $job_id Job id.
	 * @return true|WP_Error
	 */
	public static function build_archive( $job_id ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'iasm_no_zip', 'На сервере нет PHP ZipArchive. Обратитесь в поддержку хостинга.' );
		}

		$source = self::job_dir( $job_id );
		if ( ! is_dir( $source ) ) {
			return new WP_Error( 'iasm_missing_job', 'Папка экспорта не найдена.' );
		}

		$dest = self::archive_path( $job_id );
		if ( file_exists( $dest ) ) {
			wp_delete_file( $dest );
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $dest, ZipArchive::CREATE ) ) {
			return new WP_Error( 'iasm_zip_open', 'Не удалось создать архив.' );
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$base_len = strlen( $source );
		foreach ( $iterator as $file ) {
			if ( ! $file instanceof SplFileInfo ) {
				continue;
			}
			$local = substr( $file->getPathname(), $base_len );
			$local = str_replace( '\\', '/', $local );
			$local = ltrim( $local, '/' );
			if ( $file->isDir() ) {
				$zip->addEmptyDir( $local );
			} else {
				$zip->addFile( $file->getPathname(), $local );
			}
		}

		$zip->close();

		self::cleanup_job( $job_id );

		return true;
	}

	/**
	 * Extract uploaded archive to temp dir.
	 *
	 * @param string $zip_path Archive path.
	 * @param string $job_id   Job id.
	 * @return true|WP_Error
	 */
	public static function extract_archive( $zip_path, $job_id ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'iasm_no_zip', 'На сервере нет PHP ZipArchive.' );
		}
		if ( ! file_exists( $zip_path ) ) {
			return new WP_Error( 'iasm_missing_zip', 'Файл пакета не найден.' );
		}

		self::ensure_job_dir( $job_id );
		$dest = self::job_dir( $job_id );

		$zip = new ZipArchive();
		if ( true !== $zip->open( $zip_path ) ) {
			return new WP_Error( 'iasm_zip_open', 'Не удалось открыть архив.' );
		}
		$zip->extractTo( $dest );
		$zip->close();

		return true;
	}

	/**
	 * @param string $job_id Job id.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function read_manifest( $job_id ) {
		$file = self::manifest_path( $job_id );
		if ( ! is_readable( $file ) ) {
			return new WP_Error( 'iasm_no_manifest', 'manifest.json не найден в пакете.' );
		}
		$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'iasm_bad_manifest', 'manifest.json повреждён.' );
		}
		return $data;
	}
}
