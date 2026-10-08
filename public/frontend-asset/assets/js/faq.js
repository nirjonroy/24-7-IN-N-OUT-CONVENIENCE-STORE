document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-faq-accordion]').forEach((accordion) => {
    accordion.querySelectorAll('[data-faq-trigger]').forEach((button) => {
      button.addEventListener('click', () => {
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        const icon = button.querySelector('[data-faq-icon]');
        const expanded = button.getAttribute('aria-expanded') === 'true';

        button.setAttribute('aria-expanded', String(!expanded));

        if (panel) {
          panel.hidden = expanded;
        }

        if (icon) {
          icon.style.transform = expanded ? '' : 'rotate(180deg)';
        }
      });
    });
  });
});
