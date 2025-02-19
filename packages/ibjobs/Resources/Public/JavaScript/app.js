import '../Css/app.scss';

import IBJobSearch from './components/IBJobSearch';

$(() => {
    if (document.getElementById('ib-jobs-data')) {
        new IBJobSearch(document.getElementById('ib-jobs-data'));
    }
});

