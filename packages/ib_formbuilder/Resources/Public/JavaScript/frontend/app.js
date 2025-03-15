import '../../Css/frontend/app.scss'

import $ from 'jquery';
global.jQuery = global.$ = $;

import 'formBuilder/dist/form-render.min.js';

/**
 * the variables for this script are defined inside the <v:asset.script>
 * section inside Partials/Form/FrontendRenderForm.html
 */

// ------------------------------------
// SITESCRIPTS
// ------------------------------------
var myFormRenderer;
$(() => {

    if (typeof myFormData !== 'undefined') {
        // ---------------------
        // init form renderer
        // and custom frontend plugin
        // ---------------------
        var fbRenderer = $('#fb-render');

        // read data-assetbasepath value from #fb-render element
        var assetBasePath = $(fbRenderer).data('assetbasepath');
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

        myFormRenderer = $(fbRenderer).myFormBuilderRenderer({
            ajaxSubmitUrl: formSubmitUri
        });

        // ---------------------
        // insert captcha element before the submit button
        // ---------------------
        if ($('div.tx_ibformbuilder-show-frontend-form form button[type=submit]').length) {

            var formLang = $('body').data('cblanguage');
            var recaptchaCode =
                '<!-- friendly captcha start -->\
                    <div class="row columns" id="ib-recaptcha-container">\
                        <p>' + contactform_dataPrivacyLink + ' </p>\
                        <div id="formBuilderFC" class="" data-callback="fcCallback" data-lang="' + formLang + '" data-puzzle-endpoint="https://eu-api.friendlycaptcha.eu/api/v1/puzzle" data-sitekey="FCMKN6E68LK32MRG">\
                        </div>\
                        <span class="form-error">' + contactform_recaptcha_error + '</span>\
                    </div>\
                <!-- friendly captcha end -->\n';
            $(recaptchaCode)
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


/**
 *
 * this plugin handles the formbuilder frontend
 *
 * @usage: $('#container').myFormBuilderRenderer({ajaxSubmitUrl: formSubmitUri});
 */
(function ($) {

    /**
     * global plugin options
     */
    var defaults = {
        captchaSuccess: false,
        grecaptcha: null,
        ajaxSubmitUrl: ""
    };
    var fcSolved = false;


    /**
     * ------------------------
     * INIT the plugin
     * ------------------------
     * @returns {jQuery}
     */
    $.fn.myFormBuilderRenderer = function (options) {

        $.extend(defaults, options);
        //console.log('MyFormBuilderRenderer init', defaults);

        this.generalSetup();
        this.manageRadioButtons();
        this.handleFormSubmit();
        return this;
    };

    /**
     *
     * @param valueArray
     * @param activeValue
     * @returns {Array}
     */
    $.fn.parseLocalValue = function (valueArray, activeValue) {

        var resultArray = [];
        var showItems = [];
        var hideItems = [];

        var split_1 = valueArray.split(";");
        $(split_1).each(function (key, value) {
            var split_2 = value.split('#');
            if (split_2[0] === activeValue) {
                showItems.push('div.field-' + split_2[1]);
            } else {
                hideItems.push('div.field-' + split_2[1]);
            }
        });

        resultArray['show'] = showItems;
        resultArray['hide'] = hideItems;
        return resultArray
    };

    /**
     * perform general setup tasks (assing classes, ...)
     */
    $.fn.generalSetup = function () {
        var formContainer = $('form#tx_ibformbuilder_contactform');
        $(formContainer).find("button[type=submit]").addClass('button');
    };

    /**
     * ---------------------------------
     * handle click on radio buttons
     * ---------------------------------
     * check on each click if it is a control plugin
     * and if it hides / shows related form elements
     */
    $.fn.manageRadioButtons = function () {

        var that = this;
        var radios = $(this).find('div.radio-group div.radio input');

        $(radios).each(function (key, value) {

            // remove preselections
            $(value).removeAttr('checked');

            // check if radio is managed by dependecies
            // if so, hide it initially
            var localValue = $(value).data('local-value');
            if (undefined !== localValue) {
                var parsed = that.parseLocalValue(localValue, '');
                $(parsed['hide']).each(function (key, value) {
                    $(value).hide();
                });
            }
        });

        // check for each radio if it is a trigger for managing
        // dependencies and add event handling if so
        $(radios).click(function (e) {
            var target = e.currentTarget;
            var myData = $(target).data('local-value');

            if (undefined !== myData) {
                var activeValue = $(target).val();
                var parsed = that.parseLocalValue(myData, activeValue);

                $(parsed['hide']).each(function (key, value) {
                    $(value).find('input').prop('checked', false);
                    $(value).hide();
                });

                $(parsed['show']).each(function (key, value) {
                    $(value).show();
                });
            }
        });
    };

    $.fn.showCaptchaError = function (show) {

        var contactForm = $("#tx_ibformbuilder_contactform");

        if (!show) {
            $(contactForm).find('div.captcha_error').hide();
        } else {
            $(contactForm).find('div.captcha_error').show();
        }
    };

    /**
     * ---------------------------------
     * handle form submit
     * ---------------------------------
     */
    $.fn.handleFormSubmit = function () {
        var that = this;
        var loader = $('div.loader-container');
        var general_error_message = $('div.general_error_message');

        var submitButton = $("form#tx_ibformbuilder_contactform button[type=submit]");
        $(submitButton).click(function (e) {

            e.preventDefault();

            // -----------------------------------------------------------------------------
            // get the formdata as array
            // and add make the name extbase compatible by renaming
            // name="somename" to name="tx_ibformbuilder_showform[somename]"
            // otherwise the variables can't be acessed via $this->request->getArguments())
            // in the extbase controller
            // ------------------------------------------------------------------------------
            var contactForm = $("#tx_ibformbuilder_contactform");

            // ------------------------------------------
            // change checkbox names form [] to [1], [2], ...
            // ------------------------------------------
            $(contactForm).find('div.checkbox-group').each(function (key, value) {
                $(value).find('input[type=checkbox]').each(function (key2, value2) {
                    var elementId = $(value2).attr('id');
                    var elementName = $(value2).attr('name');
                    $('#' + elementId).attr('name', elementId);
                })
            });

            // ------------------------------------------
            // serialize array and prepare it for senind to server
            // ------------------------------------------
            var serializedArrayFormData = $(contactForm).serializeArray();
            for (var i = 0; i < serializedArrayFormData.length; i++) {
                serializedArrayFormData[i]['name'] = "tx_ibformbuilder_showform[formdata][" + serializedArrayFormData[i].name + "]";
            }

            if ($('#ib-recaptcha-container .frc-container').hasClass('frc-success')) {
                fcSolved = true;
            }

            // ----------------------------------
            // only submit if captcha was solved
            // ----------------------------------
            if (fcSolved) {

                $(submitButton).hide();
                $(loader).show();
                $(general_error_message).hide();

                // create serialized string of the parameters and send it via ajax to the extbase controller
                var serializedyFormData = jQuery.param(serializedArrayFormData);
                jQuery.ajax({
                    url: defaults.ajaxSubmitUrl,
                    type: "POST",
                    data: serializedyFormData,
                    dataType: 'json',
                    success: function (response) {

                        that.showCaptchaError(false);
                        $(contactForm)
                            .find(".error")
                            .removeClass('error');

                        // SUCCESS
                        if (response['success'] === true) {

                            $('div.tx_ibformbuilder-show-frontend-form').hide();
                            $('div.tx_ibformbuilder-show-frontend-form-success').show();

                            //if custom form success callback implemented (snippets tmpl), call
                            if (typeof formBuilderSuccessCallback === "function") {
                                formBuilderSuccessCallback(submitButton);
                            }

                            // if form was submitted successfully, but
                            // there where possible errors on sending the email,
                            // show a hint to visitors
                            // mk@rms, 2022-05-18
                            if (response['mail_send_success'] === false) {
                                $('#tx_formbuilder_mailsend_error').show();
                            }
                        }
                        // ERROR
                        else {

                            $(general_error_message).show();
                            //console.log('error', response.errors);
                            jQuery.each(response.errors, function (index, value) {

                                $(contactForm)
                                    .find("#" + index + ',label[for="' + index + '"]')
                                    .addClass('error');

                                if (index === 'captcha') {
                                    that.showCaptchaError(true);
                                }
                            });
                        }

                        $(submitButton).show();
                        $(loader).hide();
                    },
                    error: function (error) {
                    }
                });
            } else {
                $('#ib-recaptcha-container .form-error').fadeIn(100).fadeOut(5000);
            }

        })
    };

}($));


