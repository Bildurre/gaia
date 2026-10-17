// Video block: the external player (YouTube without cookies, Vimeo with
// "do not track") is only inserted when the cover is pressed.
function initVideoCovers() {
  document.querySelectorAll('[data-video-embed]').forEach((coverButton) => {
    coverButton.addEventListener('click', () => {
      const playerFrame = document.createElement('iframe');
      playerFrame.className = 'video__player';
      playerFrame.src = coverButton.dataset.videoEmbed;
      playerFrame.title = coverButton.dataset.videoTitle;
      playerFrame.allow = 'autoplay; encrypted-media; fullscreen; picture-in-picture';
      playerFrame.allowFullscreen = true;
      coverButton.replaceWith(playerFrame);
      playerFrame.focus();
    });
  });
}

initVideoCovers();
