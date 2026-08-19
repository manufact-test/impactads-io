<?php
/**
 * Server-side pull migration Timeweb 9tad1 -> impactads.io
 * Open once: /iac-pull-migrate.php?key=iac-migrate-impactads-2026
 */
declare( strict_types=1 );

header( 'Content-Type: text/plain; charset=utf-8' );
set_time_limit( 0 );
ini_set( 'memory_limit', '768M' );

$key = 'iac-migrate-impactads-2026';
if ( ( $_GET['key'] ?? '' ) !== $key ) {
	http_response_code( 403 );
	echo "Forbidden\n";
	exit;
}

$src_host = 'vh450.timeweb.ru';
$src_user = 'cu59725';
$src_pass = 'Loshararuslan44';
$src_root = '/wordpress_9tad1/public_html';
$dst_root = __DIR__;

$replacements = array(
	'https://cu59725-wordpress-zpsc4.tw1.ru' => 'https://impactads.io',
	'http://cu59725-wordpress-zpsc4.tw1.ru'  => 'https://impactads.io',
	'https://cu59725-wordpress-9tad1.tw1.ru' => 'https://impactads.io',
	'http://cu59725-wordpress-9tad1.tw1.ru'  => 'https://impactads.io',
);

$sync = array(
	'wp-admin',
	'wp-includes',
	'wp-content/plugins/impact-accs-chrome',
	'wp-content/plugins/impact-accs-homepage',
	'wp-content/plugins/impact-accs-preloader',
	'wp-content/plugins/impact-accs-content-editor',
	'wp-content/plugins/elementor',
	'wp-content/plugins/elementor-pro',
	'wp-content/themes/hello-elementor',
	'wp-content/uploads',
);

$core_files = array(
	'index.php', 'wp-activate.php', 'wp-blog-header.php', 'wp-comments-post.php',
	'wp-cron.php', 'wp-links-opml.php', 'wp-load.php', 'wp-login.php',
	'wp-mail.php', 'wp-settings.php', 'wp-signup.php', 'wp-trackback.php', 'xmlrpc.php',
);

function log_msg( string $msg ): void {
	echo $msg, "\n";
	@ob_flush();
	@flush();
}

function ftp_connect_src() {
	global $src_host, $src_user, $src_pass;
	$ftp = ftp_connect( $src_host, 21, 120 );
	if ( ! $ftp || ! ftp_login( $ftp, $src_user, $src_pass ) ) {
		throw new RuntimeException( 'Timeweb FTP login failed' );
	}
	ftp_pasv( $ftp, true );
	return $ftp;
}

function ensure_local_dir( string $dir ): void {
	if ( ! is_dir( $dir ) && ! mkdir( $dir, 0755, true ) && ! is_dir( $dir ) ) {
		throw new RuntimeException( "Cannot mkdir $dir" );
	}
}

function replace_urls( string $data, array $pairs ): string {
	foreach ( $pairs as $from => $to ) {
		$data = str_replace( $from, $to, $data );
	}
	return $data;
}

function ftp_list_recursive( $ftp, string $path ): array {
	$files = array();
	$list  = ftp_rawlist( $ftp, $path );
	if ( ! is_array( $list ) ) {
		return $files;
	}
	foreach ( $list as $line ) {
		$info = preg_split( '/\s+/', $line, 9 );
		if ( count( $info ) < 9 ) {
			continue;
		}
		$name = $info[8];
		if ( '.' === $name || '..' === $name ) {
			continue;
		}
		$full = rtrim( $path, '/' ) . '/' . $name;
		if ( 'd' === $info[0][0] ) {
			$files = array_merge( $files, ftp_list_recursive( $ftp, $full ) );
		} else {
			$files[] = $full;
		}
	}
	return $files;
}

