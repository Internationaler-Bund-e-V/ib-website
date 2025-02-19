'use strict'

import '../Css/app.scss';

import SuggestController from './components/SuggestController';
import OptionFacetController from './components/OptionFacetController';

$(() => {
    const optionsController = new OptionFacetController();
    const init = () => {
        $('form[data-suggest]').each((index:number, element:HTMLElement) => {
            new SuggestController(element);
        });
        optionsController.init();
    };

    init();

    $('body').on('tx_solr_updated', () => {
        init();
    });
});

