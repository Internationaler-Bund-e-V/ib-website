// use jquery.appear to detect which 1st-level-link in jetmenu is active
$( document ).ready(function() {
  // initialize appear on megamenus

	// apply class 'submenu--open' when a megamenu appears
  $('.megamenu').on('appear', function(event, $all_appeared_elements) {
    $(this).closest('li').addClass('submenu--open');
  });
  // remove class 'submenu--open' when a megamenu appears
  $('.megamenu').on('disappear', function(event, $all_disappeared_elements) {
    $(this).closest('li').removeClass('submenu--open');
  });  
}); 
