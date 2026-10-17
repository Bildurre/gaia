// Gallery block: opens the images in a dialog, with previous / next buttons,
// arrow keys and swipe. Escape and the backdrop close it.
const SWIPE_DISTANCE = 50;

function initGallery(galleryElement) {
  const galleryLinks = [...galleryElement.querySelectorAll('[data-gallery-item]')];
  const galleryDialog = galleryElement.querySelector('[data-gallery-dialog]');

  if (!galleryLinks.length || !galleryDialog) {
    return;
  }

  const fullImage = galleryDialog.querySelector('[data-gallery-image]');
  const captionElement = galleryDialog.querySelector('[data-gallery-caption]');
  let currentIndex = 0;
  let touchStartX = null;

  const showImage = (imageIndex) => {
    currentIndex = (imageIndex + galleryLinks.length) % galleryLinks.length;
    const galleryLink = galleryLinks[currentIndex];
    const thumbnailImage = galleryLink.querySelector('img');

    fullImage.src = galleryLink.href;
    fullImage.alt = thumbnailImage?.alt ?? '';
    captionElement.textContent = galleryLink.dataset.caption ?? '';
    captionElement.hidden = !galleryLink.dataset.caption;
  };

  galleryLinks.forEach((galleryLink, linkIndex) => {
    galleryLink.addEventListener('click', (clickEvent) => {
      clickEvent.preventDefault();
      showImage(linkIndex);
      galleryDialog.showModal();
    });
  });

  galleryDialog.querySelector('[data-gallery-previous]').addEventListener('click', () => showImage(currentIndex - 1));
  galleryDialog.querySelector('[data-gallery-next]').addEventListener('click', () => showImage(currentIndex + 1));
  galleryDialog.querySelector('[data-gallery-close]').addEventListener('click', () => galleryDialog.close());

  // A click on the backdrop (the dialog itself, outside the figure) closes it
  galleryDialog.addEventListener('click', (clickEvent) => {
    if (clickEvent.target === galleryDialog) {
      galleryDialog.close();
    }
  });

  galleryDialog.addEventListener('keydown', (keyboardEvent) => {
    if (keyboardEvent.key === 'ArrowLeft') {
      showImage(currentIndex - 1);
    } else if (keyboardEvent.key === 'ArrowRight') {
      showImage(currentIndex + 1);
    }
  });

  galleryDialog.addEventListener('touchstart', (touchEvent) => {
    touchStartX = touchEvent.changedTouches[0].clientX;
  }, { passive: true });

  galleryDialog.addEventListener('touchend', (touchEvent) => {
    if (touchStartX === null) {
      return;
    }

    const swipeDistance = touchEvent.changedTouches[0].clientX - touchStartX;
    touchStartX = null;

    if (Math.abs(swipeDistance) > SWIPE_DISTANCE) {
      showImage(currentIndex + (swipeDistance < 0 ? 1 : -1));
    }
  });

  // Focus returns to the image that was opened
  galleryDialog.addEventListener('close', () => {
    galleryLinks[currentIndex].focus();
  });
}

document.querySelectorAll('[data-gallery]').forEach(initGallery);
