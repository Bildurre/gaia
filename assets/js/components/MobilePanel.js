// ========================================
// Mobile Panel Component
// ========================================
// Generic slide-in panel from left side

class MobilePanel {
  constructor(element, options = {}) {
    this.element = element;
    this.header = options.header || null;
    this.openClass = options.openClass || 'is-open';
    this.onLinkClick = options.onLinkClick || null;
    this._isOpen = false;

    if (!this.element) return;

    this.init();
  }

  init() {
    // Close on link click
    if (this.onLinkClick) {
      const links = this.element.querySelectorAll('a');
      links.forEach(link => {
        link.addEventListener('click', () => this.onLinkClick());
      });
    }
  }

  open() {
    if (this._isOpen) return;

    this._isOpen = true;
    this.element.classList.add(this.openClass);
  }

  close() {
    if (!this._isOpen) return;

    this._isOpen = false;
    this.element.classList.remove(this.openClass);
  }

  isOpen() {
    return this._isOpen;
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
}

export default MobilePanel;