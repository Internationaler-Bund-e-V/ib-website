'use strict'

// any CSS you import will output into a single css file (app.css in this case)
import '../Css/app.scss';

import IBGallery from './components/IBGallery';

window.addEventListener('load', (event) => {
    if (document.querySelectorAll('.ext-ibg-image-item').length > 0) {
        new IBGallery(document.querySelectorAll('.ext-ibg-image-item'));
    }
});
