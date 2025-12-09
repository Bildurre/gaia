// ========================================
// Backdrop Component
// ========================================
// Manages backdrop overlay visibility
// Supports click to close callback

class Backdrop {
  constructor(element, options = {}) {
    this.element = element;
    this.activeClass = options.activeClass || 'is-active';
    this.onClick = options.onClick || null;

    if (!this.element) return;

    this.init();
  }

  init() {
    this.element.addEventListener('click', () => {
      if (typeof this.onClick === 'function') {
        this.onClick();
      }
    });
  }

  show() {
    this.element.classList.add(this.activeClass);
  }

  hide() {
    this.element.classList.remove(this.activeClass);
  }

  isActive() {
    return this.element.classList.contains(this.activeClass);
  }

  updatePosition(top, height) {
    this.element.style.top = typeof top === 'number' ? `${top}px` : top;
    this.element.style.height = height;
  }

  resetPosition() {
    this.element.style.top = '';
    this.element.style.height = '';
  }
}

export default Backdrop;