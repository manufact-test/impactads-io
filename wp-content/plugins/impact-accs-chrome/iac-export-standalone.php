<?php
/**
 * Standalone DB export — no WordPress bootstrap.
 */
declare( strict_types=1 );

header( 'Content-Type: text/plain; charset=utf-8' );
set_time_limit( 0 );
ini_set( 'memory_limit', '512M' );

$key = 'iac-migrate-impactads-2026';
if ( ( $_GET['key'] ?? '' ) !== $key && ( $_GET['iac_migrate'] ?? '' ) !== $key ) {
	http_response_code( 403 );
	echo "Forbidden\n";
	exit;
}

$host       = 'localhost';
$user       = 'cu59725_9tad1';
$pass       = 'SkKhyTJA';
$db         = 'cu59725_9tad1';
$src_prefix = 'wp_';
$dst_prefix = 'wp_';
$out_file   = '/home/c/cu59725/wordpress_9tad1/public_html/wp-content/uploads/migrate-impactads.sql';

$replacements = array(
	'https://cu59725-wordpress-zpsc4.tw1.ru' => 'https://impactads.io',
	'http://cu59725-wordpress-zpsc4.tw1.ru'  => 'https://impactads.io',
	'https://cu59725-wordpress-9tad1.tw1.ru' => 'https://impactads.io',
	'http://cu59725-wordpress-9tad1.tw1.ru'  => 'https://impactads.io',
);

mysqli_report( MYSQLI_REPORT_OFF );
$mysqli = mysqli_connect( $host, $user, $pass, $db );
if ( ! $mysqli ) {
	echo 'DB fail: ', mysqli_connect_error(), "\n";
	exit( 1 );
}
mysqli_set_charset( $mysqli, 'utf8mb4' );

function iac_replace_urls( string $value, array $pairs ): string {
	foreach ( $pairs as $from => $to ) {
		$value = str_replace( $from, $to, $value );
	}
	return $value;
}

function iac_sql_escape( string $value ): string {
	return str_replace(
		array( "\\", "\0", "\n", "\r", "\x1a", "'", '"' ),
		array( "\\\\", "\\0", "\\n", "\\r", "\\Z", "\\'", '\\"' ),
		$value
	);
}

$fp = fopen( $out_file, 'wb' );
if ( ! $fp ) {
	echo "Cannot write $out_file\n";
	exit( 1 );
}

fwrite( $fp, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n" );
$res = mysqli_query( $mysqli, 'SHOW TABLES' );
$exported = 0;
while ( $row = mysqli_fetch_row( $res ) ) {
	$table = $row[0];
	if ( ! str_starts_with( $table, $src_prefix ) || str_starts_with( $table, 'wp_zpsc4_' ) ) {
		continue;
	}
	$new_table = $dst_prefix . substr( $table, strlen( $src_prefix ) );
	fwrite( $fp, "DROP TABLE IF EXISTS `$new_table`;\n" );
	$create = mysqli_query( $mysqli, "SHOW CREATE TABLE `$table`" );
	$cr     = mysqli_fetch_row( $create );
	$ddl    = str_replace( "CREATE TABLE `$table`", "CREATE TABLE `$new_table`", $cr[1] );
	fwrite( $fp, $ddl . ";\n" );
	$rows = mysqli_query( $mysqli, "SELECT * FROM `$table`" );
	$cols = null;
	$row_count = 0;
	while ( $data = mysqli_fetch_assoc( $rows ) ) {
		if ( null === $cols ) {
			$cols     = array_keys( $data );
			$col_list = '`' . implode( '`,`', $cols ) . '`';
		}
		$vals = array();
		foreach ( $cols as $col ) {
			if ( null === $data[ $col ] ) {
				$vals[] = 'NULL';
			} else {
				$vals[] = "'" . iac_sql_escape( iac_replace_urls( (string) $data[ $col ], $replacements ) ) . "'";
			}
		}
		fwrite( $fp, "INSERT INTO `$new_table` ($col_list) VALUES (" . implode( ',', $vals ) . ");\n" );
		++$row_count;
	}
	echo "exported $table -> $new_table ($row_count rows)\n";
	++$exported;
}
fwrite( $fp, "SET FOREIGN_KEY_CHECKS=1;\n" );
fclose( $fp );
echo "DONE: $out_file (" . filesize( $out_file ) . " bytes)\n";
