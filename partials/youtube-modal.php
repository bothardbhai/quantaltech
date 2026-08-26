<?php

/**
 * Shared YouTube video modal — one hidden overlay reused by every
 * "[data-video-id]" play button on the Podcast pages (Featured Episode
 * card, All Episodes / Watch More Episodes cards). assets/js/podcast.js
 * sets the iframe src on open (lazy-loading exactly one video at a time)
 * and clears it on close so playback actually stops rather than just
 * being hidden — see the plan's "avoid multiple simultaneous YouTube
 * iframes" performance requirement.
 */
?>
<div id="pd-video-modal" class="pd-video-modal" aria-hidden="true">
    <div class="pd-video-modal__backdrop" data-pd-modal-close></div>
    <div class="pd-video-modal__dialog" role="dialog" aria-modal="true" aria-label="Podcast episode video">
        <button type="button" class="pd-video-modal__close" data-pd-modal-close aria-label="Close video">
            <i class="fas fa-times"></i>
        </button>
        <div class="pd-video-modal__frame">
            <iframe id="pd-video-modal-iframe" src="" title="Podcast episode video" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
        </div>
    </div>
</div>
