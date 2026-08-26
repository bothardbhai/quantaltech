/**
 * Podcast pages — shared video modal.
 *
 * Any [data-video-id] play button (Featured Episode card, episode grid
 * cards) opens #pd-video-modal (partials/youtube-modal.php) and sets the
 * iframe src only on open — closing clears the src again so playback
 * actually stops and only one YouTube iframe is ever live at a time.
 *
 * The single episode page's own video embed is a plain iframe rendered
 * directly in the page (pages/podcast/single.php) — no facade/click-to-load
 * layer, so it needs no JS here.
 */
(function () {
    var modal = document.getElementById('pd-video-modal');
    var modalIframe = document.getElementById('pd-video-modal-iframe');

    function openModal(videoId) {
        if (!modal || !modalIframe || !videoId) return;
        modalIframe.src = 'https://www.youtube-nocookie.com/embed/' + videoId + '?autoplay=1&rel=0';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('pd-modal-open');
    }

    function closeModal() {
        if (!modal || !modalIframe) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('pd-modal-open');
        modalIframe.src = '';
    }

    document.addEventListener('click', function (e) {
        var playBtn = e.target.closest('[data-video-id]');
        if (playBtn) {
            e.preventDefault();
            openModal(playBtn.getAttribute('data-video-id'));
            return;
        }
        if (e.target.closest('[data-pd-modal-close]')) {
            e.preventDefault();
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
