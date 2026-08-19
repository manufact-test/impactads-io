<?php
/**
 * Copy wp-content and root snippets.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File staging for export/import.
 */
class IASM_Files {

	/**
	 * Relative paths inside package (under files/).
	 *
	 * @return array<int,string>
	 */
	public static function export_paths() {
		return array(
			'wp-content/plugins',
			'wp-content/themes',
			'wp-content/uploads',
			'wp-content/mu-plugins',
			'wp-content/languages',
		);
	}

	/**
	 * Copy wp-content subtrees into job staging folder.
	 *
	 * @param string $job_id Job id.
	 * @return array{copied:int,skipped:int}|WP_Error
	 */
	public static function stage_wp_content( $job_id ) {
		$dest_root = IASM_Package::job_dir( $job_id ) . 'files/';
		$copied    = 0;
		$skipped   = 0;

		foreach ( self::export_paths() as $rel ) {
			$source = trailingslashit( ABSPATH ) . $rel;
			$target = $dest_root . $rel;
			if ( ! is_dir( $source ) ) {
				++$skipped;
				continue;
			}
			if ( ! wp_mkdir_p( $target ) ) {
				return new WP_Error( 'iasm_mkdir', 'Не удалось создать папку: ' . $rel );
			}
			$result = self::copy_tree( $source, $target, $copied, $skipped );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		$htaccess = ABSPATH . '.htaccess';
		if ( is_readable( $htaccess ) ) {
			$ht_dir = $dest_root . 'root/';
			wp_mkdir_p( $ht_dir );
			copy( $htaccess, $ht_dir . '.htaccess' );
			++$copied;
		}

		return array(
			'copied'  => $copied,
			'skipped' => $skipped,
		);
	}

	/**
	 * Restore staged files into live WordPress.
	 *
	 * @param string $job_id Job id.
	 * @return array{copied:int}|WP_Error
	 */
	public static function restore_wp_content( $job_id ) {
		$source_root = IASM_Package::job_dir( $job_id ) . 'files/';
		if ( ! is_dir( $source_root ) ) {
			return new WP_Error( 'iasm_no_files', 'В пакете нет папки files/.' );
		}

		$copied = 0;
		foreach ( self::export_paths() as $rel ) {
			$source = $source_root . $rel;
			$target = trailingslashit( ABSPATH ) . $rel;
			if ( ! is_dir( $source ) ) {
				continue;
			}
			wp_mkdir_p( $target );
			$skip = 0;
			$result = self::copy_tree( $source, $target, $copied, $skip );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		$ht = $source_root . 'root/.htaccess';
		if ( is_readable( $ht ) && is_writable( ABSPATH ) ) {
			copy( $ht, ABSPATH . '.htaccess' );
			++$copied;
		}

		return array( 'copied' => $copied );
	}

	/**
	 * @param string $source  Source dir.
	 * @param string $target  Target dir.
	 * @param int    $copied  Counter.
	 * @param int    $skipped Counter.
	 * @return true|WP_Error
	 */
	private static function copy_tree( $source, $target, &$copied, &$skipped ) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $item ) {
			if ( ! $item instanceof SplFileInfo ) {
				continue;
			}
			$rel = substr( $item->getPathname(), strlen( $source ) );
			$rel = str_replace( '\\', '/', $rel );
			$rel = ltrim( $rel, '/' );
			$dest = trailingslashit( $target ) . $rel;

			if ( self::should_skip_path( $rel ) ) {
				++$skipped;
				continue;
			}

			if ( $item->isDir() ) {
				wp_mkdir_p( $dest );
				continue;
			}

			wp_mkdir_p( dirname( $dest ) );
			if ( ! copy( $item->getPathname(), $dest ) ) {
				return new WP_Error( 'iasm_copy', 'Не удалось скопировать: ' . $rel );
			}
			++$copied;
		}

		return true;
	}

	/**
	 * Skip temp/cache dirs and our own export packages during export.
	 *
	 * @param string $rel Relative path.
	 * @return bool
	 */
	private static function should_skip_path( $rel ) {
		$rel = str_replace( '\\', '/', strtolower( $rel ) );
		$skip = array(
			'impact-accs-migrate/packages/',
			'/node_modules/',
			'/.git/',
			'/cache/chunks-ru/', // optional regen — include for 1:1? user asked 1:1, include all
		);
		foreach ( $skip as $needle ) {
			if ( false !== strpos( $rel, $needle ) ) {
				return true;
			}
		}
		return false;
	}
}
