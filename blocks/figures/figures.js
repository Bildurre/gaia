// Figures block: numbers count up from zero the first time they are seen.
const COUNT_DURATION = 1400;

function formatNumber(numberValue, decimalPlaces) {
  return numberValue.toLocaleString(document.documentElement.lang || undefined, {
    minimumFractionDigits: decimalPlaces,
    maximumFractionDigits: decimalPlaces,
  });
}

function animateCount(numberElement) {
  const targetValue = parseFloat(numberElement.dataset.countTo);
  const decimalPlaces = parseInt(numberElement.dataset.decimals, 10) || 0;
  const startTime = performance.now();

  const updateNumber = (currentTime) => {
    const progress = Math.min((currentTime - startTime) / COUNT_DURATION, 1);
    const easedProgress = 1 - (1 - progress) ** 3;
    numberElement.textContent = formatNumber(targetValue * easedProgress, decimalPlaces);

    if (progress < 1) {
      window.requestAnimationFrame(updateNumber);
    }
  };

  window.requestAnimationFrame(updateNumber);
}

function initFigures() {
  const animatedNumbers = document.querySelectorAll('[data-count-to]');

  if (!animatedNumbers.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  const numberObserver = new IntersectionObserver((observedEntries) => {
    observedEntries.forEach((observedEntry) => {
      if (observedEntry.isIntersecting) {
        numberObserver.unobserve(observedEntry.target);
        animateCount(observedEntry.target);
      }
    });
  }, { threshold: 0.6 });

  animatedNumbers.forEach((numberElement) => {
    // Start from zero only when the number is not on screen yet
    const elementBox = numberElement.getBoundingClientRect();

    if (elementBox.top > window.innerHeight) {
      numberElement.textContent = formatNumber(0, parseInt(numberElement.dataset.decimals, 10) || 0);
      numberObserver.observe(numberElement);
    }
  });
}

initFigures();
