<?php
namespace MOD\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\Helper\MOD_Utils as Utils;
use MOD\Model\MOD_Plugin as Plugins;
use MOD\Model\MOD_Theme as Themes;

class MOD_Updates {
    public function __construct() {
        add_action( 'mod_update_plugins', [ $this, 'mod_plugin_data' ] );
        add_action( 'mod_update_themes', [ $this, 'mod_theme_data' ] );
    }
    /**
	 * Plugin API CRON PLUGINS
	 *
	 * @since 1.0
	 * @param Array $links
	 * @return Array
	 */
    public function mod_plugin_data() {
        if ( !$token = Utils::mod_get_token() ) {
            return;
        }

        Plugins::get_plugin_api_json();
	}
    /**
	 * Theme API CRON THEMES
	 *
	 * @since 1.0
	 * @param Array $links
	 * @return Array
	 */
    public function mod_theme_data() {
        if ( !$token = Utils::mod_get_token() ) {
            return;
        }
        
        Themes::get_theme_api_json();
	}
}
