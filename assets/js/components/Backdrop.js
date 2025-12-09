// ========================================
// Backdrop Component
// ========================================
// Generic overlay backdrop for modals, panels, etc.

class Backdrop {
  constructor(element, options = {}) {
    this.element = element;
    this.activeClass = options.activeClass || 'is-active';
    this.onClick = options.onClick || null;
    this.isActive = false;

    if (!this.element) return;

    this.init();
  }

  init() {
    if (this.onClick) {
      this.element.addEventListener('click', () => this.onClick());
    }
  }

  show() {
    if (this.isActive) return;

    this.isActive = true;
    this.element.classList.add(this.activeClass);
  }

  hide() {
    if (!this.isActive) return;

    this.isActive = false;
    this.element.classList.remove(this.activeClass);
  }

  updatePosition(top, height) {
    this.element.style.top = `${top}px`;
    this.element.style.height = height;
  }
}

export default Backdrop;