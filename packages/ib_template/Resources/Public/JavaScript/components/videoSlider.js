import $ from 'jquery';
import 'slick-carousel';

//video slider
var ibVideoSlider = $('#ibVideoSlider');
ibVideoSlider.slick({
  dots: true,
  slidesToShow: 1,
  appendDots: $('#ibVideoSliderDots'),
  prevArrow: $('#ibVideoSliderPevious'),
  nextArrow: $('#ibVideoSliderNext')
});
