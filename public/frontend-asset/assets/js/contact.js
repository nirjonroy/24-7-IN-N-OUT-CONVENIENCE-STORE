document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-contact-form]').forEach((form) => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('[data-contact-submit]');

      if (button) {
        button.disabled = true;
        button.textContent = 'Sending...';
      }
    });
  });
});
