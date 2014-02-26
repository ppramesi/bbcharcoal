<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// My Custom Defines
// These are for multi domains.
ini_set('session.gc_maxlifetime', 30*60);
ini_set('memory_limit','64M');
//ini_set('display_errors',1);
//error_reporting(E_ALL);
$GLOBALS['ECHO_DB_ERRORS'] = false;

define('WP_MEMORY_LIMIT', '64M');
//define('WP_CACHE', true); //Added by WP-Cache Manager
define('WP_HOME','http://'.$_SERVER['HTTP_HOST']);
define('WP_SITEURL','http://'.$_SERVER['HTTP_HOST']);

// Path Defines
define('SITE_LOCATION', '/home5/joebotwe/public_html/bbcharcoal/dev/');

if($_SERVER['HTTPS'] == "on") {
	define('URL_BASE','https://dev.'.$_SERVER['HTTP_HOST'].'/');
} else {
	define('URL_BASE','http://dev.bbcharcoal.com/');
}

define('IMAGE_BASE',URL_BASE.'media/');
define('LIB_BASE', SITE_LOCATION.'lib/');
define('LOG_BASE', SITE_LOCATION.'logs/');
define('PLUGIN_BASE', SITE_LOCATION.'web/wp-content/plugins/');
define('UPS_BASE', LIB_BASE.'ups/');
define('UPS_LABELS_BASE', SITE_LOCATION.'web/ups-labels/');

define('MAIL_HOST', 'mail.bbcharcoal.com');
define('MAIL_USERNAME', 'smtp@bbcharcoal.com');
define('MAIL_PASSWORD', 'Dojob1289');

$GLOBALS['INVALID_ADDESS_EMAILS'] = array('joebotdesigns@gmail.com', 'contact@bbcharcoal.com');
$GLOBALS['ADMIN_EMAIL'] = array('joebotdesigns@gmail.com');
//$GLOBALS['INVALID_ADDESS_EMAILS'] = array('scotschroeder@gmail.com');
$GLOBALS['ALT_IMAGES'] = array('bannerPhone'=>'Order BBQ Charcoal Online');

require_once(UPS_BASE.'config.conf.php');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'joebotwe_bbcharcoal_dev');

/** MySQL database username */
define('DB_USER', 'joebotwe_bbchar');

/** MySQL database password */
define('DB_PASSWORD', '?bbcharcoal1289?');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ')o@)o]!m+H{k-ssGeJKxbcYn l+TUe01=aano<P&|$?,=)J;=AE<i)XG%W_C[/z}');
define('SECURE_AUTH_KEY',  'V-r4OO6:@X+8c%TLm $cHksp?K/ t!7O/](Ie3z9?$ni,?=#{*|N-c5?hyI}S&j_');
define('LOGGED_IN_KEY',    '&GN@D`WGZu5IUY}q.Kj *^hrG3RmhWINMCjt^H3mIQ2!+,}9@&OD U.-;d{zjF9G');
define('NONCE_KEY',        '(RM+Xak=5ZIv-C*|j4|[rg+-RT)6P4<*KZs-$w_>S|H?3^PZ?1{5M&Gg-dcMWEIM');
define('AUTH_SALT',        '7S|?GXKBA4J8[yBV5=AxfEzUf-nbFE7mCBz_biT+H-FyC6j:qI3SFb[ttj)wLz$w');
define('SECURE_AUTH_SALT', '7-nnz>cW)pox2Z^vIV1!+r78l>||#7?B%fUVhmHtCEzc}-6-K# _Iiav07e0{dzd');
define('LOGGED_IN_SALT',   'I1!q:~&f3-v*61aU)[t6aL)}5Rs-`pZ(W6oic/)-O9):|4nXzZ;Wf`Fq };:_UIv');
define('NONCE_SALT',       'xc?DDO*VMa1:aXqfwh+_zO&q;SA]f&zT6,I]_nM,luz[_u-7oGF`e0$zITi948}A');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
