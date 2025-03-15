import $ from 'jquery';
import Foundation from 'foundation-sites';

class IBContactForm
{
    protected uid:string = '';
    protected ibDecodeUri:string = '';
    protected doSubmit:boolean = false;
    protected formUrl:string = '';

    constructor(element:HTMLElement|JQuery<HTMLElement>|Element) {
        this.uid = $(element).data('uid');
        this.ibDecodeUri = $(element).data('ibdecodeuri');
        this.formUrl = decodeURI(this.ibDecodeUri).replace(/&amp;/g, '&');
        const formElement: HTMLFormElement = (document.getElementById('rms_contactForm-' + this.uid) as HTMLFormElement);
        const mainSelector = 'div#contact-form-' + this.uid;

        let abide = new Foundation.Abide($(formElement as HTMLElement));

        // submit handler
        $(formElement).on('submit', (e) => {
            e.preventDefault();
        });
        $(formElement).bind('formvalid.zf.abide', () => {
            if ($('#ib-recaptcha-container .frc-container').hasClass('frc-success')) {
                this.doSubmit = true;
            }
            if (this.doSubmit) {
                // show loader, hide submit button -> prevent double submits
                $(mainSelector + ' form div.ajax-loader').show();
                $(mainSelector + ' form div.submit-button').hide();
                $.ajax({
                    type: "POST",
                    url: this.formUrl,
                    data: $(mainSelector + " #rms_contactForm-" + this.uid)
                        .serialize(), // serializes the form's elements.
                    success: (data) => {
                        var json = JSON.parse(data);

                        $(mainSelector + ' form label.captcha-label').removeClass('is-invalid-label');
                        $(mainSelector + " form input[name='captcha']").removeClass('is-invalid-input');
                        $(mainSelector + ' form div.salutation-block label').removeClass('is-invalid-label');
                        if (json.errors.captcha) {
                            $(mainSelector + ' form label.captcha-label').addClass('is-invalid-label');
                            $(mainSelector + " form input[name='captcha']").addClass('is-invalid-input');
                        }
                        if (json.errors.salutation) {
                            $(mainSelector + ' form div.salutation-block label').addClass('is-invalid-label');
                        }

                        $(mainSelector + ' form div.ajax-loader').hide();
                        $(mainSelector + ' form div.submit-button').show();

                        // if there are no errors, hide form
                        if (json.errors.length === 0) {
                            formElement!.reset();
                            $(formElement!).hide();
                            $(formElement!).addClass('rs_skip');
                            $(mainSelector + ' div.callout.success').removeClass('rs_skip');
                            $(mainSelector + ' div.callout.success').show();
                        }
                    }
                });
            } else {
                $('#ib-recaptcha-container .form-error').fadeIn(100).fadeOut(5000);
            }
        });
    }
}

export default IBContactForm;
