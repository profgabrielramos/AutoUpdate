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
