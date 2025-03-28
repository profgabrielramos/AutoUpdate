(() => {
  const adminConfig = {
    init() {
      this.selectEnvironmentField = document.querySelector('#mod_token_number');

      if (!this.selectEnvironmentField) {
        return;
      }

      this.setupPasswordVisibility();
    },

    setupPasswordVisibility() {
      this.settingsPassword = document.querySelector('#mod_token_number');

      if (!this.settingsPassword) {
        return;
      }

      this.settingsPassword.type = 'password';

      this.settingsPassword.addEventListener('focusin', () => {
        this.togglePasswordVisibility(true);
      });

      this.settingsPassword.addEventListener('focusout', () => {
        this.togglePasswordVisibility(false);
      });
    },

    togglePasswordVisibility(show) {
      this.settingsPassword.type = show ? 'text' : 'password';
    },
  };

  adminConfig.init();
})();
;(function ($) {
  'use strict';

  $(document).ready(function () {
    $('#mod_update_settings').click(function () {
      var tokenValue = $('#mod_token_number').val();

      if (!tokenValue.trim()) {
        handleAjaxResponse({ success: false, data: { message: 'emptyToken' } });
        return;
      }

      $.ajax({
        url: PPWAGlobalVars.ajaxUrl,
        data: {
          'action': 'mod_admin_token',
          'token': tokenValue
        },
        type: 'POST',
        beforeSend: function (jqXHR) {
          $('#mod-update-loader').removeClass('hidden');
        },
        success: function (data) {
          handleAjaxResponse(data);
        },
        complete: function () {
          $('#mod-update-loader').addClass('hidden');
        },
        error: function (error) {
          console.log(error);
        }
      });
    });
  });

  function handleAjaxResponse(data) {
    if (data.success) {
      $('#mod-email-field, #mod-expire-field').css('display', 'block');
      $('#mod-admin-message').html(mod_success_notice());
    } else {
      $('#mod-email-field, #mod-expire-field').css('display', 'none');
      $('#mod-admin-message').html(mod_error_notice(data.data.message));
    }
  }

  function mod_success_notice() {
    return '<div class="updated notice"><p><b>MOD:</b> Configurações atualizadas com sucesso!</p></div>';
  }

  function mod_error_notice(message) {
    let errorMessages = {
      'Invalid key': 'Token inválido!',
      'emptyToken': 'Token obrigatório!',
      null: 'Nenhuma chave encontrada!',
    };

    let errorMessage = errorMessages[message] || `Erro: ${message}`;

    return `<div class="error notice"><p><b>MOD:</b> ${errorMessage}</p></div>`;
  }

}(jQuery));