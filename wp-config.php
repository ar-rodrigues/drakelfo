<?php

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL
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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "local" );

/** MySQL database username */
define( 'DB_USER', "root" );

/** MySQL database password */
define( 'DB_PASSWORD', "root" );

/** MySQL hostname */
define( 'DB_HOST', "localhost" );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'idp13rxmdditlfxjuxgxv2fwp1xjzbkua5oirks1amehkw7qirrcjzp7v0etrsbx' );
define( 'SECURE_AUTH_KEY',  'diuj3pc6qlgzz1l6czavebwyq95xxinqeeqzp5g0n6pwqpuq9tpnluenfafiih2o' );
define( 'LOGGED_IN_KEY',    'fcjre8llfn7ajyupxuqwyajdp3qjreeayz1atfglqir1kixctbbhx6vke4hpnce5' );
define( 'NONCE_KEY',        'u7eqfcjhmjx7dbhrr3m0q9fjneley96y7yzwdypojngxx5n4qpori5sukeh1zckl' );
define( 'AUTH_SALT',        '7tbypgavgziunpaoadf04us7r0cetjleqy1wjeqz109gfq17ziotoekt5ncf3doo' );
define( 'SECURE_AUTH_SALT', 's05yvh7bsrxkrcagmjjtjwmwlyebbmgo7z7ybmlmsah8i909hr6uwfkkdlkgufwh' );
define( 'LOGGED_IN_SALT',   'gwutaig68uo8wh9wwi72gsd9cqhq3ce6ra1pwgfs1ed5k1jzszq2hmvyvmggpdws' );
define( 'NONCE_SALT',       'jlemwewgo0gmwqmrp3asqr5mcrpbs9hvfkciqkqi4g6qrolbaug2eafdpen75lmf' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wppl_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
