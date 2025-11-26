// ========================================
// Mobile Navigation Base Component
// ========================================

class MobileNavigation {
  constructor(options = {}) {
    this.navigation = document.querySelector(options.navigationSelector || '.wp-block-navigation');
    this.menuContainer = document.querySelector(options.menuContainerSelector || '.wp-block-navigation__responsive-container');
    this.header = document.querySelector(options.headerSelector);
    this.adminBar = document.getElementById('wpadminbar');
    this.mobileBreakpoint = options.mobileBreakpoint || 600;
    
    this.hamburger = null;
    this.backdrop = null;

    if (!this.navigation || !this.menuContainer) return;

    this.init();
  }

  init() {
    this.createHamburger();
    this.createBackdrop();
    this.bindEvents();
  }

  createHamburger() {
    this.hamburger = document.createElement('button');
    this.hamburger.className = 'nav-hamburger';
    this.hamburger.setAttribute('aria-label', 'Toggle menu');
    this.hamburger.setAttribute('aria-expanded', 'false');
    this.hamburger.innerHTML = `
      <span class="nav-hamburger__bar"></span>
      <span class="nav-hamburger__bar"></span>
      <span class="nav-hamburger__bar"></span>
    `;

    this.navigation.appendChild(this.hamburger);
  }

  createBackdrop() {
    this.backdrop = document.createElement('div');
    this.backdrop.className = 'nav-backdrop';
    document.body.appendChild(this.backdrop);
  }

  bindEvents() {
    this.hamburger.addEventListener('click', () => this.toggleMenu());
    this.backdrop.addEventListener('click', () => this.closeMenu());

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && this.isMenuOpen()) {
        this.closeMenu();
      }
    });

    const menuLinks = this.menuContainer.querySelectorAll('a');
    menuLinks.forEach(link => {
      link.addEventListener('click', () => this.closeMenu());
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > this.mobileBreakpoint) {
        this.closeMenu();
        this.resetMenuStyles();
      }
    });
  }

  getAdminBarHeight() {
    return this.adminBar ? this.adminBar.offsetHeight : 0;
  }

  getHeaderHeight() {
    return this.header ? this.header.offsetHeight : 0;
  }

  getTotalOffset() {
    return this.getAdminBarHeight() + this.getHeaderHeight();
  }

  updateMenuPosition() {
    const totalOffset = this.getTotalOffset();
    
    this.menuContainer.style.top = `${totalOffset}px`;
    this.menuContainer.style.height = `calc(100vh - ${totalOffset}px)`;
    this.backdrop.style.top = `${totalOffset}px`;
    this.backdrop.style.height = `calc(100vh - ${totalOffset}px)`;
  }

  resetMenuStyles() {
    this.menuContainer.style.top = '';
    this.menuContainer.style.height = '';
    this.backdrop.style.top = '';
    this.backdrop.style.height = '';
  }

  toggleMenu() {
    if (this.isMenuOpen()) {
      this.closeMenu();
    } else {
      this.openMenu();
    }
  }

  openMenu() {
    this.updateMenuPosition();
    
    this.hamburger.classList.add('is-active');
    this.hamburger.setAttribute('aria-expanded', 'true');
    this.menuContainer.classList.add('is-menu-open');
    this.backdrop.classList.add('is-active');
    document.body.style.overflow = 'hidden';
  }

  closeMenu() {
    this.hamburger.classList.remove('is-active');
    this.hamburger.setAttribute('aria-expanded', 'false');
    this.menuContainer.classList.remove('is-menu-open');
    this.backdrop.classList.remove('is-active');
    document.body.style.overflow = '';
  }

  isMenuOpen() {
    return this.menuContainer.classList.contains('is-menu-open');
  }
}

export default MobileNavigation;