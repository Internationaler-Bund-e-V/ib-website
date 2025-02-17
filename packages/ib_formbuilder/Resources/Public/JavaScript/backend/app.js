import $ from 'jquery';
import 'formBuilder';

import '../../Css/backend/app.scss'


document.querySelectorAll('form.tx_ibformbuilder-submit-external').forEach((formElement) => {
    new myFormBuilderBackend(formElement);
});
