<?php
namespace MOD\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;

class MOD_Settings
{
	public static function mod_admin_page_html() {
	?>
        <div class="wrap" oncontextmenu="return false">
            <h1><?php echo __( 'MOD Atualizções Automáticas', Core::SLUG ); ?></h1>
            <p><?php echo __( 'Versão', Core::SLUG ) . ': ' . MOD_VERSION; ?></p>
            <div id="mod-admin-message"></div>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'mod_auto_updates_group' );
                    do_settings_sections( 'mod-auto-updates' );
                ?>
                <div class="mod-submit-settings">
                    <input name='mod_update_settings' type='button' id='mod_update_settings' class='button-primary' value='<?php _e("Save Changes") ?>' />
                </div>
                <div id="mod-update-loader" class="mod-update-dual-ring hidden overlay"></div>
            </form>
        </div>
    <?php
    }
}
