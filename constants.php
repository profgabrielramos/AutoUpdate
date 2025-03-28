<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

function mod_define( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

mod_define( 'MOD_SLUG', 'mod-auto-updates' );
mod_define( 'MOD_VERSION', '3.2.0' );
mod_define( 'MOD_ABSPATH', dirname( dirname(__FILE__) ) . '/' );
mod_define( 'MOD_ROOT_PATH', dirname( __FILE__ ) . '/' );
mod_define( 'MOD_ROOT_URL', plugin_dir_url( __FILE__ ) );
mod_define( 'MOD_PLUGIN_SLUG', basename( dirname(__FILE__) ) );
mod_define( 'MOD_TMP_PATH', MOD_ROOT_PATH . 'tmp' );
mod_define( 'MOD_ROOT_SRC', MOD_ROOT_PATH . 'src/' );
mod_define( 'MOD_ROOT_FILE', MOD_ROOT_PATH . MOD_SLUG . '.php' );
mod_define( 'WP_THEME_DIR', get_theme_root() );
mod_define( 'MOD_OPTION_ACTIVATE', 'mod_activate' );
