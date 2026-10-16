const WIDE_SCREEN_QUERY = '(min-width: 48rem)';

function initSiteHeader() {
  const siteHeader = document.querySelector('[data-site-header]');

  if (!siteHeader) {
    return;
  }

  const menuToggleButton = siteHeader.querySelector('[data-site-header-toggle]');
  const wideScreenMediaQuery = window.matchMedia(WIDE_SCREEN_QUERY);

  const updateHeaderHeightProperty = () => {
    document.documentElement.style.setProperty('--site-header-height', `${siteHeader.getBoundingClientRect().bottom}px`);
  };

  const isMenuOpen = () => siteHeader.classList.contains('is-menu-open');

  const setMenuOpen = (shouldOpen) => {
    siteHeader.classList.toggle('is-menu-open', shouldOpen);
    menuToggleButton?.setAttribute('aria-expanded', String(shouldOpen));
    document.documentElement.classList.toggle('has-open-menu', shouldOpen);

    if (shouldOpen) {
      updateHeaderHeightProperty();
    }
  };

  menuToggleButton?.addEventListener('click', () => {
    setMenuOpen(!isMenuOpen());
  });

  // Close when clicking outside the drawer (backdrop included)
  const menuPanel = siteHeader.querySelector('[data-site-header-panel]');

  document.addEventListener('click', (clickEvent) => {
    const clickedInsideMenu = menuPanel?.contains(clickEvent.target) || menuToggleButton?.contains(clickEvent.target);

    if (isMenuOpen() && !clickedInsideMenu) {
      setMenuOpen(false);
    }
  });

  document.addEventListener('keydown', (keyboardEvent) => {
    if (keyboardEvent.key === 'Escape' && isMenuOpen()) {
      setMenuOpen(false);
      menuToggleButton?.focus();
    }
  });

  wideScreenMediaQuery.addEventListener('change', (mediaQueryEvent) => {
    if (mediaQueryEvent.matches) {
      setMenuOpen(false);
    }
  });

  new ResizeObserver(updateHeaderHeightProperty).observe(siteHeader);
}

initSiteHeader();
