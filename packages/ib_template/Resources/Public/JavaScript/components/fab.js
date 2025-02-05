import $ from 'jquery';

$(function() {

  var fab = $('#ibFAB');
  var fabButton = $('#navButtonIconContainer');

  fabButton.on('click', function () {
    fab.toggleClass('fabOpen');
  })

  $('.fabItem').on('click', function () {
    var fabContainerID = $(this).data('cid');
    $('#fabIC-' + fabContainerID).toggle();
  })

});
