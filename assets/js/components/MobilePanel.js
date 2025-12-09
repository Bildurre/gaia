// ========================================
// Mobile Panel Component
// ========================================
// Manages slide-in panel for mobile navigation
// Handles positioning relative to header and admin bar

class MobilePanel {
  constructor(element, options = {}) {
    this.element = element;
    this.openClass = options.openClass || 'is-open';
    this.header = options.header || null;
    this.onLinkClick = options.onLinkClick || null;

    if (!this.element) return;

    this.init();
  }

  init() {
    this.bindLinkClicks();
  }

  bindLinkClicks() {
    const links = this.element.querySelectorAll('a');
    links.forEach(link => {
      link.addEventListener('click', () => {
        if (typeof this.onLinkClick === 'function') {
          this.onLinkClick();
        }
      });
    });
  }

  open() {
    this.updatePosition();
    this.element.classList.add(this.openClass);
  }

  close() {
    this.element.classList.remove(this.openClass);
  }

  isOpen() {
    return this.element.classList.contains(this.openClass);
  }

  updatePosition() {
    const adminBar = document.getElementById('wpadminbar');
    const adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
    const headerHeight = this.header ? this.header.offsetHeight : 0;
    const topOffset = adminBarHeight + headerHeight;

    this.element.style.top = `${topOffset}px`;
    this.element.style.height = `calc(100vh - ${topOffset}px)`;

    return topOffset;
  }

  resetPosition() {
    this.element.style.top = '';
    this.element.style.height = '';
  }

  getTopOffset() {
    const adminBar = document.getElementById('wpadminbar');
    const adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
    const headerHeight = this.header ? this.header.offsetHeight : 0;
    return adminBarHeight + headerHeight;
  }
}

export default MobilePanel;