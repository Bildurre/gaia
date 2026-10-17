// Archives with "load more" or infinite scroll: the next page is fetched and
// its cards are added to the grid. Without JavaScript the button is a normal
// link to the next page.
const INFINITE_SCROLL_MARGIN = '600px';

function initArchiveLoading() {
  const moreContainer = document.querySelector('[data-archive-more]');
  const itemsContainer = document.querySelector('[data-archive-items]');

  if (!moreContainer || !itemsContainer) {
    return;
  }

  // "load_more", "infinite", or "link" after an error
  const isInfinite = moreContainer.dataset.archiveMore === 'infinite';
  let nextLink = moreContainer.querySelector('[data-archive-next]');
  let isLoading = false;
  let scrollObserver = null;

  const loadNextPage = async () => {
    if (isLoading || !nextLink) {
      return;
    }

    isLoading = true;
    moreContainer.classList.add('is-loading');
    nextLink.setAttribute('aria-busy', 'true');

    try {
      const pageResponse = await fetch(nextLink.href, { credentials: 'same-origin' });

      if (!pageResponse.ok) {
        throw new Error(`HTTP ${pageResponse.status}`);
      }

      const nextDocument = new DOMParser().parseFromString(await pageResponse.text(), 'text/html');
      const newItems = [...(nextDocument.querySelector('[data-archive-items]')?.children ?? [])];
      const newNextLink = nextDocument.querySelector('[data-archive-next]');

      itemsContainer.append(...newItems);
      newItems[0]?.querySelector('a')?.focus({ preventScroll: true });

      if (newNextLink) {
        nextLink.href = newNextLink.href;
        nextLink.removeAttribute('aria-busy');
      } else {
        scrollObserver?.disconnect();
        moreContainer.remove();
        nextLink = null;
      }
    } catch {
      // Keep the link: a click opens the next page normally
      scrollObserver?.disconnect();
      moreContainer.dataset.archiveMore = 'link';
    } finally {
      isLoading = false;
      moreContainer.classList.remove('is-loading');
    }
  };

  nextLink.addEventListener('click', (clickEvent) => {
    if (moreContainer.dataset.archiveMore !== 'link') {
      clickEvent.preventDefault();
      loadNextPage();
    }
  });

  if (isInfinite) {
    scrollObserver = new IntersectionObserver((observedEntries) => {
      if (observedEntries.some((observedEntry) => observedEntry.isIntersecting)) {
        loadNextPage();
      }
    }, { rootMargin: INFINITE_SCROLL_MARGIN });
    scrollObserver.observe(moreContainer);
  }
}

initArchiveLoading();
