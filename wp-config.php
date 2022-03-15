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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'WPCACHEHOME', '/home/clients/bb4d3c129f820387dbbcf13b8a59ddb8/web/wp-content/plugins/wp-super-cache/' );
define('WP_CACHE', true);
define('DB_NAME', 'v530w_WP575605');

/** MySQL database username */
define('DB_USER', 'v530w_WP575605');

/** MySQL database password */
define('DB_PASSWORD', 'F0r9LlPHFd');

/** MySQL hostname */
define('DB_HOST', 'v530w.myd.infomaniak.com');

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
define('AUTH_KEY',         'IZDI%,Jw;p?elO^6;KXXaZ;y5AmE21fr~e{_vJzAW!y?xYM.ec%h*2e._,eTE^?Z');
define('SECURE_AUTH_KEY',  'BY9aM7)MKA={i/3&?@Nye`1d(_Q*o9SX6HUQfa?K)CU!!nh)tqsKCCATe(}n8_Wt');
define('LOGGED_IN_KEY',    'luR)x}liux7dp+ZnmzC|/rEK3S3f>gL2a{lto&BeO`47uLqe14!7htXkRj!(OQdj');
define('NONCE_KEY',        'YLeMODs<P4HPeq*GT>`I5D~FIzn;,?_SF,QVjSYsOP=F*qYhPB`H^T4NLFH%Q36D');
define('AUTH_SALT',        'E3034z!2>/<v%9kI!p0+/CnWb`9J@y`:y)=.?Eo+a)kAzBG,EpmkQ!r#LZ:G<8#5');
define('SECURE_AUTH_SALT', 'P(LdRC0raAhx,ST)4dA>^>S<EL8wh,i&)SQ&~VSNjV<bJ!L9S/Qa.`1)0Y0@o13v');
define('LOGGED_IN_SALT',   'p)A0->I1nka11i~}/U/W!~bGCS*qZgdFe#&:M!E<&s;Uu^<B{Hb,U4lGnQ+aiORD');
define('NONCE_SALT',       '@<Y-c1J{(s`9uI&-B5;!IPM}k,NIN0xsoCM#`EI7*QBQUJ3hf^X0AWpw9dUk}n1}');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_575605_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
//define('WPLANG', 'fr_FR');

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
define('WP_DEBUG',          false);
define('WP_DEBUG_LOG',      false);
define('WP_DEBUG_DISPLAY',  false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
