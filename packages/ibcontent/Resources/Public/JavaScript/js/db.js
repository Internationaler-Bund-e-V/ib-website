$(document).ready(function () {
	/*
	 * ---------------------------------------
	 * Produktsuche / Jobsuche
	 * ---------------------------------------
	 * @see DbShowJoblist.html
	 */
	$('.citySearchInputJobs').on('input focus', function () {
		searchTitleJobs();
	});

	// eventhandler for the jobtags dropown in
	// MyContent/DbShowJoblist.html
	$('#jobtags').on('change', function () {
		$('.citySearchInputJobs').val('');
		setCategoriesJobs();
	});

	function searchTitleJobs() {
		setCategoriesJobs();
		var searchTag = $('.citySearchInputJobs').val().toLowerCase();
		var categories = $('#jobtags');
		var jobID = "-" + categories.val() + "-";
		if (searchTag.length > 0) {
			$('.job.enabled').each(function () {
				var title = $(this).find('a');
				var job = $(this);
				if (!(title.attr('title').toLowerCase().indexOf(searchTag) >= 0)) {
					job.hide().removeClass('enabled');
				} else {
					job.show().addClass('enabled');
				}
			});

			$('.citytitle.enabled').each(function () {
				var city = $(this);
				if (($(this).find(".productlist li.enabled")).length == 0) {
					$(this).hide().removeClass('enabled');
				}
				if ((city.data('citysearch').toLowerCase().indexOf(searchTag) >= 0)) {
					if (jobID != "-0-") {
						$(this).find(".productlist li.job").each(function () {
							var job = $(this);
							if (!(job.data('jobtags').toLowerCase().indexOf(jobID) >= 0)) {
								job.hide().removeClass('enabled');
							} else {
								job.show().addClass('enabled');
							}
						});
					}
					else {
						$(this).find(".productlist li.job").each(function () {
							$(this).show().addClass('enabled');
						});
					}
					$(this).show().addClass('enabled');
				}
			});
		}
	}

	function setCategoriesJobs() {
		$('#db-job-container').hide();
		var categories = $('#jobtags');
		var jobID = "-" + categories.val() + "-";
		if (jobID == "-0-") {
			$('.job').show().addClass('enabled');
			$('.db-citycontainer').show();
		} else {
			$('.job').each(function () {
				var job = $(this);
				if (!(job.data('jobtags').toLowerCase().indexOf(jobID) >= 0)) {
					job.hide().removeClass('enabled');
				} else {
					job.show().addClass('enabled');
				}
			});
		}

		$('.db-citycontainer').each(function () {
			if (($(this).find(".productlist li.enabled")).length == 0) {
				$(this).hide().removeClass('enabled');
			} else {
				$(this).show().addClass('enabled');
			}
		});
		$('.citytitle.enabled').promise().done(function () {
			$('#db-job-container').show();
		});
	}


	/*
	 * ---------------------------------------
	 * Produktsuche
	 * ---------------------------------------
	 * @see DbShowCategory.html
	 */
	$('.citySearchInput').on('input focus', function () {
		searchTitle();
	});

	function searchTitle() {
		var searchTag = $('.citySearchInput').val().toLowerCase();
		$('.citytitle.enabled').each(function () {
			var city = $(this);
			if (!(city.data('citysearch').toLowerCase().indexOf(searchTag) >= 0)) {
				city.hide('fast');
			} else {
				city.show('fast');
			}
		});
	}

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

	/*
	 * dbAccordion
	 * @see moved to /typo3conf/ext/ib_template/Resources/Public/js/src/foundation-accordion.js
	 */

	/*
	 * dbPartner
	 */
	$('.ib-partner-collapsible-trigger').on('click', function () {
		var collapsibleID = $(this).data('ibcollapsibleid');
		$("#ib-partner-collapsible-content-" + collapsibleID).slideToggle(300);
		$("#ib-partner-collapsible-" + collapsibleID + " i").toggleClass('ib-icon-arrow-right ib-icon-arrow-down');
	});

	/*
	 * Header Slider
	 */
	$('.ib-startpage-slider').not('.slick-initialized').slick({
		prevArrow: $('#startpagesliderPrevButton'),
		nextArrow: $('#startpagesliderNextButton'),
		slidesToShow: 1,
		dots: true,
		autoplay: true,
		autoplaySpeed: 6000,
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


	//check slide count
	if ($('#ib-slider-container .slick-slide:not(.slick-cloned)').length <= 1) {
		$('#ib-slider-controls-toggle').css('display', 'none');
	}


	$('#ib-slider-controls-toggle').click(function (e) {

		if ($(this).hasClass('fa-play-circle')) {
			$('.ib-startpage-slider').slick('slickPlay')
			$(this).removeClass('fa-play-circle');
		} else {
			$('.ib-startpage-slider').slick('slickPause')
			$(this).addClass('fa-play-circle');
		}
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
