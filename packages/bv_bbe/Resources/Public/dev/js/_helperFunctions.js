// Function return boolean value if element is scrolled into view
export function isScrolledIntoView(elem) {
  var docViewTop = $(window)
    .scrollTop();
  var docViewBottom = docViewTop + $(window)
    .height();

  var elemTop = $(elem)
    .offset().top;
  var elemBottom = elemTop + $(elem)
    .height();

  return ((elemTop >= docViewTop) && (elemTop <= docViewBottom));
}