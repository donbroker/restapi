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
define('DB_NAME', 'restapi');

/** MySQL database username */
define('DB_USER', 'restapi');

/** MySQL database password */
define('DB_PASSWORD', 'Pembina!1483');

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
define('AUTH_KEY',         '9B]vcUj;Q/cAAwu#n|y|~Jk+7Gx,eS]s)gvrYENnkx3z-FcqtLG5{0#Aqb:?tpL ');
define('SECURE_AUTH_KEY',  'sN-=(0|2%4GHllx>4b1ZOX:rSkex1QEP,Jpd>_~c_|muB* eX:r:njN`JDS0PC,6');
define('LOGGED_IN_KEY',    '%,fb 1Dh;3]f4Ba`,O?q_>lYZ{R?<*O(Jx$K3)]x_Jjk{~ZT{5jMsh@!&OH?5;{e');
define('NONCE_KEY',        'iuTQVZV->p0MIa1~pZJsTN9I[.R?p$pV<WmB(9TbAX2^1K[`BC5H}bVjne8o>3;l');
define('AUTH_SALT',        'BQ`0S6al~Od?yGH$,K&L%w98D_aPJh(O>$x].qSbLkAg*3H~`9Z?hujr[~cl54NF');
define('SECURE_AUTH_SALT', 'c+}K+OpYM;(l[8#pDC;Ae0:<#~AJX?dBO)iE,:(QtB6wo8#yS]bmSRVA5_yp1B $');
define('LOGGED_IN_SALT',   '<&4bkRBcI>{RplK01|l(;WRuK-4Q8PaX^xMT9V-su`?q^[oM&Aoe@q){r0Qq;hI2');
define('NONCE_SALT',       '$trcpkUmlz<{nq-~K.<v :bVgi;Mv(KrN^bwNK=(6s~D3#<?Daqeimkb]*%x:^{W');

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
