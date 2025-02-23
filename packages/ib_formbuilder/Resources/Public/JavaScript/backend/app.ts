import '../../Css/backend/app.scss'

import FormBuilderBackend from './components/FormBuilderBackend';
console.log('START');
document.querySelectorAll('form.tx_ibformbuilder-submit-external').forEach((formElement:Element) => {
    console.log(formElement);
    new FormBuilderBackend(formElement as HTMLFormElement);
});
