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
define('DB_NAME', 'ApartmajiMrakic');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

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
define('AUTH_KEY',         '1vl lnV#{?#n3Q(_O.ho2mB6[k|3# }Nd01Sg^:ipRK+(!6W=Yi8My;F+ir#;3^(');
define('SECURE_AUTH_KEY',  'e9Me,7gzje2#}{^.|K_>V9sT{yetvYH-w?lQk@I)S3)z1u9c!hJub#9;TH<R[%Ux');
define('LOGGED_IN_KEY',    'dzluH3~2_QCB2#Ba.st6nJRJ<+k].FOoGq.{Yfy{8s0IQ?hOIF}hv?b1gjYc]IO/');
define('NONCE_KEY',        ',%>A&rGR{tZgsLLL^:RKFLF%adW=C0~,RU6Ju$*j*flvEOZ5H-/6S{1Ah?6Z>$YO');
define('AUTH_SALT',        'Jk6#[FWg>C493%{]cx7}@3)aN.$E6%i&8cZbOfLGC]/~==^@t>AkFEOE~<_<?; [');
define('SECURE_AUTH_SALT', ',=qc@OPs,p0r+ ~qIXY?qZJdk| 2gP+L](Zj5*gJImbPdXgDo5hsoL|RNkl]8u>h');
define('LOGGED_IN_SALT',   'QNWK-=X#]1?U[DkqfD$BrEvHZg,<cy_,VP&PTh{Xb5H])ODvuv 9i7)/7E]kSEq{');
define('NONCE_SALT',       '(@/tabNfuek:#_)U|kAD$rUO4Tu.!w6E9%&_(Fo7Hm6y X5Dcdgal#q]^_KauBa~');

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
