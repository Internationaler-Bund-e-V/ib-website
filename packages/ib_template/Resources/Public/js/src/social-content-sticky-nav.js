/**
 * ---------------------------------------------
 * SOCIAL SHARE STICKY NAV
 * ---------------------------------------------
 */
$(document).ready(function () {


	//
	//make social share buttons sticky after slick init to calculate correct position
	//

	//variables needed for sticky social
	var stickyoffset = 120;
	var stickydifference;
	var stickytop;

	/**
	 * function to calculate offsets
	 */
	function stickyvalues() {
		var ibSocial = $('.ib-social');
		var ibSocialContent = $('.ib-social-content');

		if ($(ibSocial).length) {
			stickydifference = $(ibSocial).offset().top - $(ibSocialContent).offset().top;
			stickytop = $(ibSocialContent).offset().top - stickyoffset;
		}
	}

	/**
	 * this function handles the scrolling behaviour for the
	 * sticky social nav on the right side
	 *
	 * @param subject
	 * @param stickyoffset
	 * @param stickydifference
	 * @param stickytop
	 */
	function handleScrolling(subject, stickyoffset, stickydifference, stickytop) {
		$(window).scroll(function (event) {

			var ibSocial = $('.ib-social');
			var y = $(subject).scrollTop();
			if (y >= stickytop) {
				$(ibSocial).addClass('fixed');
				$(ibSocial).css('top', stickyoffset + stickydifference + "px");
			}
			else {
				$(ibSocial).removeClass('fixed');
				$(ibSocial).css('top', '');
			}
		});
	}

	// -----------------------------------------------
	// recalculate on resize
	// -----------------------------------------------
	$(window).resize(function () {
		stickyvalues();
	});


	// -----------------------------------------------
	// init function after slick init if header slider
	// present else add class fixed to social
	// -----------------------------------------------
	if ($('#ib-partial-startpageslider').find('.ib-slider-container').length != 0) {
		$('.ib-startpage-slider').on('init', function (event, slick, currentSlide, nextSlide) {
			setTimeout(function () {

				var ibSliderContainer = $('.ib-slider-container');
				var ibSocial = $('.ib-social');

				$('.ib-social-content').show();

				// check how much offset is set on slider
				// and set class on social to manage offset of social
				if ($(ibSliderContainer).hasClass('offset--large')) {
					$(ibSocial).addClass('offset--large');
				} else if ($(ibSliderContainer).hasClass('offset--small')) {
					$(ibSocial).addClass('offset--small');
				}

				// offset: define distance from top when sticky
				stickyvalues();
				var subject = $(this);
				handleScrolling(subject, stickyoffset, stickydifference, stickytop);
			}, 500);
		});
	} else {

		// this is for all pages that have no slider in the head
		// here we also need a scroll to fixed behaviour
		$('.ib-social-content').show();


		// if there is absolutely no image in the header
		if ($('div#ib-partial-startpageslider').children('img').length == 0) {
			$('.ib-social').addClass('fixed-no-slider-no-image');
			stickyvalues();
			stickytop = 45;
			stickyoffset = 50;
		}
		// if there is no slider but i.e. a youtube video
		else {
			$('.ib-social').addClass('fixed-no-slider');
			stickyvalues();
		}

		var subject = $(this);
		handleScrolling(subject, stickyoffset, stickydifference, stickytop);
	}


});