'use strict'

// any CSS you import will output into a single css file (app.css in this case)
import '../Css/ib_gallery.scss';

import IBGallery from './components/IBGallery';

window.addEventListener('load', (event) => {
    new IBGallery();
});
