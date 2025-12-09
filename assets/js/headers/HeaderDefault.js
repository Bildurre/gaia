// ========================================
// Header Default
// ========================================
// Composes: Hamburger, MobilePanel, Backdrop, ScrollBehavior

import Hamburger from '../components/Hamburger.js';
import MobilePanel from '../components/MobilePanel.js';
import Backdrop from '../components/Backdrop.js';
import ScrollBehavior from '../components/ScrollBehavior.js';
import BodyScrollLock from '../components/BodyScrollLock.js';

class HeaderDefault {
  constructor(selector = '.header-default') {
    this.header = document.querySelector(selector);
    
    if (!this.header) return;

    this.mobileBreakpoint = 600;
    this.hideOnScroll = this.header.dataset.hideOnScroll === 'true';
    this.isSticky = this.header.classList.contains('header-default--sticky');

    this.initComponents();
    this.bindEvents();
  }

  initComponents() {
    // Hamburger
    this.hamburger = new Hamburger(
      this.header.querySelector('.header-default__hamburger'),
      {
        onToggle: (isOpen) => this.handleMenuToggle(isOpen)
      }
    );

    // Mobile Panel
    this.mobilePanel = new MobilePanel(
      this.header.querySelector('.header-default__mobile-panel'),
      {
        header: this.header,
        onLinkClick: () => this.closeMenu()
      }
    );

    // Backdrop
    this.backdrop = new Backdrop(
      this.header.querySelector('.header-default__backdrop'),
      {
        onClick: () => this.closeMenu()
      }
    );

    // Scroll Behavior (only if sticky)
    if (this.isSticky) {
      this.scrollBehavior = new ScrollBehavior(
        this.header,
        {
          hideOnScroll: this.hideOnScroll,
          isMenuOpen: () => this.isMenuOpen()
        }
      );
    }
  }

  bindEvents() {
    // Escape key
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && this.isMenuOpen()) {
        this.closeMenu();
      }
    });

    // Resize
    window.addEventListener('resize', () => {
      if (window.innerWidth > this.mobileBreakpoint) {
        this.closeMenu();
      }
    });
  }

  // ========================================
  // Menu State Management
  // ========================================

  handleMenuToggle(isOpen) {
    if (isOpen) {
      this.openMenu();
    } else {
      this.closeMenu();
    }
  }

  openMenu() {
    const topOffset = this.mobilePanel.updatePosition();
    this.backdrop.updatePosition(topOffset, `calc(100vh - ${topOffset}px)`);
    
    this.mobilePanel.open();
    this.backdrop.show();
    BodyScrollLock.lock();
  }

  closeMenu() {
    this.hamburger.close(true); // silent = true, no dispara callback
    this.mobilePanel.close();
    this.backdrop.hide();
    BodyScrollLock.unlock();
  }

  isMenuOpen() {
    return this.mobilePanel?.isOpen() || false;
  }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
  new HeaderDefault();
});

export default HeaderDefault;