/**
* handle clicks on accordions for PDB pages and TYPO3 pages
*
*
* replacement of functions in
* packages/ibcontent/Resources/Public/js/db.js
*
* @see packages/ibcontent/Resources/Private/Templates/MyContent/Accordion.html
*
*/
import $ from 'jquery';
import Masonry from 'masonry-layout';

class IBAccordion
{
    constructor() {

        $('.ib-collapsible-trigger').on('click', function () {
            var collapsibleID = $(this).data('ibcollapsibleid');
            var content = $("#ib-collapsible-content-" + collapsibleID + " iframe");

            $("#ib-collapsible-content-" + collapsibleID).slideToggle(300);
            $("#ib-collapsible-" + collapsibleID + " i").toggleClass('ib-icon-arrow-right ib-icon-arrow-down');

            content.each((index:number, element:HTMLElement) => {
                const iframe = (element as HTMLIFrameElement);
                if (iframe.dataset.loaded != 'true') {

                    const src:string = iframe.src;
                    iframe.addEventListener('load', () => {
                        const flexContainer = $('#ib-collapsible-content-' + collapsibleID + ' .flex-video');
                        flexContainer.css('visibility', 'visible');
                    });
                    iframe.src = src;
                    iframe.dataset.loaded = 'true';
                }
            })
            /**
            * resize/reint slick slider/masonry if present
            */
            var tmpSlickContainer = $("#ib-collapsible-" + collapsibleID + " .ext-ib-slider-main-stage-mobile");
            tmpSlickContainer.slick('unslick').slick({
                dots: true,
                slidesToShow: 1,
                arrows: false
            });
            tmpSlickContainer.data('slickInitialized', 'open');

            let grids:NodeListOf<Element> = document.querySelectorAll('#ib-collapsible-' + collapsibleID + ' .grid');
            grids.forEach((gridElement:Element) => {
                (new Masonry(gridElement, {}) as any).layout();
            })

            /**
            * reint masonry layout/slickslider due to hidden state in collapsible
            */
            grids = document.querySelectorAll('#ib-collapsible-content-' + collapsibleID + ' .grid');
            grids.forEach((gridElement: Element) => {
                (new Masonry(gridElement, {}) as any).layout();
            })
            $('.ext-ib-slider-main-stage-mobile').slick('unslick').slick({
                dots: true,
                slidesToShow: 1,
                arrows: false
            });

        });

        if ($(location).attr('hash') != "") {
            let anchor:string = $(location).attr('hash')!;
            anchor = anchor.replace(/ /g, '');
            $(anchor).trigger('click');
            $("html, body").animate({
                scrollTop: $(anchor).offset()!.top - 100
            }, 1000);
        }
    }
}

export default IBAccordion;
