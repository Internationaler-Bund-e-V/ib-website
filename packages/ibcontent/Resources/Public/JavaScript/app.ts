// import '../Css/app.scss';

import $ from 'jquery';

import IBDbProductList from './components/IBDbProductList';
import IBContentSlider from './components/IBContentSlider';
import IBBubbleSlider from './components/IBBubbleSlider';
import IBJobsSlider from './components/IBJobsSlider';
import IBStartPageSlider from './components/IBStartPageSlider';

//require('./components/db.js');

$(() => {
    document.querySelectorAll('.ib-dbproductlist').forEach((element: Element) => {
        new IBDbProductList(element);
    });

    document.querySelectorAll('.ib-contentslider').forEach((element: Element) => {
        new IBContentSlider(element);
    });

    document.querySelectorAll('.ib-bubbleslider').forEach((element: Element) => {
        new IBBubbleSlider(element);
    });

    document.querySelectorAll('.ib-startpageslider').forEach((element: Element) => {
        new IBStartPageSlider(element);
    });

    document.querySelectorAll('.startPage .ib-jobsslider').forEach((element: Element) => {
        new IBJobsSlider(element, { slidesToShow: 3 });
    });

    document.querySelectorAll('.twoColLayout .ib-jobsslider').forEach((element: Element) => {
        new IBJobsSlider(element, { slidesToShow: 2 });
    });
});
