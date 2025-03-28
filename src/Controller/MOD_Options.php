<?php
namespace MOD\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;
use MOD\Helper\MOD_Utils as Utils;

class MOD_Options {
	public function __construct() {
		add_filter( 'plugin_action_links_mod-auto-updates/mod-auto-updates.php', [ $this, 'plugin_links' ] );
		add_filter( 'cron_schedules', [ $this, 'mod_add_cron_interval' ] );
		add_action( 'admin_init', [ $this, 'mod_register_schedule' ] );
		add_action( 'admin_notices', [ $this, 'mod_expire_token_notice' ] );
	}

    /**
	 * Add link settings page
	 *
	 * @since 1.0
	 * @param Array $links
	 * @return Array
	 */
	public function plugin_links( $links ) {
		$links_settings = [
            sprintf(
                '<a href="%s">%s</a>',
                'options-general.php?page=mod-auto-updates',
                esc_html__('Configurações', Core::SLUG)
            )
        ];

		$support_settings = [
            sprintf(
                '<a href="%s">%s</a>',
                'https://updates.mercadoonlinedigital.com/suporte-mod/',
                esc_html__('Suporte', Core::SLUG)
            )
        ];

		return array_merge( $links_settings, $support_settings, $links );
	}

	public function mod_add_cron_interval( $schedules ) {
		$schedules['mod_tmp_plugins'] = [
			'interval' => 10 * MINUTE_IN_SECONDS,
			'display'  => esc_html__( 'Consulta a cada 10 Minutos' )
		];

		$schedules['mod_tmp_themes'] = [
			'interval' => 10 * MINUTE_IN_SECONDS,
			'display'  => esc_html__( 'Consulta a cada 10 Minutos' )
		];

		return $schedules;
	}

	public function mod_register_schedule() {
        $this->schedule_event_if_not_exists( 'mod_update_plugins', 'mod_tmp_plugins' );
        $this->schedule_event_if_not_exists( 'mod_update_themes', 'mod_tmp_themes' );
    }

    private function schedule_event_if_not_exists( $event_name, $schedule_name ) {
        if ( !wp_next_scheduled( $event_name ) ) {
            wp_schedule_event( time(), $schedule_name, $event_name );
        }
    }

	public function mod_expire_token_notice() {
		$client_expire = get_option( 'mod_client_expire_in' );
	
		if ( !$client_expire ) {
			return;
		}
	
		$days = Utils::mod_get_expire_date( $client_expire );
	
		if ( !$days ) {
			return;
		}
	
		$message = sprintf( 'MOD auto updates: Falta %d dias para expirar o seu token.', $days );
	
		if ( $days >= 5 && $days <= 10 ) {
			$this->show_notice( 'warning', $message );
		} elseif ( $days > 1 && $days < 4 ) {
			$this->show_notice( 'error', $message );
		} elseif ( $days < 0 ) {
			$this->show_notice( 'error', 'MOD auto updates: Seu Token expirou. Favor renovar para não perder nenhuma atualização!' );
		}
	}
	
	private function show_notice( $type, $message ) {
		?>
		<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}	
}
