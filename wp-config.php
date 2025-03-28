<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cursoemvideo' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         '4$Ebd59gw_3 bG0{u_6ntJVnzEG^)_4PO }).)oiqz+-7~qSv@iR}ps,XFT0~x:<' );
define( 'SECURE_AUTH_KEY',  '!e?#5UB3?nX(.]7!$qK LnG;-}wSL%kApyCu:`Y)z:On=5My9i<wkmb$rg[ulO u' );
define( 'LOGGED_IN_KEY',    '6i-e^AR-lUrrs[ %Ys21_FX4&(O*HE:+4|_=j$DaAHpVelPWa|_Y&db1Z/#ED_@p' );
define( 'NONCE_KEY',        'u3@HeYDEas,VBVsp!Af;)ip!&lgL Kgd%a]Y@Qxg)?B~eH.6HqZ:XZc{yi6H,/^N' );
define( 'AUTH_SALT',        'c2`|Z5-?9z6iPYU?##geo0W@0Pouwen F/&gcM%KEhScfv5]prr&$.<=fO)Z H#T' );
define( 'SECURE_AUTH_SALT', 'Y1CID/186en(#@AcfJz2RfI2uR=%:^zuz@$:utwqe4Fu9yhyN(gM:/FA|C)YOzW,' );
define( 'LOGGED_IN_SALT',   'dKoK9`,{rv29G0_9deZtX5&+Q:;S4U.uNy^3~RU9yIbz:U2]d[JIaJ?v?pOn+fhO' );
define( 'NONCE_SALT',       'y2lugsEws BUe*EF<a=jBiN!KA|P%,=NS/IC!YIEV4$Es2i0yRcr@Cr=Zy|+AGjD' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'cev_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
