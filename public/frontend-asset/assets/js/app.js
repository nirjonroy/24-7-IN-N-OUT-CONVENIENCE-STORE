(() => {
  const root = document.documentElement;
  const themeButtons = document.querySelectorAll('[data-theme-toggle]');
  const updateThemeIcons = () => {
    const dark = root.classList.contains('dark');
    document.querySelectorAll('[data-theme-icon="light"]').forEach(el => el.hidden = dark);
    document.querySelectorAll('[data-theme-icon="dark"]').forEach(el => el.hidden = !dark);
  };
  updateThemeIcons();
  themeButtons.forEach(button => button.addEventListener('click', () => {
    const nextDark = !root.classList.contains('dark');
    root.classList.toggle('dark', nextDark);
    localStorage.setItem('theme', nextDark ? 'dark' : 'light');
    updateThemeIcons();
  }));

  const menuButton = document.querySelector('[data-menu-toggle]');
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  if (menuButton && mobileMenu) {
    menuButton.addEventListener('click', () => {
      const opening = mobileMenu.hidden;
      mobileMenu.hidden = !opening;
      menuButton.setAttribute('aria-expanded', String(opening));
    });
  }

  document.querySelectorAll('[data-year]').forEach(el => el.textContent = new Date().getFullYear());
})();
