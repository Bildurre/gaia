/**
 * Browsers ignore autocomplete="off" on password fields. Fields with
 * [data-no-autofill] stay read-only (browsers skip read-only fields when
 * autofilling) until the user focuses them.
 */
function initNoAutofillFields() {
  document.querySelectorAll('[data-no-autofill]').forEach((passwordField) => {
    passwordField.readOnly = true;

    const enableTyping = () => {
      passwordField.readOnly = false;
    };

    passwordField.addEventListener('focus', enableTyping);
    passwordField.addEventListener('pointerdown', enableTyping);
  });

  // In case the browser autofilled before this script ran
  window.addEventListener('load', () => {
    document.querySelectorAll('[data-no-autofill]').forEach((passwordField) => {
      if (passwordField !== document.activeElement) {
        passwordField.value = '';
      }
    });
  });
}

initNoAutofillFields();
