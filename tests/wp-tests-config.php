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
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wp');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASS') ?: 'password');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 * Change these to different unique phrases!
 * You can generate these using the
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 */
define('AUTH_KEY', 'FSr(]lK3ah5UkTx0erXxW~#-[kcXQ-_FfGeGUjf-g$~i1#LsDnO;D^|q~R9!fB-2');
define('SECURE_AUTH_KEY', '+]MK1=m_7;>{9-V$Bp,q8M>H&un%x~jiJzzDa/xIzc:APzf&P=ML_EWM.[=*D:6`');
define('LOGGED_IN_KEY', 'eSi6V@Q8eqO.3k&Z<T}e&Ro|+2JS-Tr!-+aAuuKB79TA|SBASE(}V);T>g0=Mm6V');
define('NONCE_KEY', '~uvZ3MOv{pV;*ac)Sb?^lh|Bq|P1a`-uCuW(;1n5VwW.7nmTFhT%+FS|Rn^nhH3!');
define('AUTH_SALT', 'hq?z3_jMqOn=t.xcnuG]q>up$[J;W-cXbE=x9IM}20]/CM$73p^NE=g*Y0)?o.He');
define('SECURE_AUTH_SALT', 'BEQZkO>iH{y*U@aCM=w_kDj|LI8+V+Q:C,9Qvh8e^`(<iia F%GfXFcQ;fklm<##');
define('LOGGED_IN_SALT', ';5$6](Q;!{wz5(E>8W8BI46+F(nH[D%9LD B|EQE;S>F!Qv*o+Uy:9M<,&f<a([!');
define('NONCE_SALT', 'AHGu H{Nr:igq)wer$V%hQhTy2)~;7]ZO)=so~n+}AD,fw^l`OD=P,O+t`>PaTIo');


$table_prefix = 'wptests_';

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');

define('WP_PHP_BINARY', 'php');

define('WPLANG', '');
