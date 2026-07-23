	// Smooth Scroll Paused True When Need Active Make It False
	// ScrollSmoother.get()?.paused(true);
	const smoother = ScrollSmoother.get();
	if (smoother) {
		smoother.kill();
	}


	// Register GSAP Plugins

	//Smooth Scroll

	// gsap.registerPlugin(ScrollTrigger, ScrollSmoother, TweenMax, ScrollToPlugin);
	// gsap.config({
	// 	nullTargetWarn: false,
	// });
	// let smoother = ScrollSmoother.create({
	// 	smooth: 2,
	// 	effects: true,
	// 	smoothTouch: 0.1,
	// 	normalizeScroll: false,
	// 	ignoreMobileResize: true,
	// });


	// 18. webgl images hover animation //
	if ($('.tp--hover-item').length) {
		let hoverAnimation__do = function (t, n) {
			let a = new hoverEffect({
				parent: t.get(0),
				intensity: t.data("intensity") || void 0,
				speedIn: t.data("speedin") || void 0,
				speedOut: t.data("speedout") || void 0,
				easing: t.data("easing") || void 0,
				hover: t.data("hover") || void 0,
				image1: n.eq(0).attr("src"),
				image2: n.eq(0).attr("src"),
				displacementImage: t.data("displacement"),
				imagesRatio: n[0].height / n[0].width,
				hover: !1
			});
			t.closest(".tp--hover-item").on("mouseenter", function () {
				a.next()
			}).on("mouseleave", function () {
				a.previous()
			})
		}
		let hoverAnimation = function () {
			$(".tp--hover-img").each(function () {
				let n = $(this);
				let e = n.find("img");
				let i = e.eq(0);
				i[0].complete ? hoverAnimation__do(n, e) : i.on("load", function () {
					hoverAnimation__do(n, e)
				})
			})
		}
		hoverAnimation();
	}


	// Text Invert With Scroll
	const split = new SplitText(".text_invert, .text_invert-2", { type: "lines" });

	split.lines.forEach((target) => {
		gsap.to(target, {
			backgroundPositionX: 0,
			ease: "none",
			scrollTrigger: {
				trigger: target,
				scrub: 1,
				start: 'top 85%',
				end: "bottom center",
			}
		});
	});
	////fade-class-active
	if ($(".ks_fade_anim").length > 0) {
		gsap.utils.toArray(".ks_fade_anim").forEach((item) => {
			let ks_fade_offset = item.getAttribute("data-fade-offset") || 40,
				ks_duration_value = item.getAttribute("data-duration") || 0.75,
				ks_fade_direction = item.getAttribute("data-fade-from") || "bottom",
				ks_onscroll_value = item.getAttribute("data-on-scroll") || 1,
				ks_delay_value = item.getAttribute("data-delay") || 0.15,
				ks_ease_value = item.getAttribute("data-ease") || "power2.out",
				ks_anim_setting = {
					opacity: 0,
					ease: ks_ease_value,
					duration: ks_duration_value,
					delay: ks_delay_value,
					x: (ks_fade_direction == "left" ? -ks_fade_offset : (ks_fade_direction == "right" ? ks_fade_offset : 0)),
					y: (ks_fade_direction == "top" ? -ks_fade_offset : (ks_fade_direction == "bottom" ? ks_fade_offset : 0)),
				};
			if (ks_onscroll_value == 1) {
				ks_anim_setting.scrollTrigger = {
					trigger: item,
					start: 'top 85%',
				};
			}
			gsap.from(item, ks_anim_setting);
		});
	}



	// split text animation
	if ($('.split-text').length > 0) {
		var st = $(".split-text");
		gsap.registerPlugin(SplitText);
		st.each(function (index, el) {
			el.split = new SplitText(el, {
				type: "lines,words,chars",
				linesClass: "split-line"
			});
			gsap.set(el, { perspective: 400 });

			if ($(el).hasClass('split-in-right')) {
				gsap.set(el.split.chars, {
					opacity: 0,
					x: "50",
					ease: "Back.easeOut",
				});
			}
			if ($(el).hasClass('split-in-left')) {
				gsap.set(el.split.chars, {
					opacity: 0,
					x: "-50",
					ease: "circ.out",
				});
			}
			el.anim = gsap.to(el.split.chars, {
				scrollTrigger: {
					trigger: el,
					start: "top 90%",
				},
				x: "0",
				y: "0",
				rotateX: "0",
				scale: 1,
				opacity: 1,
				duration: 0.4,
				stagger: 0.02,
			});
		});
	}
	// Image Reveal Animation
	let tp_img_reveal = document.querySelectorAll(".tp_img_reveal");

	tp_img_reveal.forEach((img_reveal) => {
		let image = img_reveal.querySelector("img");
		let tl = gsap.timeline({
			scrollTrigger: {
				trigger: img_reveal,
				start: "top 10%",
			}
		});

		tl.set(img_reveal, { autoAlpha: 1 });
		tl.from(img_reveal, 1.5, {
			yPercent: -100,
			ease: Power2.out
		});
		tl.from(image, 1.5, {
			yPercent: 100,
			scale: 1.5,
			delay: -1.5,
			ease: Power2.out
		});
	});
	 /* ================================
       Des Portfolio Anim Js Start
    ================================ */

    if (document.querySelector(".des-portfolio-wrap")) {
        const pr = ScrollTrigger.matchMedia();

        pr.add("(min-width: 1199px)", () => {

            const sections = document.querySelectorAll(".des-portfolio-panel");
            const wrap = document.querySelector(".des-portfolio-wrap");

            if (!sections.length || !wrap) return;

            // Initial state
            gsap.set(sections, { scale: 1 });

            // Animate each section except the last one
            sections.forEach((section, index) => {
                const isLast = index === sections.length - 1;

                gsap.to(section, {
                    scale: isLast ? 1 : 0.8, // 👈 last one stays full-size
                    ease: "none",
                    scrollTrigger: {
                        trigger: section,
                        start: "top top",
                        end: "bottom 60%",
                        scrub: true,
                        pin: true,
                        pinSpacing: false,
                        endTrigger: wrap,
                        markers: false,
                    },
                });
            });

            // Cleanup on condition change
            return () => {
                ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
            };
        });
    }

	// 19 .service panel animation //
	let sv = gsap.matchMedia();
	sv.add("(min-width: 991px)", () => {
		let tl = gsap.timeline();
		let projectpanels = document.querySelectorAll('.tp-service-panel');
		let baseOffset = 150;
		let offsetIncrement = 180;

		projectpanels.forEach((section, index) => {
			let topOffset = baseOffset + (index * offsetIncrement);
			tl.to(section, {
				scrollTrigger: {
					trigger: section,
					pin: section,
					scrub: 1,
					start: `top ${topOffset}px`,
					end: "bottom 100%",
					endTrigger: '.tp-service-pin',
					pinSpacing: false,
					markers: false,
				},
			});
		});
	});


	// 19 Case panel animation //
	let ca = gsap.matchMedia();
	ca.add("(min-width: 1199px)", () => {
		let tl = gsap.timeline();
		let projectpanels = document.querySelectorAll('.tp-case-panel');
		let baseOffset = 200;
		let offsetIncrement = 0;

		projectpanels.forEach((section, index) => {
			let topOffset = baseOffset + (index * offsetIncrement);
			tl.to(section, {
				scrollTrigger: {
					trigger: section,
					pin: section,
					scrub: 1,
					start: `top ${topOffset}px`,
					end: "bottom 100%",
					endTrigger: '.tp-case-pin',
					pinSpacing: false,
					markers: false,
				},
			});
		});
	});


	// 21. stack panel pin section //
	let sp = gsap.matchMedia();
	sp.add("(min-width: 1199px)", () => {
		let tl = gsap.timeline();
		let panels = document.querySelectorAll('.stack-panel-pin')
		panels.forEach((section, index) => {
			tl.to(section, {
				scrollTrigger: {
					trigger: section,
					pin: section,
					scrub: 1,
					start: 'top 0%',
					end: "bottom 100%",
					endTrigger: '.stack-panel-pin-area',
					pinSpacing: false,
					markers: false,
				},
			})
		})
	});

	let sv2 = gsap.matchMedia();
	sv2.add("(min-width: 1199px)", () => {
		let tl = gsap.timeline();
		let projectpanels = document.querySelectorAll('.tp-service-panel');
		let baseOffset = 150;
		let offsetIncrement = 120;

		projectpanels.forEach((section, index) => {
			let topOffset = baseOffset + (index * offsetIncrement);
			tl.to(section, {
				scrollTrigger: {
					trigger: section,
					pin: section,
					scrub: 1,
					start: `top ${topOffset}px`,
					end: "bottom 90%",
					endTrigger: '.tp-service-pin',
					pinSpacing: false,
					markers: false,
				},
			});
		});
	});

	 // Text on Windows  Animation Start
    // Animation-1
    let tHero = gsap.timeline();

    let heading_title = new SplitText(".sec-title-1", { type: "chars" });
    let heading_char = heading_title.chars;

    tHero.from(heading_char, {
      rotate: 20,
      ease: "back.out",
      opacity: 0,
      duration: 2,
      stagger: 0.1,
    });

	if ($('.tp-project-5-2-area').length > 0) {
		let project_text = gsap.timeline({
			scrollTrigger: {
				trigger: ".tp-project-5-2-area",
				start: 'top center-=350',
				end: "bottom 105%",
				pin: ".tp-project-5-2-title",
				markers: false,
				pinSpacing: false,
				scrub: 1,
			}
		})
		project_text.set(".tp-project-5-2-title", {
			scale: 1,
			duration: 2
		})
		project_text.to(".tp-project-5-2-title", {
			scale: 3.5,
			duration: 2
		})
		project_text.to(".tp-project-5-2-title", {
			scale: 3.5,
			duration: 2
		}, "+=2")

    //  project_text.to(".tp-project-5-2-title", {
    //     autoAlpha: 0,
    //     duration: 2
    // });
	}

	// Button inner move effect
	const btnMove = gsap.utils.toArray(".btn-move");
	const btnItems = gsap.utils.toArray(".btn-item");

	btnMove.forEach((btn, i) => {
		const item = btnItems[i];

		btn.addEventListener("mousemove", (e) => {
			const rect = btn.getBoundingClientRect();
			const x = e.clientX - rect.left;
			const y = e.clientY - rect.top;
			const range = 80;

			gsap.to(item, {
				duration: 0.3,
				x: (x - rect.width / 2) / rect.width * range,
				y: (y - rect.height / 2) / rect.height * range,
				scale: 1.2,
				ease: "power2.out"
			});
		});

		btn.addEventListener("mouseleave", () => {
			gsap.to(item, {
				duration: 0.3,
				x: 0,
				y: 0,
				scale: 1,
				ease: "power2.out"
			});
		});
	});

	// Add span ripple effect to .btn-hover-bgchange
	document.querySelectorAll(".btn-hover-bgchange").forEach((btn) => {
		const span = document.createElement("span");
		btn.appendChild(span);

		btn.addEventListener("mouseenter", (e) => {
			const rect = btn.getBoundingClientRect();
			span.style.left = (e.clientX - rect.left) + "px";
			span.style.top = (e.clientY - rect.top) + "px";
		});

		btn.addEventListener("mouseout", (e) => {
			const rect = btn.getBoundingClientRect();
			span.style.left = (e.clientX - rect.left) + "px";
			span.style.top = (e.clientY - rect.top) + "px";
		});
	});

	// Custom cursor
	document.addEventListener("mousemove", (e) => {
		gsap.to(".cursor1", {
			x: e.clientX,
			y: e.clientY,
			duration: 0.3,
			ease: "power2.out"
		});

		gsap.to(".cursor2", {
			x: e.clientX,
			y: e.clientY,
			duration: 0.6,
			ease: "power2.out"
		});
	});

	gsap.utils.toArray(".tm-gsap-img-parallax").forEach(function(container) {
      let image = container.querySelector("img");

      let tl = gsap.timeline({
          scrollTrigger: {
              trigger: container,
              scrub: .5,
          },
      });
      tl.from(image, {
          yPercent: -30,
          ease: "none",
      }).to(image, {
          yPercent: 30,
          ease: "none",
      });
    });


	 gsap.utils.toArray('.tm-gsap-animate-circle').forEach((el, index) => {
        let arspin = gsap.timeline({
            scrollTrigger: {
                trigger: el,
                scrub: 1,
                start: "top 100%",
                end: "top -50%",
                toggleActions: "play none none reverse",
                markers: false
            }
        })

        arspin
        .set(el, {transformOrigin: 'center center'})
        .fromTo(el, { rotate: 0}, { rotate: 180, duration: 2, immediateRender: false})
    });


	// 43. choose anim //
	if (document.querySelectorAll('.design-choose-item-wrap').length) {
    const pw = gsap.matchMedia();
    pw.add("(min-width: 1200px)", () => {
        document.querySelectorAll('.design-choose-item-wrap').forEach(wrap => {

            // প্রতিটি pair এর জন্য
            const pairs = wrap.querySelectorAll('.col-xl-6:nth-child(odd)');
            pairs.forEach(pair => {
                const item1 = pair.querySelector('.design-choose-item-1');
                const item2 = pair.nextElementSibling?.querySelector('.design-choose-item-2');

                if (item1 && item2) {
                    // Initial set
                    gsap.set(item1, { x: -400, rotate: -40 });
                    gsap.set(item2, { x: 400, rotate: 40 });

                    // Timeline with trigger per pair
                    let tl = gsap.timeline({
                        scrollTrigger: {
                            trigger: pair, // প্রতিটি pair আলাদা trigger
                            start: 'top 90%',
                            end: 'top 20%',
                            scrub: 1,
                        }
                    });

                    tl.to(item1, { x: 0, rotate: 0 })
                      .to(item2, { x: 0, rotate: 0 }, 0);
                }
            });

        });
    });
	}

	 gsap.utils
		.toArray(".zoom-effect-style")
		.forEach((el, index) => {
			let tl1 = gsap.timeline({
				scrollTrigger: {
					trigger: el,
					scrub: 1,
					start: "top 80%",
					end: "buttom 60%",
					toggleActions: "play none none reverse",
					markers: false,
				},
			});

			tl1.set(el, { transformOrigin: "center center" }).from(
				el,
				{ scale: 0.7 },
				{
					background: "inherit",
					scale: 1,
					duration: 1,
					immediateRender: false,
				}
			);
		});

		gsap.utils.toArray(" .appear_left").forEach((el, index) => {
		let tlcta = gsap.timeline({
			scrollTrigger: {
				trigger: el,
				scrub: 2,
				start: "top 90%",
				end: "top 70%",
				toggleActions: "play none none reverse",
				markers: false,
			},
		});

		tlcta
			.set(el, { transformOrigin: "center center" })
			.from(
				el,
				{ opacity: 1, x: "-=150" },
				{ opacity: 1, x: 0, duration: 1, immediateRender: false }
			);
	    });

		//Image Reveal Animation  used
		let imgs_reveal = document.querySelectorAll(".img-reveal");

		imgs_reveal.forEach((container) => {
			let image = container.querySelector("img");
			let tl = gsap.timeline({
			scrollTrigger: {
				trigger: container,
				toggleActions: "restart none none reset"
			}
			});

			tl.set(container, { autoAlpha: 1 });
			tl.from(container, 1.5, {
			xPercent: -100,
			ease: Power2.out
			});
			tl.from(image, 1.5, {
			xPercent: 100,
			scale: 1.3,
			delay: -1.5,
			ease: Power2.out
			});
		});

		document.querySelectorAll(".scroll-text").forEach((section) => {
		let tl = gsap.timeline({
			scrollTrigger: {
			trigger: section,
			start: "top 100%",
			end: "bottom top",
			scrub: 1,
			markers: false,
			},
		});
		tl.from(section.querySelector(".text1"), { xPercent: 20 })
		.from(section.querySelector(".text2"), { xPercent: -20 }, 0);
		tl.from(section.querySelector(".scroll-anim-top"), { yPercent: 10 }, 0)
		.from(section.querySelector(".scroll-anim-bottom"), { yPercent: -10 }, 0);
	});

	document.querySelectorAll(".titlt-bottom-top").forEach((section) => {
		gsap.from(section, {
			scrollTrigger: {
				trigger: section,
				start: "top 110%",   // যখন section viewport এর কাছে আসবে
				end: "bottom top",
				scrub: true,
				markers: false,
			},
			y: 300,   // নিচ থেকে আসবে
			opacity: 0,
			duration: 1,
			ease: "power3.out"
		});
	});

	// About shape
	if (document.querySelector(".right-to-left-ani")) {
		let counterImgTL = gsap.timeline({
		  scrollTrigger: {
		    trigger: ".right-to-left-ani",
		    start: "top 80%",
		    end: "bottom 10%",
		    scrub: 2,
		    markers: false,
		  }
		});
		counterImgTL.fromTo(".right-to-left-ani",
		  {
		    x: 200,
		  },
		  {
		    x: 0,
		    duration: 1.6
		  }
		);
	}

	if (document.querySelectorAll(".left-to-right-ani").length) {
		let elements = document.querySelectorAll(".left-to-right-ani");
		elements.forEach((el) => {
			let tl = gsap.timeline({
				scrollTrigger: {
					trigger: el,
					start: "top 80%",
					end: "bottom 10%",
					scrub: 2,
					markers: false,
				},
			});

			tl.fromTo(el,
				{ x: -200 },
				{ x: 0, duration: 1.6 }
			);
		});
	}

	// 20. panel pin section //
	document.addEventListener("DOMContentLoaded", function () {
	if (document.querySelector(".tp-panel-pin")) { // শুধু এই এলিমেন্ট থাকলে
		let prw = gsap.matchMedia();

		prw.add("(min-width: 992px)", () => {
			let tl = gsap.timeline();
			let panels = document.querySelectorAll('.tp-panel-pin');
			let valueEl = document.querySelector(".step-btn .value");

			if (!valueEl) return; // value element না থাকলে stop করো

			valueEl.textContent = '';
			let total = panels.length;

			ScrollTrigger.create({
				trigger: panels[0],
				pin: true,
				start: 'top 10%',
				end: 'bottom 70%',
				pinSpacing: false,
				markers: false
			});

			for (let i = 0; i < panels.length; i++) {
				let panel = panels[i];

				tl.to(panel, {
					scrollTrigger: {
						trigger: panel,
						pin: panel,
						scrub: 1,
						start: 'top 10%',
						end: 'bottom 70%',
						endTrigger: '.tp-panel-pin-area',
						pinSpacing: false,
						markers: false,
						onEnter: () => {
							valueEl.textContent = `${i}/${total - 1}`;
						},
						onEnterBack: () => {
							valueEl.textContent = `${i}/${total - 1}`;
						}
					}
				});
			}
		});
	}
	});

	document.addEventListener("DOMContentLoaded", function () {
	    if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
	        gsap.registerPlugin(ScrollTrigger);

	        ScrollTrigger.matchMedia({
	            "(min-width: 1200px)": function () {
	                const animations = [
	                    { selector: ".get-in-text", x: 200 },
	                    // চাইলে আরও class add করতে পারো
	                    // { selector: ".another-class", x: -200 },
	                ];

	                animations.forEach(anim => {
	                    const elements = document.querySelectorAll(anim.selector);
	                    if (elements.length) {
	                        elements.forEach(el => {
	                            gsap.to(el, {
	                                x: anim.x,
	                                ease: "none",
	                                scrollTrigger: {
	                                    trigger: el,
	                                    start: "top bottom",
	                                    end: "top top",
	                                    scrub: true,
	                                },
	                            });
	                        });
	                    }
	                });
	            }
	        });
	    }
	});

	//>> Mouse Cursor Start <<//

	if (!document.body.classList.contains("is-mobile") && document.querySelector("#custom-cursor-wrapper.tp-cursor")) {
		$(".tp-magnetic-item").wrap('<div class="tp-magnetic-wrap"></div>');

		if ($("a.tp-magnetic-item").length) {
			$("a.tp-magnetic-item").addClass("not-hide-cursor");
		}

		var $mouse = { x: 0, y: 0 };
		var $pos = { x: 0, y: 0 };
		var $ratio = 0.15;
		var $active = false;
		var $cursorDot = $("#cursorDot");

		var $cursorDotWidth = 14;
		var $cursorDotHeight = 14;
		var $cursorDotScale = 1;
		var $cursorDotOpacity = 1;
		var $cursorDotBorderWidth = 1;

		gsap.set($cursorDot, {
			xPercent: -50,
			yPercent: -50,
			width: $cursorDotWidth,
			height: $cursorDotHeight,
			borderWidth: $cursorDotBorderWidth,
			opacity: $cursorDotOpacity
		});

		document.addEventListener("mousemove", mouseMove);

		function mouseMove(e) {
			$mouse.x = e.clientX;
			$mouse.y = e.clientY;
		}

		gsap.ticker.add(updatePosition);

		function updatePosition() {
			if (!$active) {
				$pos.x += ($mouse.x - $pos.x) * $ratio;
				$pos.y += ($mouse.y - $pos.y) * $ratio;

				gsap.set($cursorDot, { x: $pos.x, y: $pos.y });
			}
		}

		$(".tp-magnetic-wrap").mousemove(function (e) {
			parallaxCursor(e, this, 2); // magnetic cursorDot = low number is more attractive
			callParallax(e, this);
		});

		function callParallax(e, parent) {
			parallaxIt(e, parent, parent.querySelector(".tp-magnetic-item"), 25); // magnetic area = higher number is more attractive
		}

		function parallaxIt(e, parent, target, movement) {
			var boundingRect = parent.getBoundingClientRect();
			var relX = e.clientX - boundingRect.left;
			var relY = e.clientY - boundingRect.top;

			gsap.to(target, {
				duration: 0.3,
				x: ((relX - boundingRect.width / 2) / boundingRect.width) * movement,
				y: ((relY - boundingRect.height / 2) / boundingRect.height) * movement,
				ease: Power2.easeOut
			});
		}

		function parallaxCursor(e, parent, movement) {
			var rect = parent.getBoundingClientRect();
			var relX = e.clientX - rect.left;
			var relY = e.clientY - rect.top;
			$pos.x = rect.left + rect.width / 2 + (relX - rect.width / 2) / movement;
			$pos.y = rect.top + rect.height / 2 + (relY - rect.height / 2) / movement;
			gsap.to($cursorDot, { duration: 0.3, x: $pos.x, y: $pos.y });
		}


		// Magnetic item hover.
		$(".tp-magnetic-wrap").on("mouseenter", function (e) {
			gsap.to($cursorDot, { duration: 0.3, scale: 2, borderWidth: 1, opacity: $cursorDotOpacity });
			$active = true;
		}).on("mouseleave", function (e) {
			gsap.to($cursorDot, { duration: 0.3, scale: $cursorDotScale, borderWidth: $cursorDotBorderWidth, opacity: $cursorDotOpacity });
			gsap.to(this.querySelector(".tp-magnetic-item"), { duration: 0.3, x: 0, y: 0, clearProps: "all" });
			$active = false;
		});

		// Cursor view on hover (data attribute "data-cursor="...").
		$("[data-cursor]").each(function () {
			$(this).on("mouseenter", function () {
				$("#cursorDot").addClass("with-blur");
				$cursorDot.append('<div class="cursorDot-view"></div>');
				$(".cursorDot-view").append($(this).attr("data-cursor"));
				gsap.to($cursorDot, {
					duration: 0.3, xPercent: is_rtl() ? 50 : -50, yPercent: -60, width: 110, height: 110, opacity: 1, borderWidth: 0, zIndex: 1, backdropFilter: "blur(14px)",
					backgroundColor: "#fff"
				});
				gsap.to(".cursorDot-view", { duration: 0.3, scale: 1, autoAlpha: 1 });
			}).on("mouseleave", function () {
				gsap.to($cursorDot, { duration: 0.3, yPercent: -50, width: $cursorDotWidth, height: $cursorDotHeight, opacity: $cursorDotOpacity, borderWidth: $cursorDotBorderWidth, backgroundColor: "#000" });
				gsap.to(".cursorDot-view", { duration: 0.3, scale: 0, autoAlpha: 0, clearProps: "all" });
				$cursorDot.find(".cursorDot-view").remove();
			});
			$(this).addClass("not-hide-cursor");
		});

		$("[data-cursor2]").each(function () {
			$(this).on("mouseenter", function () {
				$("#cursorDot").addClass("with-blur");
				$cursorDot.append('<div class="cursorDot-drag"></div>');
				$(".cursorDot-drag").append($(this).attr("data-cursor2"));
				gsap.to($cursorDot, {
					duration: 0.3, xPercent: is_rtl() ? 50 : -50, yPercent: -60, width: 110, height: 110, opacity: 1, borderWidth: "1px", borderColor: "rgba(255, 255, 255, 0.22)", zIndex: 1, backdropFilter: "blur(34px)",
					backgroundColor: "rgba(255, 255, 255, 0.30)", boxShadow: "11px 11px 32.2px 0px rgba(255, 255, 255, 0.12) inset"
				});
				gsap.to(".cursorDot-drag", { duration: 0.3, scale: 1, autoAlpha: 1 });
			}).on("mouseleave", function () {
				gsap.to($cursorDot, { duration: 0.3, yPercent: -50, width: $cursorDotWidth, height: $cursorDotHeight, opacity: $cursorDotOpacity, borderWidth: $cursorDotBorderWidth, backgroundColor: "#000" });
				gsap.to(".cursorDot-drag", { duration: 0.3, scale: 0, autoAlpha: 0, clearProps: "all" });
				$cursorDot.find(".cursorDot-drag").remove();
			});
			$(this).addClass("not-hide-cursor2");
		});
		// Show/hide cursor //

		// Hide on hover//
		$("a, button") // class "hide-cursor" is for global use.
			.not('.cursor-hide') // omit from selection.
			.on("mouseenter", function () {
				gsap.to($cursorDot, { duration: 0.3, scale: 0, opacity: 0 });
			}).on("mouseleave", function () {
				gsap.to($cursorDot, { duration: 0.3, scale: $cursorDotScale, opacity: $cursorDotOpacity });
			});

		// Hide on click//
		$("a")
			.not('[target="_blank"]') // omit from selection.
			.not('.cursor-hide') // omit from selection.
			.not('[href^="#"]') // omit from selection.
			.not('[href^="mailto"]') // omit from selection.
			.not('[href^="tel"]') // omit from selection.
			.not(".lg-trigger") // omit from selection.
			.not(".tp-btn-disabled a") // omit from selection.
			.on('click', function () {
				gsap.to($cursorDot, { duration: 0.3, scale: 1.3, autoAlpha: 0 });
			});

		// Show/hide on document leave/enter//
		$(document).on("mouseleave", function () {
			gsap.to("#cursor-outer", { duration: 0.3, autoAlpha: 0 });
		}).on("mouseenter", function () {
			gsap.to("#cursor-outer", { duration: 0.3, autoAlpha: 1 });
		});

		// Show as the mouse moves//
		$(document).mousemove(function () {
			gsap.to("#cursor-outer", { duration: 0.3, autoAlpha: 1 });
		});
	}
