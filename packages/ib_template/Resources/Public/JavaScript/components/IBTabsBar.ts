import $ from 'jquery';

class IBTabsBar
{
    constructor() {
        let tabsWidth = 0;

        $('.ib-tabs-bar-ul li').each((index:number, element:any) => {
            tabsWidth += $(element).innerWidth()!;
        });

        /**
        * tabs bar right
        */

        $('.ib-tabs-bar-right .ib-tabs-bar-ul').css('width', tabsWidth+15);
        $('.ib-tabs-bar-right .ib-tabs-bar-ul').css('top', tabsWidth) ;
        /**
        * tabs bar left
        */

        $('.ib-tabs-bar-left .ib-tabs-bar-ul').css('width', tabsWidth+15);
        $('.ib-tabs-bar-left .ib-tabs-bar-ul').css('top', tabsWidth);

        $('#ib-tabs-bar').css('visibility', 'visible');


        $(".ib-tabs-bar-item").on('click', (event) => {
            const currentElement:JQuery<HTMLElement> = $(event.currentTarget);
            const cID:string = (currentElement.data('cid') as string);

            $('.ib-tabs-bar-item').removeClass('ib-tabs-bar-item-active');
            currentElement.addClass('ib-tabs-bar-item-active');
            $("#ib-tabs-bar").css('background-color', currentElement.css('background-color'));

            if (!$('#ib-tabs-bar').hasClass('ib-tabs-content-open')) {
                $('#ib-tabs-bar').toggleClass('ib-tabs-content-open');
            }

            $('.ib-tabs-bar-item-content').hide();
            $('#itbic-'+cID).show();
        });

        $('#itbic-close-button i').on('click', () => {
            $('.ib-tabs-bar-item').removeClass('ib-tabs-bar-item-active');
            $('#ib-tabs-bar').toggleClass('ib-tabs-content-open');
        });
    }
}

export default IBTabsBar;
