// ========================================
// Hamburger Button Component
// ========================================
// Generic hamburger button with toggle functionality

class Hamburger {
  constructor(element, options = {}) {
    this.element = element;
    this.isActive = false;
    this.options = {
      activeClass: options.activeClass || 'is-active',
      onToggle: options.onToggle || null,
    };

    if (!this.element) return;

    this.init();
  }

  init() {
    this.element.addEventListener('click', () => this.toggle());
  }

  toggle() {
    if (this.isActive) {
      this.close();
    } else {
      this.open();
    }
  }

  open() {
    if (this.isActive) return;

    this.isActive = true;
    this.element.classList.add(this.options.activeClass);
    this.element.setAttribute('aria-expanded', 'true');

    if (this.options.onToggle) {
      this.options.onToggle(true);
    }
  }

  close(silent = false) {
    if (!this.isActive) return;

    this.isActive = false;
    this.element.classList.remove(this.options.activeClass);
    this.element.setAttribute('aria-expanded', 'false');

    if (!silent && this.options.onToggle) {
      this.options.onToggle(false);
    }
  }
}

export default Hamburger;