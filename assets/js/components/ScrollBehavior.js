// ========================================
// Scroll Behavior Component
// ========================================
// Manages header scroll behavior:
// - Shadow on scroll
// - Hide on scroll down / show on scroll up

class ScrollBehavior {
  constructor(element, options = {}) {
    this.element = element;
    this.hideOnScroll = options.hideOnScroll || false;
    this.scrolledClass = options.scrolledClass || 'header-default--scrolled';
    this.hiddenClass = options.hiddenClass || 'header-default--hidden';
    this.scrollThreshold = options.scrollThreshold || 50;
    this.shadowThreshold = options.shadowThreshold || 10;
    this.isMenuOpen = options.isMenuOpen || (() => false);

    this.lastScrollY = 0;

    if (!this.element) return;

    this.init();
  }

  init() {
    window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
  }

  handleScroll() {
    const currentScrollY = window.scrollY;

    this.updateShadow(currentScrollY);

    if (this.hideOnScroll) {
      this.updateVisibility(currentScrollY);
    }

    this.lastScrollY = currentScrollY;
  }

  updateShadow(scrollY) {
    if (scrollY > this.shadowThreshold) {
      this.element.classList.add(this.scrolledClass);
    } else {
      this.element.classList.remove(this.scrolledClass);
    }
  }

  updateVisibility(scrollY) {
    // Don't hide if menu is open
    if (this.isMenuOpen()) return;

    const isScrollingDown = scrollY > this.lastScrollY;
    const isPastThreshold = scrollY > this.scrollThreshold;

    if (isScrollingDown && isPastThreshold) {
      this.hide();
    } else {
      this.show();
    }
  }

  hide() {
    this.element.classList.add(this.hiddenClass);
  }

  show() {
    this.element.classList.remove(this.hiddenClass);
  }

  isHidden() {
    return this.element.classList.contains(this.hiddenClass);
  }

  destroy() {
    window.removeEventListener('scroll', this.handleScroll);
  }
}

export default ScrollBehavior;