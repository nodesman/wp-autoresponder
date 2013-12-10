<?php
/* Path to the WordPress codebase you'd like to test. Add a backslash in the end. */
$configuration = parse_ini_file(__DIR__."/../../install.properties");

$wp_dir = getenv('wp_dir');
$wp_dbname = getenv('wp_dbname');
$wp_dbuser = getenv('wp_dbuser');
$wp_dbpass = getenv('wp_dbpass');
$wp_dbhost = getenv('wp_dbhost');

$dir = (!empty($wp_dir))?$wp_dir:$configuration['wp.dir'];
$dbname = (!empty($wp_dbname))?$wp_dbname:$configuration['wp.dbname'];
$user = (!empty($wp_dbuser))?$wp_dbuser:$configuration['wp.dbuser'];
$pass = (!empty($wp_dbpass))?$wp_dbpass:$configuration['wp.dbpass'];
$host = (!empty($wp_dbhost))?$wp_dbhost:$configuration['wp.dbhost'];
define( 'ABSPATH', $dir."/");
define('DB_NAME', $dbname);
define( 'DB_USER', $user);
define('DB_PASSWORD',$pass);
define( 'DB_HOST',  $host);

define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WPLANG', '' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );

define( 'WP_TESTS_DOMAIN', $configuration['wp.wp_url'] );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_TESTS_NETWORK_TITLE', 'Test Network' );
define( 'WP_TESTS_SUBDOMAIN_INSTALL', false );
$base = '/freeness/';

/* Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only */
define( 'DISABLE_WP_CRON', true );

define( 'WP_ALLOW_MULTISITE', false );
if ( WP_ALLOW_MULTISITE ) {
	define( 'WP_TESTS_BLOGS', 'first,second,third,fourth' );
}
if ( WP_ALLOW_MULTISITE && !defined('WP_INSTALLING') ) {
	define( 'SUBDOMAIN_INSTALL', WP_TESTS_SUBDOMAIN_INSTALL );
	define( 'MULTISITE', true );
	define( 'DOMAIN_CURRENT_SITE', WP_TESTS_DOMAIN );
	define( 'PATH_CURRENT_SITE', '/' );
	define( 'SITE_ID_CURRENT_SITE', 1);
	define( 'BLOG_ID_CURRENT_SITE', 1);
	//define( 'SUNRISE', TRUE );
}

$table_prefix  = 'dev_';

define( 'WP_PHP_BINARY', 'php' );
