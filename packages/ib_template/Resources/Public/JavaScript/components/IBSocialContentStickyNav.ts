import $ from 'jquery';

class IBSocialContentStickyNav
{

    protected stickyoffset:number = 120;
    protected stickydifference:number = 0;
    protected stickytop:number = 0;

    /**
    * function to calculate offsets
    */
    constructor() {
        $(window).on('resize', () => {
            this.stickyvalues();
        });
        if ($('#ib-partial-startpageslider').find('.ib-slider-container').length != 0) {
            $('.ib-startpage-slider').on('init', (event, slick, currentSlide, nextSlide) => {
                setTimeout(() => {

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
                    this.stickyvalues();
                    var subject = $(event.currentTarget);
                    this.handleScrolling(subject, this.stickyoffset, this.stickydifference, this.stickytop);
                }, 500);
            });
        } else {

            // this is for all pages that have no slider in the head
            // here we also need a scroll to fixed behaviour
            $('.ib-social-content').show();


            // if there is absolutely no image in the header
            if ($('div#ib-partial-startpageslider').children('img').length == 0) {
                $('.ib-social').addClass('fixed-no-slider-no-image');
                this.stickyvalues();
                this.stickytop = 45;
                this.stickyoffset = 50;
            }
            // if there is no slider but i.e. a youtube video
            else {
                $('.ib-social').addClass('fixed-no-slider');
                this.stickyvalues();
            }

            this.handleScrolling($(document), this.stickyoffset, this.stickydifference, this.stickytop);
        }
    }

    stickyvalues() {
        var ibSocial = $('.ib-social');
        var ibSocialContent = $('.ib-social-content');

        if ($(ibSocial).length) {
            this.stickydifference = $(ibSocial).offset()!.top - $(ibSocialContent).offset()!.top;
            this.stickytop = $(ibSocialContent).offset()!.top - this.stickyoffset;
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
    handleScrolling(subject:any, stickyoffset:number, stickydifference:number, stickytop:number) {
        $(window).on('scroll', (event) => {

            var ibSocial = $('.ib-social');
            var y = $(subject).scrollTop()!;
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


    // -----------------------------------------------
    // init function after slick init if header slider
    // present else add class fixed to social
    // -----------------------------------------------
}

export default IBSocialContentStickyNav;
