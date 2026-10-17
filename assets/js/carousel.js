// Carousels (slider, latest posts, logos): a horizontal scroll-snap track
// with previous / next buttons, dots, keyboard arrows and optional
// autoplay that stops on hover, focus, a hidden tab, the pause button or
// "reduce motion". Without JavaScript the track still scrolls.
//
// Markup: [data-carousel] (data-autoplay="seconds", data-label-slide,
// data-label-go-to) > [data-carousel-track] > slides, plus the controls of
// template-parts/carousel-controls.php.

function initCarousel(carouselElement) {
  const track = carouselElement.querySelector('[data-carousel-track]');
  const slides = track ? [...track.children] : [];
  const previousButton = carouselElement.querySelector('[data-carousel-previous]');
  const nextButton = carouselElement.querySelector('[data-carousel-next]');
  const dotsContainer = carouselElement.querySelector('[data-carousel-dots]');
  const pauseButton = carouselElement.querySelector('[data-carousel-pause]');
  const autoplaySeconds = parseFloat(carouselElement.dataset.autoplay) || 0;
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!track || slides.length < 2) {
    carouselElement.classList.add('is-static');
    return;
  }

  let snapPositions = [];
  let currentPosition = 0;
  let autoplayTimer = null;
  let isPausedByUser = false;
  let isPausedByInteraction = false;

  const slideLabel = carouselElement.dataset.labelSlide || '%1$d / %2$d';
  const goToLabel = carouselElement.dataset.labelGoTo || '%d';

  slides.forEach((slide, slideIndex) => {
    slide.setAttribute('role', 'group');
    slide.setAttribute('aria-roledescription', 'slide');
    slide.setAttribute('aria-label', slideLabel.replace('%1$d', slideIndex + 1).replace('%2$d', slides.length));
  });

  // Scroll positions where a slide starts, up to the end of the track
  const measurePositions = () => {
    const maximumScroll = track.scrollWidth - track.clientWidth;
    const firstOffset = slides[0].offsetLeft;
    const slideOffsets = slides.map((slide) => slide.offsetLeft - firstOffset).filter((slideOffset) => slideOffset < maximumScroll - 2);

    snapPositions = maximumScroll > 2 ? [...slideOffsets, maximumScroll] : [0];
    carouselElement.classList.toggle('is-static', snapPositions.length < 2);
    buildDots();
    updateCurrentPosition();
  };

  const buildDots = () => {
    if (!dotsContainer) {
      return;
    }

    dotsContainer.replaceChildren(...snapPositions.map((snapPosition, positionIndex) => {
      const dotButton = document.createElement('button');
      dotButton.type = 'button';
      dotButton.className = 'carousel__dot';
      dotButton.setAttribute('aria-label', goToLabel.replace('%d', positionIndex + 1));
      dotButton.addEventListener('click', () => {
        goToPosition(positionIndex);
        restartAutoplay();
      });
      return dotButton;
    }));
  };

  const updateCurrentPosition = () => {
    const scrollLeft = track.scrollLeft;
    currentPosition = snapPositions.reduce((closestIndex, snapPosition, positionIndex) => (
      Math.abs(snapPosition - scrollLeft) < Math.abs(snapPositions[closestIndex] - scrollLeft) ? positionIndex : closestIndex
    ), 0);

    dotsContainer?.querySelectorAll('.carousel__dot').forEach((dotButton, dotIndex) => {
      dotButton.toggleAttribute('aria-current', dotIndex === currentPosition);
    });
  };

  const goToPosition = (positionIndex) => {
    const targetIndex = (positionIndex + snapPositions.length) % snapPositions.length;
    track.scrollTo({ left: snapPositions[targetIndex], behavior: prefersReducedMotion ? 'auto' : 'smooth' });
  };

  previousButton?.addEventListener('click', () => {
    goToPosition(currentPosition - 1);
    restartAutoplay();
  });

  nextButton?.addEventListener('click', () => {
    goToPosition(currentPosition + 1);
    restartAutoplay();
  });

  track.addEventListener('keydown', (keyboardEvent) => {
    if (keyboardEvent.key === 'ArrowRight' || keyboardEvent.key === 'ArrowLeft') {
      keyboardEvent.preventDefault();
      goToPosition(currentPosition + (keyboardEvent.key === 'ArrowRight' ? 1 : -1));
    }
  });

  let isScrollUpdateScheduled = false;
  track.addEventListener('scroll', () => {
    if (!isScrollUpdateScheduled) {
      isScrollUpdateScheduled = true;
      window.requestAnimationFrame(() => {
        isScrollUpdateScheduled = false;
        updateCurrentPosition();
      });
    }
  }, { passive: true });

  // Autoplay
  const stopAutoplay = () => {
    window.clearInterval(autoplayTimer);
    autoplayTimer = null;
  };

  const startAutoplay = () => {
    stopAutoplay();

    if (autoplaySeconds > 0 && !prefersReducedMotion && !isPausedByUser && !isPausedByInteraction && !document.hidden && snapPositions.length > 1) {
      autoplayTimer = window.setInterval(() => goToPosition(currentPosition + 1), autoplaySeconds * 1000);
    }
  };

  const restartAutoplay = () => {
    if (autoplayTimer) {
      startAutoplay();
    }
  };

  if (autoplaySeconds > 0 && !prefersReducedMotion) {
    const pauseForInteraction = (shouldPause) => {
      isPausedByInteraction = shouldPause;
      startAutoplay();
    };

    carouselElement.addEventListener('pointerenter', () => pauseForInteraction(true));
    carouselElement.addEventListener('pointerleave', () => pauseForInteraction(carouselElement.contains(document.activeElement)));
    carouselElement.addEventListener('focusin', () => pauseForInteraction(true));
    carouselElement.addEventListener('focusout', (focusEvent) => {
      if (!carouselElement.contains(focusEvent.relatedTarget)) {
        pauseForInteraction(false);
      }
    });
    document.addEventListener('visibilitychange', startAutoplay);

    pauseButton?.addEventListener('click', () => {
      isPausedByUser = !isPausedByUser;
      pauseButton.setAttribute('aria-pressed', String(isPausedByUser));
      startAutoplay();
    });
  } else if (pauseButton) {
    pauseButton.hidden = true;
  }

  new ResizeObserver(measurePositions).observe(track);
  measurePositions();
  startAutoplay();
}

document.querySelectorAll('[data-carousel]').forEach(initCarousel);
