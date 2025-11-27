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
    this.mobileBreakpoint = 600;

    this.init();
  }

  init() {
    this.bindEvents();
    this.updatePanelPosition();
  }

  bindEvents() {
    this.hamburger?.addEventListener('click', () => this.toggleMenu());
    this.backdrop?.addEventListener('click', () => this.closeMenu());

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && this.isMenuOpen()) {
        this.closeMenu();
      }
    });

    const menuLinks = this.mobilePanel?.querySelectorAll('a');
    menuLinks?.forEach(link => {
      link.addEventListener('click', () => this.closeMenu());
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > this.mobileBreakpoint) {
        this.closeMenu();
      }
      this.updatePanelPosition();
    });
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
}

document.addEventListener('DOMContentLoaded', () => {
  new HeaderDefault();
});

export default HeaderDefault;