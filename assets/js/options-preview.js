const PREVIEW_OPEN_STORAGE_KEY = 'gaia-options-preview-open';
const PREVIEW_DEVICE_STORAGE_KEY = 'gaia-options-preview-device';
const PREVIEW_UPDATE_DELAY = 400;
const PREVIEW_CHANGE_CHECK_INTERVAL = 800;
const PREVIEW_DEVICE_WIDTHS = { desktop: 1280, mobile: 390 };
// From this width the panel sits next to the fields (see admin.scss)
const SIDE_BY_SIDE_QUERY = '(min-width: 1100px)';

function readStoredPreference(storageKey) {
  try {
    return localStorage.getItem(storageKey);
  } catch {
    return null;
  }
}

function storePreference(storageKey, value) {
  try {
    localStorage.setItem(storageKey, value);
  } catch {
    // Storage unavailable: the preference lasts until the page is reloaded
  }
}

function initOptionsPreview() {
  const previewPanel = document.querySelector('[data-options-preview]');
  const optionsForm = document.querySelector('form#post');
  const openButtonTemplate = document.querySelector('[data-preview-open-template]');

  if (!previewPanel || !optionsForm || !openButtonTemplate) {
    return;
  }

  const previewFrame = previewPanel.querySelector('[data-preview-frame]');
  const previewViewport = previewPanel.querySelector('[data-preview-viewport]');
  const pageSelect = previewPanel.querySelector('[data-preview-page]');
  const statusElement = previewPanel.querySelector('[data-preview-status]');
  const closeButton = previewPanel.querySelector('[data-preview-close]');
  const deviceButtons = [...previewPanel.querySelectorAll('[data-preview-device]')];

  // "Vista previa" button next to the page title
  const pageTitle = document.querySelector('.wrap h1');
  pageTitle?.after(openButtonTemplate.content.cloneNode(true));
  const openButton = document.querySelector('[data-preview-open]');

  // Hidden form that posts the unsaved values to the preview frame
  const previewForm = document.createElement('form');
  previewForm.method = 'post';
  previewForm.target = previewPanel.dataset.previewFrameName;
  previewForm.hidden = true;
  document.body.append(previewForm);

  let currentPreviewUrl = pageSelect.value;
  let currentDevice = readStoredPreference(PREVIEW_DEVICE_STORAGE_KEY) === 'mobile' ? 'mobile' : 'desktop';
  let lastSubmittedValues = null;
  let scrollPositionToRestore = 0;
  let updateTimeout = null;

  const isPreviewOpen = () => !previewPanel.hidden;

  // Form values that belong to fields (acf[...]) as a comparable string
  const getFieldValues = () => {
    window.tinymce?.triggerSave();
    const fieldValues = new URLSearchParams();

    for (const [fieldName, fieldValue] of new FormData(optionsForm)) {
      if (fieldName.startsWith('acf[') && typeof fieldValue === 'string') {
        fieldValues.append(fieldName, fieldValue);
      }
    }

    return fieldValues;
  };

  const appendHiddenInput = (inputName, inputValue) => {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = inputName;
    hiddenInput.value = inputValue;
    previewForm.append(hiddenInput);
  };

  const updatePreview = ({ keepScrollPosition = true } = {}) => {
    window.clearTimeout(updateTimeout);
    const fieldValues = getFieldValues();

    try {
      scrollPositionToRestore = keepScrollPosition ? previewFrame.contentWindow.scrollY : 0;
    } catch {
      scrollPositionToRestore = 0;
    }

    previewForm.replaceChildren();
    previewForm.action = currentPreviewUrl;
    appendHiddenInput('gaia_options_preview', '1');
    appendHiddenInput('gaia_options_preview_nonce', previewPanel.dataset.previewNonce);
    fieldValues.forEach((fieldValue, fieldName) => appendHiddenInput(fieldName, fieldValue));

    lastSubmittedValues = fieldValues.toString();
    statusElement.textContent = previewPanel.dataset.previewLoadingText;
    previewPanel.classList.add('is-loading');
    previewForm.submit();
  };

  const scheduleUpdate = () => {
    if (!isPreviewOpen()) {
      return;
    }

    window.clearTimeout(updateTimeout);
    updateTimeout = window.setTimeout(() => {
      if (getFieldValues().toString() !== lastSubmittedValues) {
        updatePreview();
      }
    }, PREVIEW_UPDATE_DELAY);
  };

  const navigatePreview = (newPreviewUrl) => {
    currentPreviewUrl = newPreviewUrl;
    pageSelect.value = [...pageSelect.options].some((option) => option.value === newPreviewUrl) ? newPreviewUrl : '';
    updatePreview({ keepScrollPosition: false });
  };

  // Scale the frame so the chosen device width fits the panel
  const updateFrameScale = () => {
    const frameWidth = PREVIEW_DEVICE_WIDTHS[currentDevice];
    const viewportWidth = previewViewport.clientWidth;
    const viewportHeight = previewViewport.clientHeight;
    const frameScale = Math.min(1, viewportWidth / frameWidth);

    previewFrame.style.width = `${frameWidth}px`;
    previewFrame.style.height = `${viewportHeight / frameScale}px`;
    previewFrame.style.transform = `scale(${frameScale})`;
    previewFrame.style.left = `${Math.max(0, (viewportWidth - frameWidth * frameScale) / 2)}px`;
  };

  const setDevice = (newDevice) => {
    currentDevice = newDevice;
    storePreference(PREVIEW_DEVICE_STORAGE_KEY, newDevice);
    deviceButtons.forEach((deviceButton) => {
      deviceButton.setAttribute('aria-pressed', String(deviceButton.dataset.previewDevice === newDevice));
    });
    updateFrameScale();
  };

  const setPreviewOpen = (shouldOpen) => {
    previewPanel.hidden = !shouldOpen;
    document.body.classList.toggle('has-gaia-options-preview', shouldOpen);
    openButton?.setAttribute('aria-expanded', String(shouldOpen));

    if (shouldOpen) {
      updateFrameScale();

      if (lastSubmittedValues === null || getFieldValues().toString() !== lastSubmittedValues) {
        updatePreview();
      }
    }
  };

  // Links and search forms inside the preview keep the unsaved values
  const handleFrameClick = (clickEvent) => {
    const clickedLink = clickEvent.target.closest('a[href]');

    if (!clickedLink || clickEvent.defaultPrevented) {
      return;
    }

    const linkUrl = new URL(clickedLink.href, previewFrame.contentWindow.location.href);
    const frameUrl = new URL(previewFrame.contentWindow.location.href);
    const isSamePageAnchor = linkUrl.hash && linkUrl.pathname === frameUrl.pathname && linkUrl.search === frameUrl.search;

    if (isSamePageAnchor || linkUrl.href.startsWith('javascript:')) {
      return;
    }

    clickEvent.preventDefault();

    if (linkUrl.origin === window.location.origin && !/\/wp-(admin|login)/.test(linkUrl.pathname)) {
      navigatePreview(linkUrl.href);
    }
  };

  const handleFrameSubmit = (submitEvent) => {
    const submittedForm = submitEvent.target;
    submitEvent.preventDefault();

    if (submittedForm.method.toLowerCase() !== 'get') {
      return;
    }

    const searchUrl = new URL(submittedForm.action, previewFrame.contentWindow.location.href);
    new FormData(submittedForm).forEach((fieldValue, fieldName) => {
      searchUrl.searchParams.set(fieldName, fieldValue);
    });
    navigatePreview(searchUrl.href);
  };

  previewFrame.addEventListener('load', () => {
    statusElement.textContent = '';
    previewPanel.classList.remove('is-loading');

    try {
      const frameDocument = previewFrame.contentDocument;
      frameDocument.addEventListener('click', handleFrameClick);
      frameDocument.addEventListener('submit', handleFrameSubmit);
      previewFrame.contentWindow.scrollTo(0, scrollPositionToRestore);
    } catch {
      // Not the site (e.g. an error page from another origin): nothing to do
    }
  });

  optionsForm.addEventListener('input', scheduleUpdate);
  optionsForm.addEventListener('change', scheduleUpdate);

  // Some fields (color pickers, images) change their inputs without native
  // events: compare the values regularly while the panel is open
  window.setInterval(() => {
    if (isPreviewOpen() && !previewPanel.classList.contains('is-loading') && getFieldValues().toString() !== lastSubmittedValues) {
      scheduleUpdate();
    }
  }, PREVIEW_CHANGE_CHECK_INTERVAL);

  pageSelect.addEventListener('change', () => {
    if (pageSelect.value) {
      navigatePreview(pageSelect.value);
    }
  });

  deviceButtons.forEach((deviceButton) => {
    deviceButton.addEventListener('click', () => setDevice(deviceButton.dataset.previewDevice));
  });

  // Only the visitor's own choice is remembered
  const togglePreviewByUser = (shouldOpen) => {
    setPreviewOpen(shouldOpen);
    storePreference(PREVIEW_OPEN_STORAGE_KEY, shouldOpen ? '1' : '0');
  };

  openButton?.addEventListener('click', () => togglePreviewByUser(!isPreviewOpen()));
  closeButton.addEventListener('click', () => {
    togglePreviewByUser(false);
    openButton?.focus();
  });

  new ResizeObserver(updateFrameScale).observe(previewViewport);

  setDevice(currentDevice);
  // Open on load unless closed before; narrow screens only open on demand
  // (the panel would cover the fields)
  setPreviewOpen(readStoredPreference(PREVIEW_OPEN_STORAGE_KEY) !== '0' && window.matchMedia(SIDE_BY_SIDE_QUERY).matches);
}

initOptionsPreview();
