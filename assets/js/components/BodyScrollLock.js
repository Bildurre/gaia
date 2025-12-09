// ========================================
// Body Scroll Lock Component
// ========================================
// Utility to lock/unlock body scroll when modals/panels are open
// Preserves scroll position and handles iOS quirks

const BodyScrollLock = {
  scrollPosition: 0,
  isLocked: false,

  lock() {
    if (this.isLocked) return;

    this.scrollPosition = window.scrollY;
    this.isLocked = true;

    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${this.scrollPosition}px`;
    document.body.style.width = '100%';
  },

  unlock() {
    if (!this.isLocked) return;

    this.isLocked = false;

    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';

    window.scrollTo(0, this.scrollPosition);
  }
};

export default BodyScrollLock;