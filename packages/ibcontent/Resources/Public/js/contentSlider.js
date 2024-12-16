$(document).ready(function() {
	$('.ib-contentslider-slider').each(function(){
		var sliderID = $(this).data('sliderid');
		$(this).slick({
			prevArrow : $("#contentsliderPrevButton-"+sliderID),
			nextArrow : $("#contentsliderNextButton-"+sliderID),
			slidesToShow : 1,
			dots: true,
			responsive : [
			{
				breakpoint : 900,
				settings : {
					slidesToShow : 1,
					slidesToScroll : 1
				}
			}, {
				breakpoint : 480,
				settings : {
					slidesToShow : 1,
					slidesToScroll : 1
				}
			}]
		});
	});
}); 