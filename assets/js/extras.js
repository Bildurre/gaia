const ANNOUNCEMENT_STORAGE_KEY = 'gaia-dismissed-announcement';
const BACK_TO_TOP_SCROLL_THRESHOLD = 600;

function initAnnouncementBar() {
  const announcementBar = document.querySelector('[data-announcement-bar]');
  const closeButton = announcementBar?.querySelector('[data-announcement-close]');

  closeButton?.addEventListener('click', () => {
    const announcementId = announcementBar.dataset.announcementId;
    document.documentElement.setAttribute('data-dismissed-announcement', announcementId);

    try {
      localStorage.setItem(ANNOUNCEMENT_STORAGE_KEY, announcementId);
    } catch {
      // Storage unavailable: hidden until the page is reloaded
    }
  });
}

function initBackToTopButton() {
  const backToTopButton = document.querySelector('[data-back-to-top]');

  if (!backToTopButton) {
    return;
  }

  let isUpdateScheduled = false;

  const updateVisibility = () => {
    isUpdateScheduled = false;
    backToTopButton.classList.toggle('is-visible', window.scrollY > BACK_TO_TOP_SCROLL_THRESHOLD);
  };

  window.addEventListener('scroll', () => {
    if (!isUpdateScheduled) {
      isUpdateScheduled = true;
      window.requestAnimationFrame(updateVisibility);
    }
  }, { passive: true });

  backToTopButton.addEventListener('click', (clickEvent) => {
    clickEvent.preventDefault();
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
    document.querySelector('.skip-link')?.focus({ preventScroll: true });
  });

  updateVisibility();
}

initAnnouncementBar();
initBackToTopButton();
