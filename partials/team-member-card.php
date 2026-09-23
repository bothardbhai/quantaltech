<?php

/**
 * "Meet the Expert" card — a single team_members profile (see
 * db/migrations/2026-09-23-001-create-team-members-table.sql), reusing the
 * exact same card classes as Hire Master's "Meet Our Engineers" grid
 * (pages/hire/_subhire.php) — .engineer-card / .engineer-card__* / .skill-tag
 * — so it gets identical styling for free instead of needing new CSS.
 * First consumer: pages/success-stories/single.php's "Meet the Expert"
 * section. Renders nothing if $tm_member is empty/null — callers should
 * wrap the whole section (heading included) in that same check.
 *
 * Expects, before including this file:
 *   $tm_member  array  a team_members row, plus 'expertise' decoded to
 *                      string[] (see single.php's mapping)
 */

if (empty($tm_member)) {
    return;
}
?>
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="engineer-card">
            <div class="engineer-card__media">
                <div class="engineer-card__photo">
                    <img src="<?= e($tm_member['image'] ? media_url($tm_member['image']) : asset('images/quantal/team/default-avatar.svg')) ?>"
                        alt="<?= attr($tm_member['name']) ?>" loading="lazy">
                </div>
                <?php if (!empty($tm_member['profile_url'])): ?>
                    <a class="theme-btn btn-style-border engineer-card__linkedin-btn"
                        href="<?= attr($tm_member['profile_url']) ?>" target="_blank" rel="noopener">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        <span class="btn-title">View Profile</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="engineer-card__info">
                <div class="engineer-card__header">
                    <h4><?= e($tm_member['name']) ?></h4>
                </div>
                <?php if (!empty($tm_member['designation'])): ?>
                    <p class="engineer-card__role"><?= e($tm_member['designation']) ?></p>
                <?php endif; ?>
                <?php if (!empty($tm_member['experience_text'])): ?>
                    <p><?= e($tm_member['experience_text']) ?></p>
                <?php endif; ?>
                <?php if (!empty($tm_member['expertise'])): ?>
                    <div class="engineer-card__skills">
                        <?php foreach ($tm_member['expertise'] as $tag): ?>
                            <span class="skill-tag"><?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
