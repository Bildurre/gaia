function initUserMenu() {
  const userMenu = document.querySelector('[data-user-menu]');

  if (!userMenu) {
    return;
  }

  const toggleButton = userMenu.querySelector('[data-user-menu-toggle]');
  const dropdown = userMenu.querySelector('[data-user-menu-dropdown]');

  const isOpen = () => !dropdown.hidden;

  const setOpen = (shouldOpen) => {
    dropdown.hidden = !shouldOpen;
    toggleButton.setAttribute('aria-expanded', String(shouldOpen));
  };

  toggleButton.addEventListener('click', () => {
    setOpen(!isOpen());
  });

  document.addEventListener('click', (clickEvent) => {
    if (isOpen() && !userMenu.contains(clickEvent.target)) {
      setOpen(false);
    }
  });

  userMenu.addEventListener('keydown', (keyboardEvent) => {
    if (keyboardEvent.key === 'Escape' && isOpen()) {
      keyboardEvent.stopPropagation();
      setOpen(false);
      toggleButton.focus();
    }
  });

  // Close when keyboard focus leaves the menu
  userMenu.addEventListener('focusout', (focusEvent) => {
    if (isOpen() && !userMenu.contains(focusEvent.relatedTarget)) {
      setOpen(false);
    }
  });
}

initUserMenu();
