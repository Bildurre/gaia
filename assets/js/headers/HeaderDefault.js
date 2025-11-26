// ========================================
// Header Default
// ========================================

import MobileNavigation from '../components/MobileNavigation.js';

class HeaderDefault {
  constructor() {
    this.header = document.querySelector('.header-default');
    
    if (!this.header) return;

    this.init();
  }

  init() {
    this.mobileNav = new MobileNavigation({
      headerSelector: '.header-default',
      mobileBreakpoint: 600
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new HeaderDefault();
});

export default HeaderDefault;