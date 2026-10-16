const AVATAR_MAX_BYTES = 2 * 1024 * 1024;
const AVATAR_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

function initAvatarField() {
  const avatarField = document.querySelector('[data-avatar-field]');

  if (!avatarField) {
    return;
  }

  const fileInput = avatarField.querySelector('[data-avatar-input]');
  const dropzone = avatarField.querySelector('[data-avatar-dropzone]');
  const previewImage = avatarField.querySelector('[data-avatar-preview]');
  const statusMessage = avatarField.querySelector('[data-avatar-status]');
  const originalPreviewSource = previewImage?.src ?? '';
  let previewObjectUrl = '';

  const showStatus = (message, isError = false) => {
    statusMessage.textContent = message;
    statusMessage.classList.toggle('is-error', isError);
  };

  const resetSelection = () => {
    fileInput.value = '';

    if (previewImage) {
      previewImage.src = originalPreviewSource;
    }
  };

  const validateFile = (selectedFile) => {
    if (!AVATAR_MIME_TYPES.includes(selectedFile.type)) {
      return avatarField.dataset.errorType;
    }

    if (selectedFile.size > AVATAR_MAX_BYTES) {
      return avatarField.dataset.errorSize;
    }

    return '';
  };

  const handleSelectedFile = () => {
    const selectedFile = fileInput.files?.[0];

    if (!selectedFile) {
      showStatus('');
      return;
    }

    const validationError = validateFile(selectedFile);

    if (validationError) {
      resetSelection();
      showStatus(validationError, true);
      return;
    }

    if (previewObjectUrl) {
      URL.revokeObjectURL(previewObjectUrl);
    }

    previewObjectUrl = URL.createObjectURL(selectedFile);

    if (previewImage) {
      previewImage.src = previewObjectUrl;
      previewImage.removeAttribute('srcset');
    }

    showStatus(selectedFile.name);
  };

  fileInput.addEventListener('change', handleSelectedFile);

  // Drag and drop on the dropzone
  ['dragenter', 'dragover'].forEach((eventName) => {
    dropzone.addEventListener(eventName, (dragEvent) => {
      dragEvent.preventDefault();
      dropzone.classList.add('is-dragging');
    });
  });

  ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
    dropzone.addEventListener(eventName, () => {
      dropzone.classList.remove('is-dragging');
    });
  });

  dropzone.addEventListener('drop', (dropEvent) => {
    dropEvent.preventDefault();
    const droppedFile = dropEvent.dataTransfer?.files?.[0];

    if (!droppedFile) {
      return;
    }

    const fileList = new DataTransfer();
    fileList.items.add(droppedFile);
    fileInput.files = fileList.files;
    handleSelectedFile();
  });
}

initAvatarField();
