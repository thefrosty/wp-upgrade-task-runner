<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
define('ABSPATH', dirname(__DIR__) . '/wordpress/');
/*
 * Path to the theme to test with.
 *
 * The 'default' theme is symlinked from test/phpunit/data/themedir1/default into
 * the themes directory of the WordPress installation defined above.
 */
define('WP_DEFAULT_THEME', 'default');

// Test with multisite enabled.
// Alternatively, use the tests/phpunit/multisite.xml configuration file.
// define( 'WP_TESTS_MULTISITE', true );

// Force known bugs to be run.
// Tests with an associated Trac ticket that is still open are normally skipped.
// define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode (default).
define('WP_DEBUG', true);

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.
define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress_test');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wordpress_user');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASS') ?: 'mysql_password');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 */
define('AUTH_KEY', '#,+Zam!9.)h;PpLaSlT++Kd7)+[zp.QKZV*kttjqi8WnNp+@p!&wf0H?tB:&C[UN');
define('SECURE_AUTH_KEY', '(@UehBTA^-Ncn@Bm`L+|`!;f=P/g;:n*Galw4ml.`fV,0hb~G?[R<vhRn$vqZCo5');
define('LOGGED_IN_KEY', 'r=yn5qj+=c n,@Q-:sF:T{}]oVckubbLd_5_g..{+|h;nU2_e+nMtMlJ6!|B;nCt');
define('NONCE_KEY', 'd?9V*>ZVe2N+bO>rF,pMB3AzNr~*)T)%`N.M421.E*x]jehx-gO3Uc#6o/DBUIW&');
define('AUTH_SALT', '=q 5#w[FLLj)Aa%^}4Gux![V*==@0/L]@L+?YgQpqqj?EBTT,J9 MT|b*Qor*AUq');
define('SECURE_AUTH_SALT', 'HA6w)E|K|c[bB.H}iLM?OoZ*}Qyq|qh8QzY-,0=k#7D9#;k6,&e;sf;R$^IT^|cM');
define('LOGGED_IN_SALT', '7qj+-QM|)C}ZFZ$?2eC&xC$`%|6jJb_+wOd`|NalfS^%^D.|D!j+cOVyTW_#62o#');
define('NONCE_SALT', 'Fm`|BOqG8zAUAN[HEf$:uam=0Q|,Zlx6!qYH,kMX3-X%>t6Y3jrT:`KY>igCEB50');


$table_prefix = 'wptests_';

define('WP_TESTS_DOMAIN', 'wp-upgrade-task-runner.test');
define('WP_TESTS_EMAIL', 'admin@wp-upgrade-task-runner.test');
define('WP_TESTS_TITLE', 'Test CMS');

define('WP_PHP_BINARY', 'php');

define('WPLANG', '');
