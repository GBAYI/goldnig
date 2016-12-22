<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'goldokma_onlineGN');

/** MySQL database username */
define('DB_USER', 'goldokma_net247');

/** MySQL database password */
define('DB_PASSWORD', 'network247');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'cpxffdz9viufocj6u6w4iw4g6w6mcqa5tu0h3ldmb10hkaxdv9ufq8kmgzhn16ra');
define('SECURE_AUTH_KEY',  'awqtn4rc0ktcwd35ixgunetiyfvff2jokbg0jxnwehnpem0y39kltcy3ljufloql');
define('LOGGED_IN_KEY',    'qgv0snor1r2vrbpys3zhpbc0xoahcvbpmeojv8fijuus0rgp5pxi6wbhorvnzkny');
define('NONCE_KEY',        'giheznkxfszzrsy80ocynb69cudeiety3dd5im0smu7m0hzxuetiynzzhz4bslcu');
define('AUTH_SALT',        'zynvrk0j7v0mxakaaxjefaxkidgr1hpey5svnhwbr5zfgc9ajtaxh7apd2oyjoeq');
define('SECURE_AUTH_SALT', 'd7zyxiyiwn0adg2mj8ld4waj2unbjtjawrr4ht8qqykh4bdlr3hxogf5etbrf1og');
define('LOGGED_IN_SALT',   '8q7vqlawy8dhckyly2asez34eeep1h8yarellu7zqczo8mhcmykz3drbvsgd41ob');
define('NONCE_SALT',       'kowcgfwqmxvuvuadmizbesw5sfcerszmz6yt9xxtkrffumvxw6dc5vt5z0asa8mh');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress

 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
