$(document).ready(function () {
  console.log("contactOverlay loaded...");
  $('.toggleContactOverlay').on('click', function () {
    $('#dbContactOverlay').toggle();
  })
});