/**
 * Robust sticky behavior for the sidebar block made of ".service-details-help"
 * plus the CTA button widget right after it. Vanilla ES6, no dependencies.
 *
 * ".sidebar-service-list" (the links list above) is left completely alone —
 * only ".service-details-help" and its next sibling are grouped and stuck.
 *
 * Behavior:
 *  - Normal document flow until the group's natural top scrolls to within
 *    STICKY_OFFSET of the viewport top.
 *  - position: fixed while there is still room below it in its column.
 *  - position: absolute (pinned to the bottom of that column) once fixed
 *    positioning would overlap the widget below it / the footer.
 *  - Disabled entirely below the 992px breakpoint.
 *
 * CSS-only `position: sticky` can't express the "stop before the bottom of
 * the column" boundary, hence the manual fixed/absolute switch below.
 */
(function () {
  'use strict';

  var STICKY_OFFSET = 70; // px kept below the fixed header once pinned
  var mq = window.matchMedia('(min-width: 992px)');

  var helpEl = document.querySelector('.service-details-help');
  if (!helpEl) return;

  // In the existing markup, the CTA button widget
  // (.sidebar-widget.service-sidebar-single.mt-4) is the very next element
  // after .service-details-help — grab it by position, not by class, since
  // that class name is also used by an ancestor further up the tree.
  var ctaEl = helpEl.nextElementSibling;

  // --- Group both elements under one sticky unit, without editing markup --
  var parent = helpEl.parentNode;

  var group = document.createElement('div');
  group.className = 'sdh-sticky-group';
  parent.insertBefore(group, helpEl);
  group.appendChild(helpEl);
  if (ctaEl) group.appendChild(ctaEl);

  var wrapper = document.createElement('div');
  wrapper.className = 'sdh-sticky-wrapper';
  parent.insertBefore(wrapper, group);
  wrapper.appendChild(group);

  // Boundary: the Bootstrap column holding the sidebar. It stretches to the
  // full row height (flex `align-items: stretch` against the long main
  // content column), which is what gives the group room to travel down.
  var container = group.closest('[class*="col-"]') || wrapper.parentElement;

  var state = 'static'; // 'static' | 'fixed' | 'absolute'
  var ticking = false;

  function reset() {
    group.classList.remove('is-sdh-fixed', 'is-sdh-absolute');
    group.style.top = '';
    group.style.left = '';
    group.style.width = '';
    wrapper.style.height = '';
    state = 'static';
  }

  function update() {
    ticking = false;

    if (!mq.matches) {
      if (state !== 'static') reset();
      return;
    }

    var scrollY = window.pageYOffset;
    var naturalDocTop = wrapper.getBoundingClientRect().top + scrollY;
    var groupHeight = group.offsetHeight;

    var containerRect = container.getBoundingClientRect();
    var containerBottom = containerRect.top + scrollY + container.offsetHeight;
    var maxTop = containerBottom - groupHeight; // last allowed doc-top
    var travel = maxTop - naturalDocTop;

    // Where the group's top would land if pinned to the viewport right now.
    var wouldBeDocTop = scrollY + STICKY_OFFSET;

    if (travel <= 0 || wouldBeDocTop <= naturalDocTop) {
      // No room to travel, or not scrolled far enough yet.
      if (state !== 'static') reset();
      return;
    }

    if (wouldBeDocTop < maxTop) {
      // Pin to the viewport.
      if (state !== 'fixed') {
        group.classList.remove('is-sdh-absolute');
        group.classList.add('is-sdh-fixed');
        state = 'fixed';
      }
      var wrapRect = wrapper.getBoundingClientRect();
      group.style.top = STICKY_OFFSET + 'px';
      group.style.left = wrapRect.left + 'px';
      group.style.width = wrapRect.width + 'px';
      wrapper.style.height = groupHeight + 'px';
    } else {
      // Reached the bottom of the column: park it so it stops exactly there.
      if (state !== 'absolute') {
        group.classList.remove('is-sdh-fixed');
        group.classList.add('is-sdh-absolute');
        state = 'absolute';
      }
      group.style.left = '';
      group.style.width = '';
      group.style.top = travel + 'px';
      wrapper.style.height = groupHeight + 'px';
    }
  }

  function onScrollOrResize() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }

  window.addEventListener('scroll', onScrollOrResize, { passive: true });
  window.addEventListener('resize', onScrollOrResize);
  window.addEventListener('load', onScrollOrResize);

  // Recalculate immediately when the 992px breakpoint is crossed.
  if (typeof mq.addEventListener === 'function') {
    mq.addEventListener('change', onScrollOrResize);
  } else if (typeof mq.addListener === 'function') {
    mq.addListener(onScrollOrResize); // Safari < 14 fallback
  }

  // Recalculate if the page's height changes dynamically (accordions,
  // images loading, async content, etc.).
  if (typeof ResizeObserver !== 'undefined') {
    new ResizeObserver(onScrollOrResize).observe(document.body);
  }

  update();
})();
