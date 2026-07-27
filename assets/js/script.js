var THEMEMASCOT = {};
(function ($) {

	"use strict";

	/* ---------------------------------------------------------------------- */
	/* --------------------------- Start Demo Switcher  --------------------- */
	/* ---------------------------------------------------------------------- */
	//   var showSwitcher = false;
	//   var $body = $('body');
	//   var $style_switcher = $('#style-switcher');
	//   if( !$style_switcher.length && showSwitcher ) {
	//       $.ajax({
	//           url: "color-switcher/style-switcher.html",
	//           success: function (data) { $body.append(data); },
	//           dataType: 'html'
	//       });
	//   }
	/* ---------------------------------------------------------------------- */
	/* ----------------------------- En Demo Switcher  ---------------------- */
	/* ---------------------------------------------------------------------- */


	//   THEMEMASCOT.isRTL = {
	//     check: function() {
	//       if( $( "html" ).attr("dir") === "rtl" ) {
	//         return true;
	//       } else {
	//         return false;
	//       }
	//     }
	//   };

	//   THEMEMASCOT.isLTR = {
	//     check: function() {
	//       if( $( "html" ).attr("dir") !== "rtl" ) {
	//         return true;
	//       } else {
	//         return false;
	//       }
	//     }
	//   };


	//Hide Loading Box (Preloader) - DISABLED
	// function loader() {
	// 	$(window).on('load', function () {
	// 		// Animate loader off screen
	// 		$(".preloader").addClass('loaded');
	// 		$(".preloader").delay(600).fadeOut();
	// 	});
	// }
	// loader();

	// Call headerStyle on scroll
	$(window).on('scroll', function () {
		headerStyle();
	});

	// Also call on page load to handle reload
	$(document).ready(function () {
		headerStyle();
	});


	//Update Header Style and Scroll to Top
	function headerStyle() {
		if ($('.main-header').length) {
			var windowpos = $(window).scrollTop();
			var siteHeader = $('.header-style-one');
			var scrollLink = $('.scroll-to-top');
			var sticky_header = $('.main-header .sticky-header');
			var header_top = $('.main-header .header-top');
			if (windowpos > 100) {
				sticky_header.addClass("fixed-header animated slideInDown");
				header_top.addClass("fixed-header animated slideInDown");
				scrollLink.fadeIn(300);
			} else {
				sticky_header.removeClass("fixed-header animated slideInDown");
				header_top.removeClass("fixed-header animated slideInDown");
				scrollLink.fadeOut(300);
			}
			if (windowpos > 1) {
				siteHeader.addClass("fixed-header");
			} else {
				siteHeader.removeClass("fixed-header");
			}
		}
	}
	headerStyle();

	// Keep --main-header-height / --header-top-height (used by CSS to space
	// page content below the absolutely-positioned header, and to dock the
	// sticky top bar directly above the sticky nav bar) in sync with the
	// header's real rendered size, so both keep working if its height ever
	// changes (top bar hidden on mobile, content wraps, etc).
	function updateHeaderMetrics() {
		var $headerTop = $('.main-header > .header-top');
		var $headerLower = $('.main-header > .header-lower');
		if (!$headerLower.length) {
			return;
		}
		// topH stays exact (unbuffered): it docks .sticky-header flush under
		// .header-top, and any padding there would show up as a visible gap
		// between the two. The content-overlap total gets a couple px of
		// headroom instead, since a sub-pixel shortfall there (e.g. a web
		// font swapping in after this first measurement) just means a hair
		// of extra breathing room, not a visible seam.
		var topH = $headerTop.length ? Math.ceil($headerTop.outerHeight()) : 0;
		var lowerH = Math.ceil($headerLower.outerHeight() || 0);
		var root = document.documentElement;
		root.style.setProperty('--header-top-height', topH + 'px');
		root.style.setProperty('--main-header-height', (topH + lowerH + 2) + 'px');
	}
	updateHeaderMetrics();
	$(window).on('load', updateHeaderMetrics);

	(function () {
		var resizeTimer;
		$(window).on('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(updateHeaderMetrics, 150);
		});
	})();

	// Header hide on scroll down, show on scroll up (optional)

	if ($(window).width() > 991) {
		if ($(window).width() > 768) {
			$('.parallaxie').parallaxie({
				speed: 0.55,
				offset: 0,
			});
		}
	}

	// hover animation
	const $items = $(".case-block-four");

	// set second one active by default
	$items.eq(1).addClass("active");

	$items.hover(function () {
	$items.removeClass("active");
	$(this).addClass("active");
	});

	//Submenu Dropdown Toggle
	if ($('.main-header li.dropdown ul').length) {
		$('.main-header .navigation li.dropdown').append('<div class="dropdown-btn"><i class="fa fa-angle-down"></i></div>');
	}

	//Mobile Nav Hide Show
	if ($('.mobile-menu').length) {

		var mobileMenuContent = $('.main-header .main-menu .navigation').html();

		$('.mobile-menu .navigation').append(mobileMenuContent);
		$('.sticky-header .navigation').append(mobileMenuContent);
		$('.mobile-menu .close-btn').on('click', function () {
			$('body').removeClass('mobile-menu-visible');
		});

		//Dropdown Button
		$('.mobile-menu li.dropdown .dropdown-btn').on('click', function () {
			$(this).prev('ul').slideToggle(500);
			$(this).toggleClass('active');
		});

		//Menu Toggle Btn
		$('.mobile-nav-toggler').on('click', function () {
			$('body').addClass('mobile-menu-visible');
		});

		//Menu Toggle Btn
		$('.mobile-menu .menu-backdrop, .mobile-menu .close-btn').on('click', function () {
			$('body').removeClass('mobile-menu-visible');
		});

	}

	//Header Search
	if ($(".search-btn").length) {
		$(".search-btn").on("click", function () {
			$(".main-header").addClass("moblie-search-active");
		});
		$(".close-search, .search-back-drop").on("click", function () {
			$(".main-header").removeClass("moblie-search-active");
		});
	}

	//Fact Counter + Text Count
	if ($(".count-box").length) {
		$(".count-box").appear(
			function () {
				var $t = $(this),
					n = $t.find(".count-text").attr("data-stop"),
					r = parseInt($t.find(".count-text").attr("data-speed"), 10);

				if (!$t.hasClass("counted")) {
					$t.addClass("counted");
					$({
						countNum: $t.find(".count-text").text(),
					}).animate(
						{
							countNum: n,
						},
						{
							duration: r,
							easing: "linear",
							step: function () {
								$t.find(".count-text").text(Math.floor(this.countNum));
							},
							complete: function () {
								$t.find(".count-text").text(this.countNum);
							},
						}
					);
				}
			},
			{ accY: 0 }
		);
	}


	//Price Range Slider
	if ($(".price-range-slider").length) {
		$(".price-range-slider").slider({
			range: true,
			min: 10,
			max: 99,
			values: [10, 60],
			slide: function (event, ui) {
				$("input.property-amount").val(ui.values[0] + " - " + ui.values[1]);
			},
		});

		$("input.property-amount").val(
			$(".price-range-slider").slider("values", 0) +
			" - $" +
			$(".price-range-slider").slider("values", 1)
		);
	}

	 /* ================================
      Hover Active Js Start
    ================================ */

    $(".team-award-block-three ").hover(
		// Function to run when the mouse enters the element
		function () {
			// Remove the "active" class from all elements
			$(".team-award-block-three ").removeClass("active");
			// Add the "active" class to the currently hovered element
			$(this).addClass("active");
		}
	);


	//product bxslider
	if ($(".product-details .bxslider").length) {
		$(".product-details .bxslider").bxSlider({
			nextSelector: ".product-details #slider-next",
			prevSelector: ".product-details #slider-prev",
			nextText: '<i class="fa fa-angle-right"></i>',
			prevText: '<i class="fa fa-angle-left"></i>',
			mode: "fade",
			auto: "true",
			speed: "700",
			pagerCustom: ".product-details .slider-pager .thumb-box",
		});
	}
	//Tabs Box

	//Quantity box
	$(".quantity-box .add").on("click", function () {
		if ($(this).prev().val() < 999) {
			$(this)
				.prev()
				.val(+$(this).prev().val() + 1);
		}
	});
	$(".quantity-box .sub").on("click", function () {
		if ($(this).next().val() > 1) {
			if ($(this).next().val() > 1)
				$(this)
					.next()
					.val(+$(this).next().val() - 1);
		}
	});

	$(".feature-block-one").on("mouseenter", function () {
		$(".feature-block-one").removeClass("active");
		$(this).addClass("active");
	});

	// Horizontal accordion js area start here ***
	$(".hzAccordion__item").on("click", function () {
		$(this).addClass("active").siblings().removeClass("active");
	});
	// Horizontal accordion js area end here ***


	$(function () {
		const $featureList = $(".feature-list-items");
		const $img = $("#rotatable-image");

		if (!$featureList.length || !$img.length) return;

		let currentRotation = 0;
		let lastIndex = null;

		$featureList.on("mouseenter", "li .title", function () {
			const $titles = $featureList.find("li .title");
			const index = $titles.index(this);

			if (lastIndex === null) {
				currentRotation += 45;
				$img.css({
					transition: "transform 0.4s ease-in-out",
					transform: `rotate(${currentRotation}deg)`
				});
				lastIndex = index;
				return;
			}

			if (index > lastIndex) {
            currentRotation += 45; // moving down
          } else if (index < lastIndex) {
            currentRotation -= 45; // moving up
          }

          $img.css({
          	transition: "transform 0.4s ease-in-out",
          	transform: `rotate(${currentRotation}deg)`
          });

          lastIndex = index;
        });
	});



	 /* ================================
       Project Anim Js Start
    ================================ */

	// Project Image Slider
	if ($('.project-image-slider').length) {
		var swiper = new Swiper(".project-image-slider", {
			slidesPerView: 2,
			spaceBetween: 30,
			speed: 600,
			loop: true,
			breakpoints: {
				320: {
					slidesPerView: 1,
				},
				576: {
					slidesPerView: 1,
				},
				768: {
					slidesPerView: 1,
				},
				992: {
					slidesPerView: 2,
				},
				1023: {
					slidesPerView: 2,
				},
			},
		});
	}

	 /* ================================
           Image Move scale Js Start
        ================================ */
        const $section = $('.hero-section1');
        const $target = $('.tilt_scale');

        if ($section.length && $target.length) {
            let requestId;

            $section.on('mousemove', function(e) {
                if (requestId) {
                    cancelAnimationFrame(requestId);
                }

                requestId = requestAnimationFrame(() => {
                    const offset = $section.offset();
                    const width = $section.outerWidth();
                    const height = $section.outerHeight();

                    const x = e.pageX - offset.left;
                    const y = e.pageY - offset.top;

                    const rotateY = ((x / width) - 0.5) * 20;
                    const rotateX = ((y / height) - 0.5) * -20;

                    $target.css({
                        transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`
                    });
                });
            });

            $section.on('mouseleave', function() {
                if (requestId) {
                    cancelAnimationFrame(requestId);
                }
                $target.css({
                    transform: 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)'
                });
            });
        }

	// hover reveal start
	const hoverItem = document.querySelectorAll(".bw-hover-image");

	function moveImage(e, hoverItem, index) {
		const item = hoverItem.getBoundingClientRect();
		const x = e.clientX - item.x;
		const y = e.clientY - item.y;
		if (hoverItem.children[index]) {
			hoverItem.children[index].style.transform = `translate(${x}px, ${y}px)`;
		}
	}
	hoverItem.forEach((item, i) => {
		item.addEventListener("mousemove", (e) => {
			setInterval(moveImage(e, item, 1), 50);
		});
	});
	$("[data-background").each(function () {
		$(this).css("background-image", "url( " + $(this).attr("data-background") + "  )");
	});






	//Accordion Box
	if ($('.accordion-box').length) {
		$(".accordion-box").on('click', '.acc-btn', function () {

			var outerBox = $(this).parents('.accordion-box');
			var target = $(this).parents('.accordion');

			if ($(this).hasClass('active') !== true) {
				$(outerBox).find('.accordion .acc-btn').removeClass('active ');
			}

			if ($(this).next('.acc-content').is(':visible')) {
				return false;
			} else {
				$(this).addClass('active');
				$(outerBox).children('.accordion').removeClass('active-block');
				$(outerBox).find('.accordion').children('.acc-content').slideUp(300);
				target.addClass('active-block');
				$(this).next('.acc-content').slideDown(300);
			}
		});
	}

	//Brand-slider
	if ($('.brand-slider').length > 0) {
		const brandSlider = new Swiper(".brand-slider", {
			spaceBetween: 25,
			speed: 2000,
			loop: true,
			autoplay: {
				delay: 1000,
				disableOnInteraction: false,
			},
			pagination: {
				el: ".dot",
				clickable: true,
			},
			breakpoints: {
			
				1399: {
					slidesPerView: 5,
				},
				1199: {
					slidesPerView: 4,
				},
				991: {
					slidesPerView: 3,
				},
				767: {
					slidesPerView: 3,
				},
				575: {
					slidesPerView: 2,
				},
				0: {
					slidesPerView: 1.8,
				},
			},
		});
	}

	if ($('.brand-slider2').length > 0) {
		const brandSlider2 = new Swiper(".brand-slider2", {
			spaceBetween: 25,
			speed: 2000,
			loop: true,
			autoplay: {
				delay: 1000,
				disableOnInteraction: false,
			},
			pagination: {
				el: ".dot",
				clickable: true,
			},
			breakpoints: {
			
				1399: {
					slidesPerView: 5,
				},
				1199: {
					slidesPerView: 4,
				},
				991: {
					slidesPerView: 3,
				},
				767: {
					slidesPerView: 2,
				},
				0: {
					slidesPerView: 1.4,
				},
			},
		});
	}

	if ($('.project-slider-5').length > 0) {
		const projectSlider5 = new Swiper(".project-slider-5", {
			spaceBetween: 30,
			speed: 2000,
			loop: true,
			autoplay: {
				delay: 1000,
				disableOnInteraction: false,
			},
			
			breakpoints: {
				1199: {
					slidesPerView: 3,
				},
				991: {
					slidesPerView: 2,
				},
				767: {
					slidesPerView: 1.8,
				},
				0: {
					slidesPerView: 1.1,
				},
			},
		});
	}

	//testimonial-carousel One
	if ($('.testimonial-swiper-one').length) {
		var swiper = new Swiper(".testimonial-swiper-one", {
			slidesPerView: 1,
			spaceBetween: 10,
			loop: true,
			speed: 2000,
      		freeMode: true,
			navigation: {
				nextEl: ".testimonial-arry-next",
				prevEl: ".testimonial-arry-prev",
			},
		});
	}

	//service-slider
	if ($('.service-slide').length > 0) {
		const serviceSlider = new Swiper(".service-slide", {
			spaceBetween: 25,
			speed: 2000,
			loop: true,
			centeredSlides: true,
			autoplay: {
				delay: 1000,
				disableOnInteraction: false,
			},
			pagination: {
				el: ".dot",
				clickable: true,
			},
			breakpoints: {
				1599: {
					slidesPerView: 1.9,
				},
				1499: {
					slidesPerView: 1.7,
				},
				1399: {
					slidesPerView: 1.6,
				},
				1299: {
					slidesPerView: 1.5,
				},
				1199: {
					slidesPerView: 1.4,
				},
				991: {
					slidesPerView: 1.3,
				},
				767: {
					slidesPerView: 1,
				},
				0: {
					slidesPerView: 1,
				},
			},
		});
	}

	//testimonial-slider
	if ($('.testimonial-slider').length > 0) {
		const testimonialSlider = new Swiper(".testimonial-slider", {
			loop: true,
			autoplay: true,
			spaceBetween: 30,
			speed: 1000,
			navigation: {
				nextEl: ".array-prev",
				prevEl: ".array-next",
			},
		});
	}


	//testimonial-slider-two
	if ($('.testimonial-slider-two').length > 0) {
		const testimonialSliderTwo = new Swiper(".testimonial-slider-two", {
			loop: true,
			autoplay: true,
			spaceBetween: 24,
			speed: 2000,
			autoplay: {
				delay: 1000,
			},
			pagination: {
				el: ".dot",
				clickable: true,
			},
			navigation: {
				nextEl: ".array-prev",
				prevEl: ".array-next",
			},
			breakpoints: {
				0: {
					slidesPerView: 1,
				},
				575: {
					slidesPerView: 1,
				},
				767: {
					slidesPerView: 1.4,
				},
				991: {
					slidesPerView: 2,
				},
				1199: {
					slidesPerView: 1.5,
				},
				1399: {
					slidesPerView: 2,
				},
				1499: {
					slidesPerView: 2.2,
				},
				1599: {
					slidesPerView: 2.3,
				},
				1699: {
					slidesPerView: 2.5,
				},
				1799: {
					slidesPerView: 2.8,
				},
			},
		});
	}
	
	//Service Hover image
	document.addEventListener("DOMContentLoaded", function() {
		const serviceLinks = document.querySelectorAll(".service-block-three");
		const imageBoxes = document.querySelectorAll(".service-hover-image .image-box");

		if (!serviceLinks.length || !imageBoxes.length) return;

		// Initialize first item as active
		serviceLinks[0].classList.add("active");

		serviceLinks.forEach(link => {
			link.addEventListener("mouseenter", function() {
			const index = this.getAttribute("data-index");
			imageBoxes.forEach(box => box.classList.remove("active"));
			const activeBox = document.querySelector(`.service-hover-image .image-box[data-index="${index}"]`);
			if (activeBox) {
				activeBox.classList.add("active");
			}

			serviceLinks.forEach(l => l.classList.remove("active"));
			this.classList.add("active");
			});
		});
	});


	// Thumbs Slider
	const testimonialThumbs = new Swiper('.testimonial-thumbs', {
		spaceBetween: 10,
		slidesPerView: 5,
		freeMode: true,
		watchSlidesProgress: true,
		centeredSlides: true,
		speed: 2000,
		loop: true,
		autoplay: {
			delay: 1000,
			disableOnInteraction: false,
		},
		navigation: {
				nextEl: ".array-prev",
				prevEl: ".array-next",
			},
	});

	//testimonial-slider
	if ($('.testimonial-slider-three').length > 0) {
		const testimonialSliderThree = new Swiper(".testimonial-slider-three", {
			loop: true,
			autoplay: true,
			spaceBetween: 30,
			speed: 2000,
			autoplay: {
				delay: 1000,
				disableOnInteraction: false,
			},
			navigation: {
				nextEl: ".array-prev",
				prevEl: ".array-next",
			},
			pagination: {
				el: ".dot",
				clickable: true,
			},
			thumbs: {
				swiper: testimonialThumbs,
			}
		});
	}



	$('.ai-hero-slider-1').slick({
		speed: 10000,
		autoplay: true,
		autoplaySpeed: 0,
		cssEase: 'linear',
		slidesPerRow: 1,
		slidesToShow: 4,
		arrows: false,
		buttons: false,
		vertical: true,
		verticalSwiping: true,
		responsive: [
			{
				breakpoint: 1200,
				settings: {
				}
			},
			{
				breakpoint: 992,
				settings: {
				}
			},
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 3,
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 3,
				}
			}
		]
	});
	// Swiper marqee area end here ***


	function is_rtl() {
		return $('html').attr('dir') == 'rtl' ? true : false;
	}


	//MixItup Gallery
	if ($(".filter-list").length) {
		$(".filter-list").mixItUp({});
	}

	//Jquery Knob animation  // Pie Chart Animation
	if ($(".dial").length) {
		$(".dial").appear(
			function () {
				var elm = $(this);
				var color = elm.attr("data-fgColor");
				var perc = elm.attr("value");

				elm.knob({
					value: 0,
					min: 0,
					max: 100,
					skin: "tron",
					readOnly: true,
					thickness: 0.15,
					dynamicDraw: true,
					displayInput: false,
				});
				$({ value: 0 }).animate(
					{ value: perc },
					{
						duration: 2000,
						easing: "swing",
						progress: function () {
							elm.val(Math.ceil(this.value)).trigger("change");
						},
					}
				);
				//circular progress bar color
				$(this).append(function () {
					// elm.parent().parent().find('.circular-bar-content').css('color',color);
					//elm.parent().parent().find('.circular-bar-content .txt').text(perc);
				});
			},
			{ accY: 20 }
		);
	}

	//Accordion Box
	if ($('.accordion-box').length) {
		$(".accordion-box").on('click', '.acc-btn', function () {

			var outerBox = $(this).parents('.accordion-box');
			var target = $(this).parents('.accordion');

			if ($(this).hasClass('active') !== true) {
				$(outerBox).find('.accordion .acc-btn').removeClass('active ');
			}

			if ($(this).next('.acc-content').is(':visible')) {
				return false;
			} else {
				$(this).addClass('active');
				$(outerBox).children('.accordion').removeClass('active-block');
				$(outerBox).find('.accordion').children('.acc-content').slideUp(300);
				target.addClass('active-block');
				$(this).next('.acc-content').slideDown(300);
			}
		});
	}

	if ($(".tabs-box").length) {
		$(".tabs-box .tab-buttons .tab-btn").on("click", function (e) {
			e.preventDefault();
			var target = $($(this).attr("data-tab"));

			if ($(target).is(":visible")) {
				return false;
			} else {
				target
					.parents(".tabs-box")
					.find(".tab-buttons")
					.find(".tab-btn")
					.removeClass("active-btn");
				$(this).addClass("active-btn");
				target
					.parents(".tabs-box")
					.find(".tabs-content")
					.find(".tab")
					.fadeOut(0);
				target
					.parents(".tabs-box")
					.find(".tabs-content")
					.find(".tab")
					.removeClass("active-tab animated fadeIn");
				$(target).fadeIn(300);
				$(target).addClass("active-tab animated fadeIn");
			}
		});
	}

	// Scroll to a Specific Div
	if ($('.scroll-to-target').length) {
		$(".scroll-to-target").on('click', function () {
			var target = $(this).attr('data-target');
			// animate
			$('html, body').animate({
				scrollTop: $(target).offset().top
			}, 0);

		});
	}

	// Elements Animation
	if ($('.wow').length) {
		var wow = new WOW(
			{
				boxClass: 'wow',      // animated element css class (default is wow)
				animateClass: 'animated', // animation css class (default is animated)
				offset: 0,          // distance to the element when triggering the animation (default is 0)
				mobile: false,       // trigger animations on mobile devices (default is true)
				live: true       // act on asynchronously loaded content (default is true)
			}
		);
		wow.init();
	}

	//Jquery Knob animation  // Pie Chart Animation
	if ($(".dial").length) {
		$(".dial").appear(
			function () {
				var elm = $(this);
				var color = elm.attr("data-fgColor");
				var perc = elm.attr("value");

				elm.knob({
					value: 0,
					min: 0,
					max: 100,
					skin: "tron",
					readOnly: true,
					thickness: 0.07,
					dynamicDraw: true,
					displayInput: false,
				});

				$({ value: 0 }).animate(
					{ value: perc },
					{
						duration: 2000,
						easing: "swing",
						progress: function () {
							elm.val(Math.ceil(this.value)).trigger("change");
						},
					}
				);

				//circular progress bar color
				$(this).append(function () {
					// elm.parent().parent().find('.circular-bar-content').css('color',color);
					//elm.parent().parent().find('.circular-bar-content .txt').text(perc);
				});
			},
			{ accY: 20 }
		);
	}

	if ($('.service-accordian-item .inner-box').length) {
		const $boxes = $('.service-accordian-item .inner-box');

		if ($boxes.length) {
			// Click logic
			$boxes.on('click', function () {
				$boxes.removeClass('active');
				$('.service-accordian-item .content-box').slideUp().removeClass('active');

				$(this).addClass('active');
				$(this).find('.content-box').slideDown().addClass('active');
			});
		}
	}

	

	$(document).ready(function () {
		$("select").niceSelect();
	});

	//>> Video Popup Start <<//
	$(".img-popup").magnificPopup({
		type: "image",
		gallery: {
			enabled: true,
		},
	});

	$('.video-popup').magnificPopup({
		type: 'iframe',
		callbacks: {
		}
	});

	// count Bar
	if ($(".count-bar").length) {
		$(".count-bar").appear(
			function () {
				var el = $(this);
				var percent = el.data("percent");
				$(el).css("width", percent).addClass("counted");
			},
			{
				accY: -50,
			}
		);
	}


	(function () {
		function animateProgress(id, valueId, endValue, speed) {
			const progress = document.getElementById(id);
			const valueContainer = document.getElementById(valueId);

			if (!progress || !valueContainer) return;

			let currentValue = 0;

			function updateProgress() {
				currentValue++;
				if (currentValue > endValue) {
					currentValue = endValue;
				}
				valueContainer.textContent = `${currentValue}%`;
				progress.style.background = `conic-gradient(
					#C8F169 ${currentValue * 3.6}deg,
					#D4D4D4 ${currentValue * 3.6}deg
				)`;

				if (currentValue < endValue) {
					setTimeout(() => requestAnimationFrame(updateProgress), speed);
				}
			}

			requestAnimationFrame(updateProgress);
		}

		// Initialize progress bars only if their elements exist
		document.addEventListener("DOMContentLoaded", function () {
			if (document.getElementById('progress1') && document.getElementById('value1')) {
				animateProgress('progress1', 'value1', 95, 20);
			}
			if (document.getElementById('progress2') && document.getElementById('value2')) {
				animateProgress('progress2', 'value2', 85, 20);
			}
			if (document.getElementById('progress3') && document.getElementById('value3')) {
				animateProgress('progress3', 'value3', 85, 20);
			}
			if (document.getElementById('progress4') && document.getElementById('value4')) {
				animateProgress('progress4', 'value4', 85, 20);
			}
			if (document.getElementById('progress5') && document.getElementById('value5')) {
				animateProgress('progress5', 'value5', 85, 20);
			}
			if (document.getElementById('progress6') && document.getElementById('value6')) {
				animateProgress('progress6', 'value6', 85, 20);
			}
			if (document.getElementById('progress7') && document.getElementById('value7')) {
				animateProgress('progress7', 'value7', 85, 20);
			}
			if (document.getElementById('progress8') && document.getElementById('value8')) {
				animateProgress('progress8', 'value8', 85, 20);
			}
		});


	})();

	



})(window.jQuery);
