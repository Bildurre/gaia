// ========================================
// Header Default
// ========================================

class HeaderDefault {
  constructor() {
    this.header = document.querySelector('.header-default');
    
    if (!this.header) return;

    this.hamburger = this.header.querySelector('.header-default__hamburger');
    this.mobilePanel = this.header.querySelector('.header-default__mobile-panel');
    this.backdrop = this.header.querySelector('.header-default__backdrop');
    this.searchToggle = this.header.querySelector('.header-default__search-toggle');
    this.searchModal = this.header.querySelector('.header-default__search-modal');
    this.searchClose = this.header.querySelector('.header-default__search-close');
    
    this.mobileBreakpoint = 600;
    this.hideOnScroll = this.header.dataset.hideOnScroll === 'true';
    this.lastScrollY = 0;
    this.scrollThreshold = 50;

    this.init();
  }

  init() {
    this.bindEvents();
    this.updatePanelPosition();
  }

  bindEvents() {
    // Mobile menu
    this.hamburger?.addEventListener('click', () => this.toggleMenu());
    this.backdrop?.addEventListener('click', () => this.closeMenu());

    // Search modal
    this.searchToggle?.addEventListener('click', () => this.openSearch());
    this.searchClose?.addEventListener('click', () => this.closeSearch());
    this.searchModal?.addEventListener('click', (e) => {
      if (e.target === this.searchModal) this.closeSearch();
    });

    // Keyboard
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        if (this.isMenuOpen()) this.closeMenu();
        if (this.isSearchOpen()) this.closeSearch();
      }
    });

    // Close menu on link click
    const menuLinks = this.mobilePanel?.querySelectorAll('a');
    menuLinks?.forEach(link => {
      link.addEventListener('click', () => this.closeMenu());
    });

    // Resize
    window.addEventListener('resize', () => {
      if (window.innerWidth > this.mobileBreakpoint) {
        this.closeMenu();
      }
      this.updatePanelPosition();
    });

    // Scroll behavior
    if (this.hideOnScroll) {
      window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
    } else if (this.header.classList.contains('header-default--sticky')) {
      window.addEventListener('scroll', () => this.handleScrollSimple(), { passive: true });
    }
  }

  // Scroll handling for hide/show
  handleScroll() {
    const currentScrollY = window.scrollY;
    
    // Add scrolled class for shadow
    if (currentScrollY > 10) {
      this.header.classList.add('header-default--scrolled');
    } else {
      this.header.classList.remove('header-default--scrolled');
    }

    // Don't hide/show if menu is open
    if (this.isMenuOpen()) return;

    // Hide on scroll down, show on scroll up
    if (currentScrollY > this.lastScrollY && currentScrollY > this.scrollThreshold) {
      // Scrolling down
      this.header.classList.add('header-default--hidden');
    } else {
      // Scrolling up
      this.header.classList.remove('header-default--hidden');
    }

    this.lastScrollY = currentScrollY;
  }

  // Simple scroll handling (just shadow)
  handleScrollSimple() {
    if (window.scrollY > 10) {
      this.header.classList.add('header-default--scrolled');
    } else {
      this.header.classList.remove('header-default--scrolled');
    }
  }

  updatePanelPosition() {
    if (!this.mobilePanel) return;

    const adminBar = document.getElementById('wpadminbar');
    const adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
    const headerHeight = this.header.offsetHeight;
    const topOffset = adminBarHeight + headerHeight;

    this.mobilePanel.style.top = `${topOffset}px`;
    this.mobilePanel.style.height = `calc(100vh - ${topOffset}px)`;
    this.backdrop.style.top = `${topOffset}px`;
    this.backdrop.style.height = `calc(100vh - ${topOffset}px)`;
  }

  // Menu methods
  toggleMenu() {
    if (this.isMenuOpen()) {
      this.closeMenu();
    } else {
      this.openMenu();
    }
  }

  openMenu() {
    this.updatePanelPosition();
    this.hamburger.classList.add('is-active');
    this.hamburger.setAttribute('aria-expanded', 'true');
    this.mobilePanel.classList.add('is-open');
    this.backdrop.classList.add('is-active');
    document.body.style.overflow = 'hidden';
  }

  closeMenu() {
    this.hamburger.classList.remove('is-active');
    this.hamburger.setAttribute('aria-expanded', 'false');
    this.mobilePanel.classList.remove('is-open');
    this.backdrop.classList.remove('is-active');
    document.body.style.overflow = '';
  }

  isMenuOpen() {
    return this.mobilePanel?.classList.contains('is-open');
  }

  // Search methods
  openSearch() {
    this.searchModal?.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    
    // Focus search input
    setTimeout(() => {
      this.searchModal?.querySelector('.search-field')?.focus();
    }, 100);
  }

  closeSearch() {
    this.searchModal?.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  isSearchOpen() {
    return this.searchModal?.classList.contains('is-open');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new HeaderDefault();
});

export default HeaderDefault;