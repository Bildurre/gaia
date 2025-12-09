// ========================================
// Hamburger Component
// ========================================
// Manages hamburger button toggle state
// Emits callback for state changes

class Hamburger {
  constructor(element, options = {}) {
    this.element = element;
    this.activeClass = options.activeClass || 'is-active';
    this.onToggle = options.onToggle || null;

    if (!this.element) return;

    this.init();
  }

  init() {
    this.element.addEventListener('click', () => this.toggle());
  }

  toggle() {
    if (this.isActive()) {
      this.close();
    } else {
      this.open();
    }
  }

  open() {
    this.element.classList.add(this.activeClass);
    this.element.setAttribute('aria-expanded', 'true');
    this.notifyToggle(true);
  }

  close() {
    this.element.classList.remove(this.activeClass);
    this.element.setAttribute('aria-expanded', 'false');
    this.notifyToggle(false);
  }

  isActive() {
    return this.element.classList.contains(this.activeClass);
  }

  notifyToggle(isOpen) {
    if (typeof this.onToggle === 'function') {
      this.onToggle(isOpen);
    }
  }
}

export default Hamburger;