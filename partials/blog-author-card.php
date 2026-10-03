<?php

/**
 * Blog post "Author" box — per-post fields stored directly on posts (see
 * db/migrations/2026-10-03-001-add-author-fields-to-posts.sql), not a
 * team_members profile. Reuses the same .engineer-card / .engineer-card__*
 * / .skill-tag classes as partials/team-member-card.php so it gets identical
 * styling for free, swapping the "View Profile" button for an explicit
 * LinkedIn one. Renders nothing if $has_author is empty — the caller
 * (pages/blog/single.php) already guards the include with that check, so
 * this file assumes $post and $author_expertise are set.
 *
 * Expects, before including this file:
 *   $post              array   a posts row (author_image, author_name,
 *                               author_designation, author_description,
 *                               author_linkedin_url)
 *   $author_expertise  string[] decoded author_expertise_json
 */

if (empty($post['author_name'])) {
    return;
}
?>
<div class="post-author" style="margin-top:50px;padding-top:30px;border-top:1px solid #eee;">
    <div class="row">
        <div class="col-lg-12">
            <div class="engineer-card">
                <div class="engineer-card__media">
                    <div class="engineer-card__photo">
                        <img src="<?= e($post['author_image'] ? media_url($post['author_image']) : asset('images/quantal/team/default-avatar.svg')) ?>"
                            alt="<?= attr($post['author_name']) ?>" loading="lazy">
                    </div>
                    <?php if (!empty($post['author_linkedin_url'])): ?>
                        <a class="theme-btn btn-style-border engineer-card__linkedin-btn"
                            href="<?= attr($post['author_linkedin_url']) ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-linkedin"></i>
                            <span class="btn-title">LinkedIn</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="engineer-card__info">
                    <div class="engineer-card__header">
                        <h4><?= e($post['author_name']) ?></h4>
                    </div>
                    <?php if (!empty($post['author_designation'])): ?>
                        <p class="engineer-card__role"><?= e($post['author_designation']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($post['author_description'])): ?>
                        <div class="engineer-card__experience"><?= e($post['author_description']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($author_expertise)): ?>
                        <div class="engineer-card__skills mt-3">
                            <?php foreach ($author_expertise as $tag): ?>
                                <span class="skill-tag"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>