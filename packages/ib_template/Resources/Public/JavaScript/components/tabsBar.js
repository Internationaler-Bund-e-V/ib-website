import $ from 'jquery';

$(function () {
  var tabsWidth = 0;
  $('.ib-tabs-bar-ul li').each(function(){
    tabsWidth += $(this).innerWidth();
  });

  /**
   * tabs bar right
   */

  $('.ib-tabs-bar-right .ib-tabs-bar-ul').css('width',tabsWidth+15);
  $('.ib-tabs-bar-right .ib-tabs-bar-ul').css('top',tabsWidth) ;
  /**
   * tabs bar left
   */

  $('.ib-tabs-bar-left .ib-tabs-bar-ul').css('width',tabsWidth+15);
  $('.ib-tabs-bar-left .ib-tabs-bar-ul').css('top',tabsWidth);

  $('#ib-tabs-bar').css('visibility','visible');


    $(".ib-tabs-bar-item").on('click', function () {
        var cID = $(this).data('cid');
        $('.ib-tabs-bar-item').removeClass('ib-tabs-bar-item-active');
        $(this).addClass('ib-tabs-bar-item-active');
        $("#ib-tabs-bar").css('background-color',$(this).css('background-color'));
        if (!$('#ib-tabs-bar').hasClass('ib-tabs-content-open')) {
            $('#ib-tabs-bar').toggleClass('ib-tabs-content-open');
        }
        $('.ib-tabs-bar-item-content').hide();
        $('#itbic-'+cID).show();
    });

    $('#itbic-close-button i').on('click',function(){
        $('.ib-tabs-bar-item').removeClass('ib-tabs-bar-item-active');
        $('#ib-tabs-bar').toggleClass('ib-tabs-content-open');
    });
})
