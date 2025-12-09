// ========================================
// Scroll Behavior Component
// ========================================
// Handles sticky header behavior with optional hide on scroll

class ScrollBehavior {
  constructor(element, options = {}) {
    this.element = element;
    this.hideOnScroll = options.hideOnScroll || false;
    this.isMenuOpen = options.isMenuOpen || (() => false);
    this.scrollThreshold = options.scrollThreshold || 50;
    this.scrolledThreshold = options.scrolledThreshold || 10;
    this.hiddenClass = options.hiddenClass || 'header-default--hidden';
    this.scrolledClass = options.scrolledClass || 'header-default--scrolled';

    this.lastScrollY = 0;
    this.ticking = false;

    if (!this.element) return;

    this.init();
  }

  init() {
    this.handleScroll = this.handleScroll.bind(this);
    window.addEventListener('scroll', this.handleScroll, { passive: true });
  }

  handleScroll() {
    if (!this.ticking) {
      window.requestAnimationFrame(() => {
        this.updateScrollState();
        this.ticking = false;
      });
      this.ticking = true;
    }
  }

  updateScrollState() {
    const currentScrollY = window.scrollY;

    // Scrolled state (for shadow)
    if (currentScrollY > this.scrolledThreshold) {
      this.element.classList.add(this.scrolledClass);
    } else {
      this.element.classList.remove(this.scrolledClass);
    }

    // Hide/show on scroll
    if (this.hideOnScroll && !this.isMenuOpen()) {
      const isScrollingDown = currentScrollY > this.lastScrollY;
      const isPastThreshold = currentScrollY > this.scrollThreshold;

      if (isScrollingDown && isPastThreshold) {
        this.element.classList.add(this.hiddenClass);
      } else {
        this.element.classList.remove(this.hiddenClass);
      }
    }

    this.lastScrollY = currentScrollY;
  }

  destroy() {
    window.removeEventListener('scroll', this.handleScroll);
  }
}

export default ScrollBehavior;