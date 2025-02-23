/**
 * the variables for this script are defined inside the <v:asset.script>
 * section inside Partials/Form/FrontendRenderForm.html
 */

// ------------------------------------
// SITESCRIPTS
// ------------------------------------
var myFormRenderer;
jQuery(document).ready(function () {

    if (typeof myFormData !== 'undefined') {
        // ---------------------
        // init form renderer
        // and custom frontend plugin
        // ---------------------
        var fbRenderer = $('#fb-render');

        // read data-assetbasepath value from #fb-render element
        var assetBasePath = jQuery(fbRenderer).data('assetbasepath');

        $(fbRenderer).formRender({
            formData: myFormData,
            dataType: 'json',
            render: true,
            i18n: {
                locale: 'de-DE',
                // add language files from i.e. https://formbuilder.online/assets/lang/de-DE.lang
                location: assetBasePath + 'JavaScript/'
                //extension: '.ext'
                //override: {
                //    'en-US': {...}
                //}
            }
        });

        myFormRenderer = jQuery(fbRenderer).myFormBuilderRenderer({
            ajaxSubmitUrl: formSubmitUri
        });

        // ---------------------
        // insert captcha element before the submit button
        // ---------------------
        if (jQuery('div.tx_ibformbuilder-show-frontend-form form button[type=submit]').length) {

            var formLang = jQuery('body').data('cblanguage');
            var recaptchaCode =
                '<!-- friendly captcha start -->\
                    <div class="row columns" id="ib-recaptcha-container">\
                        <p>' + contactform_dataPrivacyLink + ' </p>\
                        <div id="formBuilderFC" class="" data-callback="fcCallback" data-lang="' + formLang + '" data-puzzle-endpoint="https://eu-api.friendlycaptcha.eu/api/v1/puzzle" data-sitekey="FCMKN6E68LK32MRG">\
                        </div>\
                        <span class="form-error">' + contactform_recaptcha_error + '</span>\
                    </div>\
                <!-- friendly captcha end -->\n';
            jQuery(recaptchaCode)
                .insertBefore('div.tx_ibformbuilder-show-frontend-form form button[type=submit]');
            var element = document.querySelector("#formBuilderFC");
            var myCustomWidget = new friendlyChallenge.WidgetInstance(element, {})

        } else {
            alert("Dieses Formular benötigt einen Submit-Button");
        }
    }
});


// ------------------------------------
// RECAPTCHA STUFF
// ------------------------------------
var fcSolved = false;

function fcCallback(solution) {
    if ($('#ib-recaptcha-container .frc-container').hasClass('frc-success')) {
        fcSolved = true;
    }
}
