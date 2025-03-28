<?php
namespace MOD\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;
use MOD\View\MOD_Settings as Settings_View;
use MOD\Model\MOD_Setting as Settings_Model;
use MOD\Helper\MOD_Utils as Utils;

class MOD_Settings {
    /**
     * Page name
     *
     * @var string
     */
    private $page_name;
    /**
     * Page log
     *
     * @var string
     */
    private $page_log;
    /**
     * Settings model
     *
     * @var object
     */
    private $settings_model;
    /**
     * Constructor
     */
	public function __construct() {
        $this->page_name      = 'mod-auto-updates';
        $this->page_log       = 'mod-auto-updates-log';
        $this->settings_model = new Settings_Model();

        add_action( 'admin_menu', [ $this, 'mod_add_plugin_page' ] );
        add_action( 'admin_init', [ $this, 'mod_page_init' ] );
        add_action( 'wp_ajax_mod_admin_token', [ $this, 'mod_update_admin_settings' ] );
    }
    /**
     * Add options page
     */
    public function mod_add_plugin_page() {
        add_submenu_page(
            'options-general.php',
            __( 'Configurações', Core::SLUG ),
            __( 'MOD Updates', Core::SLUG ),
            'manage_options',
            $this->page_name,
            [ $this, 'mod_create_admin_page' ]
        );
    }
    /**
     * Options admin page callback
     */
    public function mod_create_admin_page() {
        Settings_View::mod_admin_page_html();
    }
    /**
     * Register and add settings
     */
    public function mod_page_init() {
        $this->settings_model->mod_page_register_fields( $this->page_name );
    }

     /**
     * Update admin settings
     */
    public function mod_update_admin_settings() {
        if ( !wp_doing_ajax() ) {
			return;
		}

        $token = Utils::post( 'token' ) ? Utils::post( 'token' ) : '';

        if ( empty( $token ) ) {
            wp_send_json_error( [
                'message' => __( 'Token está vazio!', Core::SLUG ),
            ] );
        }

        //$client_args   = Utils::mod_get_client_curl( $token ) ? Utils::mod_get_client_curl( $token ) : '';
        $client_args   = Utils::mod_get_client( $token ) ? Utils::mod_get_client( $token ) : '';
        $client_email  = isset( $client_args['email'] ) ? sanitize_email( $client_args['email'] ) : '';
        $client_expire = isset( $client_args['expire_in'] ) ? $client_args['expire_in'] : '';

        if ( empty( $client_email ) ) {
            update_option( 'mod_client_email', $client_email );
            update_option( 'mod_auto_updates_field', [ 'mod_token_number' => $token ] );

            wp_send_json_error( [
                'email'   => $client_email,
                'token'   => $token,
                'message' => __( 'E-mail não foi encontrado!', Core::SLUG ),
            ] );
        }

        if ( empty( $client_expire ) ) {
            update_option( 'mod_client_expire_in', $client_expire );
            update_option( 'mod_auto_updates_field', [ 'mod_token_number' => $token ] );
        }

        update_option( 'mod_client_email', $client_email );
        update_option( 'mod_client_expire_in', $client_expire );
        update_option( 'mod_auto_updates_field', [ 'mod_token_number' => $token ] );

       wp_send_json_success( [
            'email'     => $client_email,
            'expire_in' => $client_expire,
            'token'     => $token,
            'message'   => __( 'Token atualizado com sucesso!', Core::SLUG ),
        ] );
    }
}
