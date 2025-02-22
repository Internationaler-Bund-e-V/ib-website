/* ============================ */
/* IMAGE TO BACKGROUND */

/* ============================ */
function changeImgToBg(imgSel, parentSel) {
  console.log("imgtobg");
  if (!imgSel) {
    console.info('no img selector');
    return false;
  }

  let $parent,
    _this;

  $(imgSel)
    .each(function () {
      _this = $(this);
      if ('none' == _this.css('display')) {
        return true;
      }

      $parent = _this.closest(parentSel);
      $parent = $parent.length ? $parent : _this.parent();
      $parent.css('background-image', 'url(' + _this.attr('src') + ')');
      _this.hide();
    });
}
export function initChangeImgToBg(imgSel, parentSel) {
  changeImgToBg(imgSel, parentSel);
}