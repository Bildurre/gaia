const WIDE_SCREEN_QUERY = '(min-width: 48rem)';
const SCROLL_DELTA_THRESHOLD = 8;
const TRANSPARENT_SCROLL_THRESHOLD = 16;
const COLOR_SCHEME_STORAGE_KEY = 'gaia-color-scheme';

function initSiteHeader() {
  const siteHeader = document.querySelector('[data-site-header]');

  if (!siteHeader) {
    return;
  }

  const menuToggleButton = siteHeader.querySelector('[data-site-header-toggle]');
  const menuPanel = siteHeader.querySelector('[data-site-header-panel]');
  const wideScreenMediaQuery = window.matchMedia(WIDE_SCREEN_QUERY);
  const isMinimalLayout = siteHeader.classList.contains('site-header--minimal');
  const isTransparentHeader = siteHeader.hasAttribute('data-transparent');

  const updateHeaderHeightProperty = () => {
    document.documentElement.style.setProperty('--site-header-height', `${siteHeader.getBoundingClientRect().bottom}px`);
  };

  // Transparent header: dark scheme over the hero, the background scheme
  // (data-solid-scheme) once the page scrolls or a panel is open
  const solidSchemeClass = siteHeader.dataset.solidScheme || '';

  const updateTransparency = () => {
    const isPageScrolled = window.scrollY > TRANSPARENT_SCROLL_THRESHOLD;
    siteHeader.classList.toggle('is-page-scrolled', isPageScrolled);

    if (!isTransparentHeader) {
      return;
    }

    const isSolid = isPageScrolled || siteHeader.classList.contains('is-menu-open') || siteHeader.classList.contains('is-search-open');
    siteHeader.classList.toggle('is-scrolled', isSolid);
    siteHeader.classList.remove('scheme-dark', 'scheme-light');
    siteHeader.classList.add(...(isSolid ? [solidSchemeClass] : ['scheme-dark']).filter(Boolean));
  };

  // Drawer menu
  const isMenuOpen = () => siteHeader.classList.contains('is-menu-open');
  const usesDrawer = () => isMinimalLayout || !wideScreenMediaQuery.matches;

  const setMenuOpen = (shouldOpen) => {
    siteHeader.classList.toggle('is-menu-open', shouldOpen);
    menuToggleButton?.setAttribute('aria-expanded', String(shouldOpen));
    document.documentElement.classList.toggle('has-open-menu', shouldOpen);

    if (shouldOpen) {
      siteHeader.classList.remove('is-hidden');
      updateHeaderHeightProperty();
    }

    updateTransparency();
  };

  menuToggleButton?.addEventListener('click', () => {
    setMenuOpen(!isMenuOpen());
  });

  wideScreenMediaQuery.addEventListener('change', () => {
    if (!usesDrawer()) {
      setMenuOpen(false);
    }

    closeSubmenus();
  });

  // Submenus: dropdowns on wide screens, accordions in the drawer
  const submenuToggleButtons = [...siteHeader.querySelectorAll('[data-submenu-toggle]')];

  const setSubmenuOpen = (toggleButton, shouldOpen) => {
    toggleButton.setAttribute('aria-expanded', String(shouldOpen));
    toggleButton.closest('.menu-item-has-children')?.classList.toggle('is-submenu-open', shouldOpen);
  };

  const closeSubmenus = (exceptButton = null) => {
    submenuToggleButtons.forEach((toggleButton) => {
      if (toggleButton !== exceptButton) {
        setSubmenuOpen(toggleButton, false);
      }
    });
  };

  submenuToggleButtons.forEach((toggleButton) => {
    toggleButton.addEventListener('click', () => {
      const shouldOpen = toggleButton.getAttribute('aria-expanded') !== 'true';

      if (!usesDrawer()) {
        closeSubmenus(toggleButton);
      }

      setSubmenuOpen(toggleButton, shouldOpen);
    });
  });

  // Search panel
  const searchToggleButton = siteHeader.querySelector('[data-site-header-search-toggle]');
  const searchPanel = siteHeader.querySelector('[data-site-header-search]');

  const setSearchOpen = (shouldOpen) => {
    if (!searchPanel) {
      return;
    }

    searchPanel.hidden = !shouldOpen;
    searchToggleButton?.setAttribute('aria-expanded', String(shouldOpen));
    siteHeader.classList.toggle('is-search-open', shouldOpen);
    updateTransparency();
    updateHeaderHeightProperty();

    if (shouldOpen) {
      searchPanel.querySelector('input')?.focus();
    }
  };

  searchToggleButton?.addEventListener('click', () => {
    setSearchOpen(searchPanel.hidden);
  });

  // Light / dark / browser switch ("auto" color mode)
  const schemeSwitcher = siteHeader.querySelector('[data-scheme-switcher]');
  const schemeSwitcherToggle = schemeSwitcher?.querySelector('[data-scheme-switcher-toggle]');
  const schemeSwitcherMenu = schemeSwitcher?.querySelector('[data-scheme-switcher-menu]');
  const schemeChoiceButtons = schemeSwitcher ? [...schemeSwitcher.querySelectorAll('[data-scheme-choice]')] : [];
  const systemDarkQuery = window.matchMedia('(prefers-color-scheme: dark)');

  const isSchemeMenuOpen = () => schemeSwitcherMenu && !schemeSwitcherMenu.hidden;

  const setSchemeMenuOpen = (shouldOpen) => {
    if (!schemeSwitcherMenu) {
      return;
    }

    schemeSwitcherMenu.hidden = !shouldOpen;
    schemeSwitcherToggle.setAttribute('aria-expanded', String(shouldOpen));
  };

  const applyColorPreference = (colorPreference) => {
    const rootElement = document.documentElement;
    const resolvedScheme = colorPreference === 'system' ? (systemDarkQuery.matches ? 'dark' : 'light') : colorPreference;

    rootElement.dataset.colorPreference = colorPreference;
    rootElement.dataset.colorScheme = resolvedScheme;
    schemeChoiceButtons.forEach((choiceButton) => {
      choiceButton.setAttribute('aria-pressed', String(choiceButton.dataset.schemeChoice === colorPreference));
    });
  };

  schemeSwitcherToggle?.addEventListener('click', () => {
    setSchemeMenuOpen(!isSchemeMenuOpen());
  });

  schemeChoiceButtons.forEach((choiceButton) => {
    choiceButton.addEventListener('click', () => {
      const colorPreference = choiceButton.dataset.schemeChoice;
      applyColorPreference(colorPreference);

      try {
        if (colorPreference === 'system') {
          localStorage.removeItem(COLOR_SCHEME_STORAGE_KEY);
        } else {
          localStorage.setItem(COLOR_SCHEME_STORAGE_KEY, colorPreference);
        }
      } catch {
        // Storage unavailable: the choice lasts until the page is reloaded
      }

      setSchemeMenuOpen(false);
      schemeSwitcherToggle.focus();
    });
  });

  // Browser preference changes while the page is open
  systemDarkQuery.addEventListener('change', () => {
    if (document.documentElement.dataset.colorPreference === 'system') {
      applyColorPreference('system');
    }
  });

  if (schemeSwitcher) {
    applyColorPreference(document.documentElement.dataset.colorPreference || 'system');
  }

  // Clicks outside and Escape close menus, submenus and search
  document.addEventListener('click', (clickEvent) => {
    if (isMenuOpen() && !menuPanel?.contains(clickEvent.target) && !menuToggleButton?.contains(clickEvent.target)) {
      setMenuOpen(false);
    }

    if (!clickEvent.target.closest('.menu-item-has-children')) {
      closeSubmenus();
    }

    if (searchPanel && !searchPanel.hidden && !searchPanel.contains(clickEvent.target) && !searchToggleButton?.contains(clickEvent.target)) {
      setSearchOpen(false);
    }

    if (isSchemeMenuOpen() && !schemeSwitcher.contains(clickEvent.target)) {
      setSchemeMenuOpen(false);
    }
  });

  document.addEventListener('keydown', (keyboardEvent) => {
    if (keyboardEvent.key !== 'Escape') {
      return;
    }

    if (isMenuOpen()) {
      setMenuOpen(false);
      menuToggleButton?.focus();
    }

    if (searchPanel && !searchPanel.hidden) {
      setSearchOpen(false);
      searchToggleButton?.focus();
    }

    if (isSchemeMenuOpen()) {
      setSchemeMenuOpen(false);
      schemeSwitcherToggle.focus();
    }

    closeSubmenus();
  });

  // Hide when scrolling down, show when scrolling up
  const hidesOnScroll = siteHeader.hasAttribute('data-hide-on-scroll');
  let lastScrollPosition = window.scrollY;
  let isScrollUpdateScheduled = false;

  const handleScroll = () => {
    isScrollUpdateScheduled = false;
    updateTransparency();

    if (!hidesOnScroll) {
      return;
    }

    const currentScrollPosition = window.scrollY;
    const scrollDelta = currentScrollPosition - lastScrollPosition;
    const hasOpenPanel = isMenuOpen() || siteHeader.classList.contains('is-search-open') || siteHeader.querySelector('.is-submenu-open');

    if (hasOpenPanel || Math.abs(scrollDelta) < SCROLL_DELTA_THRESHOLD) {
      return;
    }

    siteHeader.classList.toggle('is-hidden', scrollDelta > 0 && currentScrollPosition > siteHeader.offsetHeight);
    lastScrollPosition = currentScrollPosition;
  };

  window.addEventListener('scroll', () => {
    if (!isScrollUpdateScheduled) {
      isScrollUpdateScheduled = true;
      window.requestAnimationFrame(handleScroll);
    }
  }, { passive: true });

  siteHeader.addEventListener('focusin', () => {
    siteHeader.classList.remove('is-hidden');
  });

  updateTransparency();
  new ResizeObserver(updateHeaderHeightProperty).observe(siteHeader);
}

initSiteHeader();
