$(() => {
	/* ---------------------------------------
	 *  Partials
	 * ---------------------------------------
	 */

	/*
	 * dbGallery
	 */
	$('.ib-dbGallery-slider').slick({
		prevArrow: $('#dbGalleryPrevButton'),
		nextArrow: $('#dbGalleryNextButton'),
		slidesToShow: 1,
		dots: true,
		responsive: [{
			breakpoint: 900,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	});

	/*
	 * dbNews
	 */
	$('.ib-dbNews-slider').slick({
		prevArrow: $('#dbNewsPrevButton'),
		nextArrow: $('#dbNewsNextButton'),
		slidesToShow: 2,
		slidesToScroll: 2,
		dots: true,
		responsive: [{
			breakpoint: 900,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	});

	/*
	 * dbSidebar
	 */
	var contactFormOpen = false;
	$('.db-contactform-jump').on('click', function () {
		var tmpAnchor = $(this).data("anchor");
		if (!contactFormOpen) {
			$("#" + tmpAnchor + " .ib-collapsible-header").click();
			contactFormOpen = true;
		}
		$('html,body').animate({
			scrollTop: $('#' + tmpAnchor).offset().top - 100
		}, 'fast');
	});

    $('#ib-partial-startpageslider div.slick-arrow').on('click', () => {
        $('.ib-startpage-slider').slick('slickPause');
    });
    if ($('#ib-partial-startpageslider .slick-slide:not(.slick-cloned)').length <= 1) {
        $('#ib-slider-controls-toggle').css('display', 'none');
    }

	/*
	 * dbPartner
	 */
	$('.ib-partner-collapsible-trigger').on('click', function () {
		var collapsibleID = $(this).data('ibcollapsibleid');
		$("#ib-partner-collapsible-content-" + collapsibleID).slideToggle(300);
		$("#ib-partner-collapsible-" + collapsibleID + " i").toggleClass('ib-icon-arrow-right ib-icon-arrow-down');
	});

    /**
	 * sponsor scroll
	 */
	$('.ib-sidebar-sponsor-imagelink').on('click', function () {
		var anchorid = $(this).data('anchorid');
		$('html, body').animate({
			scrollTop: $("#" + anchorid).offset().top - 100
		}, 300);
	});



});
