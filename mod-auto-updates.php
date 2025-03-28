<?php
/*
 * Plugin Name: MOD - Atualizações Automáticas
 * Plugin URI:  https://mercadoonlinedigital.com/
 * Version:     3.2.0
 * Author:      Coffee Code
 * Author URI:  https://coffee-code.tech/
 * Text Domain: mod-auto-updates
 * Domain Path: /languages
 * License:     GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Description: Ferramenta necessária para busca automática das atualizações dos plugins/temas do Mercado Online Digital.
 * Requires PHP: 7.1
 * Requires at least: 5.0
 */

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

require_once dirname( __FILE__ ) . '/constants.php';

function mod_render_admin_notice_html( $message, $type = 'error' ) {
?>
	<div class="<?php echo esc_attr( $type ); ?> notice is-dismissible">
		<p>
			<strong><?php _e( 'MOD - Atualizações Automáticas', MOD_SLUG ); ?>: </strong>
			<?php echo esc_html( $message ); ?>
		</p>
	</div>
<?php
}

if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
	function mod_admin_notice_php_version() {
		mod_render_admin_notice_html(
			__( 'Sua versão no PHP não é suportada. Requerido >= 7.1', MOD_SLUG )
		);
	}

	_mod_load_notice( 'admin_notice_php_version' );
	return;
}

function _mod_load_notice( $name ) {
	add_action( 'admin_notices', "mod_{$name}" );
}

function _mod_load_instances() {
	require_once __DIR__ . '/vendor/autoload.php';

	MOD\MOD_Core::instance();

	$mod_update = new \Puc_v4p11_Vcs_PluginUpdateChecker(
		new \Puc_v4p11_Vcs_GitLabApi( 'https://git.mercadoonlinedigital.com/free/mod-auto-updates' ),
		__FILE__,
		'mod-auto-updates'
	);

	$mod_update->setBranch('main');

	do_action( 'mod_init' );

	$plugins_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'mod-updates/plugins';
	$themes_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'mod-updates/themes';
	wp_mkdir_p( $plugins_dir );
	wp_mkdir_p( $themes_dir );
}

function mod_plugins_loaded_check() {
	return _mod_load_instances();
}

add_action( 'plugins_loaded', 'mod_plugins_loaded_check', 0 );

function mod_on_activation() {
	add_option( MOD_OPTION_ACTIVATE, true );
	set_transient( 'mod-activation-notice', true, 5 );

	mod_delete_options();

	register_uninstall_hook( __FILE__, 'mod_on_uninstall' );
}

if ( get_transient( 'mod-activation-notice' ) ) {
	function mod_admin_notice_active_token() {
		mod_render_admin_notice_html(
			'Precisamos do seu TOKEN para atualizações autómaticas! <a href="options-general.php?page=mod-auto-updates">Adicionar Token</a>',
			'notice-warning'
		);
	}

	_mod_load_notice( 'admin_notice_active_token' );
	delete_transient( 'mod-activation-notice' );

	return;
}

function mod_on_deactivation() {
	mod_delete_options();
}

function mod_delete_options() {
	delete_option( '_mod_plugins_datajson' );
	delete_option( '_mod_themes_datajson' );
}

function mod_on_uninstall() {}

register_activation_hook( __FILE__, 'mod_on_activation' );
register_deactivation_hook( __FILE__, 'mod_on_deactivation' );
