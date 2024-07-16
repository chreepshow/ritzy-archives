<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ritzy_archives2');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', '');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',          '8|[x,g&=({`/DWrsHD(CTs9f9NlY`zq=#qE=~0}ksr|U7-Lfta@lD2Jn1p8{8@h)');
define('SECURE_AUTH_KEY',   ':!7{{^n&o}V&`?VH9&}R1_>gw:FvT}Sdqv=0D+Ph&l?]M(vx+/Up]4xU87Fp]ldz');
define('LOGGED_IN_KEY',     '@.!S8v8G8|Z[e,~30:rlVmfaF2P&Q?@V]q5xS*=/@*QKW5WAr1@Sf/dkRNo.jch%');
define('NONCE_KEY',         'gr4VFV43!h&!HK/?<S<-L7D82Bz}RUTTAp+P,>1tRE9Fe.XE=`z#Sp:pQwYBHW@D');
define('AUTH_SALT',         'kx2%3a7hHFLb.`e#LcEMD/c`al.U@F0>/_@El:b<|xwZByGGH)SAIxYYN{q]w+sb');
define('SECURE_AUTH_SALT',  'S.b1M t+Yd.V0Kz@~,tupx@KF#Vyef (pM=]eou[he`~jMSgn5CyBO|a27;.#P{b');
define('LOGGED_IN_SALT',    'g^oU!!wxhNr51v>.R=<H?c{gKw9W9r(07|jf%*na2MA4&>l=JXH}BCY#YTO]f2$m');
define('NONCE_SALT',        'z_Z2o@`=Oz^FeRyJxl9>lm?O AEZ..vfu8IA[UM]iv2,aKQEJo_hA0i[clgMZk:&');
define('WP_CACHE_KEY_SALT', '^/lBe}T51|&EfL)/o8R3ZE(`0yWT_e[{G7;^ a<3Q8#{,_?%.I+(IFqG<dXvS9Yn');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);


/* Add any custom values between this line and the "stop editing" line. */



define('FS_METHOD', 'direct');
define('WP_AUTO_UPDATE_CORE', 'minor');
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
