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
define('DB_NAME', 'basheskia');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         'RiY:;%6-Yk+lLTcC}`4O!Nq2Y; .L$(-6)!Fm@ .^q]R:t>xo&GWSuQ4]t[j}O#m');
define('SECURE_AUTH_KEY',  'ZBFmK>Fqnp+Miose,MQ.x~f5BC7,~+ KJTk%aV L*kS {*afane=E)}<aFN.nMDG');
define('LOGGED_IN_KEY',    '{K}F}y14%j=PHD/n%:lB$nS{8BZf9MM*E#NZ;CRp!0@+d-eS4vR!V(y!|pt~to}i');
define('NONCE_KEY',        '^P2}.-@xSwxL_uK$^S`3X_c:%5DBHEHo_-b.^2&~+?~x*68?V}de)&3=bI.!XJjL');
define('AUTH_SALT',        '>^qIY83,2)0_zj;R4]%%l}MG~mbq)y4Huro`iDI,4C*@-Os*DaRPFmbeMN.XPRJa');
define('SECURE_AUTH_SALT', '%AGmR;UE=jPItF`05*Vm.Zg=LIou|IV6^(.e$b@C x?E(ScSuJ0_Z(d@erVbKHgq');
define('LOGGED_IN_SALT',   'bz0h 9LhOM U}w7{Dq)JQ3W][<kXIp(9Ey=*,[gRk%9;fhW6mat`b*7YEiVcYU#$');
define('NONCE_SALT',       'c{(7dWS>.>WE5>EqdG=,5A!k3Dw,M4V)7 dkdh]^Evf(5Dm-Kc vTZ5r_~pYs|vh');

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