try {
	log_msg( '=== Pull migration 9tad1 -> impactads.io ===' );

	$ctx = stream_context_create(
		array(
			'http' => array( 'timeout' => 900 ),
			'ssl'  => array( 'verify_peer' => false, 'verify_peer_name' => false ),
		)
	);
	$export_url = 'https://cu59725-wordpress-9tad1.tw1.ru/wp-content/plugins/impact-accs-chrome/iac-export-standalone.php?key=' . rawurlencode( $key );
	log_msg( 'Export SQL on Timeweb...' );
	$export_resp = @file_get_contents( $export_url, false, $ctx );
	log_msg( trim( (string) $export_resp ) );
	if ( false === $export_resp || ! str_contains( (string) $export_resp, 'DONE:' ) ) {
		throw new RuntimeException( 'SQL export failed' );
	}

	$ftp = ftp_connect_src();
	log_msg( 'Timeweb FTP connected' );

	foreach ( $core_files as $file ) {
		$local = $dst_root . '/' . $file;
		$fh    = fopen( $local, 'wb' );
		if ( ! ftp_fget( $ftp, $fh, $src_root . '/' . $file, FTP_BINARY ) ) {
			fclose( $fh );
			throw new RuntimeException( "Core file failed: $file" );
		}
		fclose( $fh );
	}
	log_msg( 'Core PHP files copied' );

	$count = 0;
	foreach ( $sync as $rel ) {
		$remote_dir = $src_root . '/' . $rel;
		$files      = ftp_list_recursive( $ftp, $remote_dir );
		log_msg( "$rel — " . count( $files ) . ' files' );
		foreach ( $files as $remote_file ) {
			$rel_file   = substr( $remote_file, strlen( $src_root ) + 1 );
			$local_file = $dst_root . '/' . $rel_file;
			ensure_local_dir( dirname( $local_file ) );
			$fh = fopen( $local_file, 'wb' );
			if ( ! ftp_fget( $ftp, $fh, $remote_file, FTP_BINARY ) ) {
				fclose( $fh );
				throw new RuntimeException( "Download failed: $remote_file" );
			}
			fclose( $fh );
			if ( preg_match( '/\.(php|html|js|css|json|htaccess)$/i', $local_file ) ) {
				$text = file_get_contents( $local_file );
				file_put_contents( $local_file, replace_urls( $text, $replacements ) );
			}
			++$count;
		}
	}
	ftp_close( $ftp );

	$ftp2 = ftp_connect_src();
	$pre  = $src_root . '/wp-content/uploads/migrate-impactads.sql';
	$local_sql = $dst_root . '/migrate-impactads.sql';
	$fh = fopen( $local_sql, 'wb' );
	if ( ! ftp_fget( $ftp2, $fh, $pre, FTP_BINARY ) ) {
		fclose( $fh );
		throw new RuntimeException( 'SQL download failed' );
	}
	fclose( $fh );
	$ht = fopen( 'php://temp', 'w+' );
	if ( @ftp_fget( $ftp2, $ht, $src_root . '/.htaccess', FTP_BINARY ) ) {
		rewind( $ht );
		file_put_contents( $dst_root . '/.htaccess', stream_get_contents( $ht ) );
		log_msg( '.htaccess copied' );
	}
	fclose( $ht );
	ftp_close( $ftp2 );

	log_msg( "Synced $count tree files" );
	log_msg( 'Importing DB...' );

	require $dst_root . '/db-config-impactads.php';
	mysqli_report( MYSQLI_REPORT_OFF );
	$mysqli = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	if ( ! $mysqli ) {
		throw new RuntimeException( 'DB connect: ' . mysqli_connect_error() );
	}
	mysqli_set_charset( $mysqli, 'utf8mb4' );
	$sql = file_get_contents( $local_sql );
	if ( ! mysqli_multi_query( $mysqli, $sql ) ) {
		throw new RuntimeException( 'Import: ' . mysqli_error( $mysqli ) );
	}
	do {
		if ( $result = mysqli_store_result( $mysqli ) ) {
			mysqli_free_result( $result );
		}
		if ( mysqli_errno( $mysqli ) ) {
			throw new RuntimeException( 'Import error: ' . mysqli_error( $mysqli ) );
		}
	} while ( mysqli_more_results( $mysqli ) && mysqli_next_result( $mysqli ) );

	@unlink( $local_sql );
	log_msg( 'DONE — https://impactads.io/' );
} catch ( Throwable $e ) {
	http_response_code( 500 );
	log_msg( 'ERROR: ' . $e->getMessage() );
}
