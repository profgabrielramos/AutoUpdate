<?php
namespace MOD;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\Helper\MOD_Utils as Utils;

class MOD_Core {
	private static $_instance = null;

	const SLUG               = MOD_SLUG;
	const LOCALIZE_SCRIPT_ID = 'PPWAGlobalVars';
	const PLUGINS_DIR        = WP_PLUGIN_DIR . '/';
	const PASSWORD           = 'mercadoonlinedigital';
	const API_CLIENTS        = 'https://dash-api.mercadoonlinedigital.com/wp-json/mod/v1/clients';
	const MOD_SUPPORT        = 'https://mercadoonlinedigital.com/suporte/';
	const PLUGINS_JSON       = 'plugins.json';
	const THEMES_JSON        = 'themes.json';
	const TMP_MOD            = 'mod-updates';

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

		self::initialize();
		self::admin_enqueue_scripts();
		//self::front_enqueue_scripts();
	}

	public static function load_textdomain() {
		load_plugin_textdomain( self::SLUG, false, self::plugin_rel_path( 'languages' ) );
	}

	public static function initialize() {
		$controllers = [
			'MOD_Options',
			'MOD_Settings',
			'MOD_Updates'
		];

		self::load_controllers( $controllers );
	}

	public static function load_controllers( $controllers ) {
		foreach ( $controllers as $controller ) {
			$class = sprintf( __NAMESPACE__ . '\Controller\%s', $controller );
			new $class();
		}
	}

	public static function get_localize_script_args( $args = array() ) {
		$defaults = [
			'ajaxUrl' => Utils::get_admin_url( 'admin-ajax.php' ),
		];

		return array_merge( $defaults, $args );
	}

	public static function admin_enqueue_scripts() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'scripts_admin' ) );
	}

	public static function front_enqueue_scripts() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'scripts_front' ) );
	}

	public static function scripts_admin() {
		self::enqueue_scripts( 'admin' );
		self::enqueue_styles( 'admin' );
	}

	public static function scripts_front() {
		self::enqueue_scripts( 'front' );
		self::enqueue_styles( 'front' );
	}

	public static function enqueue_scripts( $type, $deps = array(), $localize_args = array() ) {
		$id = "{$type}-script-" . self::SLUG;

		wp_enqueue_script(
			$id,
			self::plugins_url( "assets/javascripts/{$type}/built.js" ),
			array_merge( array( 'jquery' ), $deps ),
			self::filemtime( "assets/javascripts/{$type}/built.js" ),
			true
		);

		wp_localize_script(
			$id,
			self::LOCALIZE_SCRIPT_ID,
			self::get_localize_script_args( $localize_args )
		);
	}

	public static function enqueue_styles( $type ) {
		wp_enqueue_style(
			"{$type}-style-" . self::SLUG,
			self::plugins_url( "assets/stylesheets/{$type}/style.css" ),
			array(),
			self::filemtime( "assets/stylesheets/{$type}/style.css" )
		);
	}

	public static function plugin_dir_path( $path = '' ) {
		return plugin_dir_path( MOD_ROOT_FILE ) . $path;
	}

	public static function plugin_rel_path( $path ) {
		return dirname( self::plugin_basename() ) . '/' . $path;
	}
	/**
	 * Plugin file root path
	 *
	 * @since 1.0
	 * @param String $file
	 * @return String
	 */
	public static function get_file_path( $file, $path = '' ) {
		return self::plugin_dir_path( $path ) . $file;
	}

	public static function plugins_url( $path ) {
		return esc_url( plugins_url( $path, MOD_ROOT_FILE ) );
	}

	public static function filemtime( $path ) {
		$file = self::plugin_dir_path( $path );

		return file_exists( $file ) ? filemtime( $file ) : MOD_VERSION;
	}

	public static function get_page_link() {
		return Utils::get_admin_url( 'plugins.php?plugin_status=all' );
	}

	public static function support_link() {
		return esc_url( 'https://updates.mercadoonlinedigital.com/suporte-mod/' );
    }

    public static function mod_site_link() {
		return esc_url( 'https://mercadoonlinedigital.com/' );
	}
	/**
	 * Plugin base name
	 *
	 * @since 1.0
	 * @param String $filter
	 * @return String
	 */
	public static function plugin_basename( $filter = '' ) {
		return $filter . plugin_basename( MOD_ROOT_FILE );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) :
			self::$_instance = new self;
		endif;
	}
}
