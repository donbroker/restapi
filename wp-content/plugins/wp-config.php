<?php
# Database Configuration
define( 'DB_NAME', 'wp_qhong' );
define( 'DB_USER', 'qhong' );
define( 'DB_PASSWORD', 'w8HBBPMrKnFX3y4qqFBn' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_HOST_SLAVE', '127.0.0.1' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         '0-|zl{Wv(TYezws+orhbsiPWj*dbY:tB^WT@Hc68R1}xUN58dnMlCf$-q72Vy*[r');
define('SECURE_AUTH_KEY',  '3yQ*_1d<_+C@;([2=k!svVY>hQ<Y?T-/ %i`LZm4VnRnrX)YOj6V]1k}HSEi?s9^');
define('LOGGED_IN_KEY',    'bRt/DYtd^5@$O)~M|(_jNt$@sn+wB)$C }Q#d3g)L-{~Yvjiu-nn>Nv3m/yy%~PW');
define('NONCE_KEY',        '0o:-;u)Pw$inP|)55uH-l..#0}S-}#A l0ga-j^IWY%kY!^:&~b|QYT[lr~:9v}[');
define('AUTH_SALT',        '&>#u(+TccD1`-^i%CG=>-5f9dnCz=GFjH+9A E%{mO4nG(![II>nymVZNG[yI3||');
define('SECURE_AUTH_SALT', 'E_-KXtWAbV$~XB&g)03RG+Y7uY|W4xt=4AHu|vNNM/zWw[:AEvO1-=%+ng^{QQp4');
define('LOGGED_IN_SALT',   'sX6{EQtZs~J%LO|]x6Wl]6*@@f[aP|-eKK9%{tf)7RPd/zvr|U9E@[BuCt|O9{ y');
define('NONCE_SALT',       ';pRsXaRRwHHf>AUoEIK`)59rVjjQde+94^S5?!-VYm_y<`h5|pAgp?pR2({d6f0I');
define('JWT_AUTH_SECRET_KEY', '}xNSmZ}hF}4kxyvUWdp[dQHtw?OZ$`qOyseu03*DA1J~<U[sj2yjQ6{+j84cS<kd');

# Localized Language Stuff

define( 'WP_CACHE', TRUE );

define( 'WP_AUTO_UPDATE_CORE', false );

define( 'PWP_NAME', 'qhong' );

define( 'FS_METHOD', 'direct' );

define( 'FS_CHMOD_DIR', 0775 );

define( 'FS_CHMOD_FILE', 0664 );

define( 'PWP_ROOT_DIR', '/nas/wp' );

define( 'WPE_APIKEY', '566793a75c695de535a08b43c929e56cafc723b5' );

define( 'WPE_CLUSTER_ID', '100113' );

define( 'WPE_CLUSTER_TYPE', 'pod' );

define( 'WPE_ISP', true );

define( 'WPE_BPOD', false );

define( 'WPE_RO_FILESYSTEM', false );

define( 'WPE_LARGEFS_BUCKET', 'largefs.wpengine' );

define( 'WPE_SFTP_PORT', 2222 );

define( 'WPE_LBMASTER_IP', '104.155.134.146' );

define( 'WPE_CDN_DISABLE_ALLOWED', true );

define( 'DISALLOW_FILE_MODS', FALSE );

define( 'DISALLOW_FILE_EDIT', FALSE );

define( 'DISABLE_WP_CRON', false );

define( 'WPE_FORCE_SSL_LOGIN', false );

define( 'FORCE_SSL_LOGIN', false );

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

define( 'WPE_EXTERNAL_URL', false );

define( 'WP_POST_REVISIONS', FALSE );

define( 'WPE_WHITELABEL', 'wpengine' );

define( 'WP_TURN_OFF_ADMIN_BAR', false );

define( 'WPE_BETA_TESTER', false );

umask(0002);

$wpe_cdn_uris=array ( );

$wpe_no_cdn_uris=array ( );

$wpe_content_regexs=array ( );

$wpe_all_domains=array ( 0 => 'qhong.wpengine.com', );

$wpe_varnish_servers=array ( 0 => 'pod-100113', );

$wpe_special_ips=array ( 0 => '104.155.134.146', );

$wpe_ec_servers=array ( );

$wpe_largefs=array ( );

$wpe_netdna_domains=array ( );

$wpe_netdna_domains_secure=array ( );

$wpe_netdna_push_domains=array ( );

$wpe_domain_mappings=array ( );

$memcached_servers=array ( );
define('WPLANG','');
define('WP_DEBUG', true);

# WP Engine ID


# WP Engine Settings






# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');

$_wpe_preamble_path = null; if(false){}
