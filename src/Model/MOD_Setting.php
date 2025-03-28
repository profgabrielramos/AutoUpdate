<?php
namespace MOD\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;
use MOD\Helper\MOD_Utils as Utils;

class MOD_Setting {
	public function mod_page_register_fields( $page_name ) {
		register_setting(
            'mod_auto_updates_group',
            'mod_auto_updates_field',
            [ $this, 'sanitize' ]
        );

        add_settings_section(
            'setting_section_id',
            '',
            [ $this, 'print_section_info' ],
            $page_name
        );

        add_settings_field(
            'mod_token_number',
			'',
            [ $this, 'mod_token_callback' ],
            $page_name,
            'setting_section_id',
            [ 'class' => 'mod-token-field' ]
        );
	}

	/**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
        $new_input = [];

        if ( isset( $input['mod_token_number'] ) ) {
            $new_input['mod_token_number'] = sanitize_text_field( $input['mod_token_number'] );
        }

        return $new_input;
	}

	/**
     * Print the Section text
    */
    public function print_section_info() {
        $client_email  = get_option( 'mod_client_email' );
        $client_expire = get_option( 'mod_client_expire_in' );
        $email_field   = __( 'Nenhum email cadastrado.', Core::SLUG );
        $expire_field  = __( 'Nenhum data de expiração cadastrada.', Core::SLUG );

        if ( $client_email ) {
            $email_field = sprintf(
                'Email: <b>%s</b>',
                $client_email
            );
        }

        if ( $client_expire ) {
            $expire_field = sprintf(
                'Data de Expiração: <b>%s</b>',
                Utils::convert_date_i18n( $client_expire )
            );
        }

        printf('<div class="mod-container">
            <div class="mod-info-card">
                <h3>%s</h3>
                <p>%s</p>
                <p>%s <strong>%s</strong></p>                
            </div>
            <div class="mod-card">
                <h3>%s</h3>
                <p>%s</p>
                <a class="mod-button" href="%s" target="_blank">Contato</a>
            </div>
            </div>',
            __( 'Informação sobre o funcionamento', Core::SLUG ),
            __( 'Para funcionamento das atualizações obrigatório inserir o token.', Core::SLUG ),
            __( 'O plugin foi configurado para verificar a disponibilidade de atualizações a cada intervalo de', Core::SLUG ),
            __( '10 MINUTOS.', Core::SLUG ),
            __( 'Suporte', Core::SLUG ),
            __( 'Problemas ou dúvidas? Entre em contato conosco.', Core::SLUG ),
            Core::MOD_SUPPORT
        );

        printf('<div class="mod-container">            
            <div class="mod-card">
                <h3>%s</h3>
                <div id="mod-email-field">%s</div>
                <div id="mod-expire-field">%s</div>
            </div>
            <div class="mod-token-card">%s</div>
            </div>',            
            __( 'Informação da conta', Core::SLUG ),
            $email_field,
            $expire_field,
            $this->mod_token_callback()
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function mod_token_callback() {
		$options = get_option( 'mod_auto_updates_field' );
        $token   = isset( $options['mod_token_number'] ) ? esc_attr( $options['mod_token_number']) : '';

        return sprintf(
            '<h3>%s</h3>
            <input type="password" id="mod_token_number" name="mod_auto_updates_field[mod_token_number]" value="%s" required/>
            <p class="description" id="tagline-description">%s</p>',
            __( 'Token', Core::SLUG ),
            $token,
            __( 'Campo para inserir seu Token para atualização dos plugins e/ou temas.', Core::SLUG )
        );
    }
}
