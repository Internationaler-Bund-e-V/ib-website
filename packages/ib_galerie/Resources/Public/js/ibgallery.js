$(document).ready(function () {

    var currentGallery;
    var mainStage;
    var navStage;
    var mainID = "#ext-ibg-main-";
    var navID = "#ext-ibg-nav-";
    var overlayContainer = $('#ext-ibg-overlay-container-0');
    var overlay = $('#ext-ibg-overlay-0');

    overlayContainer.appendTo('html');
    overlay.appendTo('html');

    $('.ext-ibg-image-item').on('click', function () {

        currentGallery = $(this).data('galleryid');
        mainStage = $(mainID + currentGallery);
        navStage = $(navID + currentGallery);
        gotoSlide = $(this).data('gotoslide');

        $("#ext-ibgc-" + currentGallery + " .dummyContainer").appendTo(overlay).show();

        overlayContainer.show();
        overlay.show();


        navStage.slick({
            asNavFor: mainID + currentGallery,
            focusOnSelect: true,
            dots: false,
            slidesToShow: 10,
            arrows: false,
            infinite: true,
            initialSlide: parseInt(gotoSlide),

        });

        mainStage.slick({
            prevArrow: $('#ext-ibg-nav-left-0'),
            nextArrow: $('#ext-ibg-nav-right-0'),
            asNavFor: navID + currentGallery,
            initialSlide: parseInt(gotoSlide),
            dots: true,
            infinite: true,
            rows: 0
        });





        $('#ext-ibg-close-0').unbind();
        $('#ext-ibg-close-0').on('click', function () {
            overlayContainer.hide();
            overlay.hide();
            mainStage.slick('unslick');
            navStage.slick('unslick');
            $('#ext-ibg-overlay-0 .dummyContainer').appendTo("#ext-ibgc-" + currentGallery);
        });



    });


    $('.ext-ib-slider-main-stage-mobile').slick({
        dots: true,
        slidesToShow: 1,
        arrows: false
    });


    $('.grid').masonry({
        // options
        percentPosition: true,
        itemSelector: '.grid-item'
    });

});