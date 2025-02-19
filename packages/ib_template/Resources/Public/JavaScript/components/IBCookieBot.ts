/**
* check for get parameter ibAnchor for scrolling and opening contentelements
* usage: ?ibAnchor = cXXX or XXX
* c -> content element id
* XXX -> accordion element id
*/

import $ from 'jquery';

class IBCookieBot {
    protected cbIframeData;
    protected cookiebotLanguage:string = '';

    constructor() {
        const EXT_ibTemplateAssets = document.getElementById('ib-container')?.dataset.ibtemplatefolder;
        const jsonPath = EXT_ibTemplateAssets + 'lang/customCookiebot/';
        if (typeof window.Cookiebot != "undefined") {

            this.cookiebotLanguage = $('body').data('cblanguage');

            $.getJSON(jsonPath + this.cookiebotLanguage + ".json", () => {}).done((languagePackJSON) => {
                const data = document.querySelectorAll(".video-responsive iframe");
                for (var i = 0; i < data.length; i++) {
                    var str = $(data[i]).data('src');
                    var cbBlockSrc = $(data[i]).data('cookieblock-src');
                    var strSrc = $(data[i]).attr('src');
                    if ((typeof str !== "undefined" && str.toLowerCase().indexOf('youtube') >= 0) || (typeof strSrc !== "undefined" && strSrc.toLowerCase().indexOf('youtube') >= 0) || (typeof cbBlockSrc !== "undefined" && cbBlockSrc.toLowerCase().indexOf('youtube') >= 0)) {
                        var ytContainer:JQuery<Element> = $(data[i]).closest('.video-responsive');
                        if (ytContainer.length == 0) {
                            ytContainer = $(data[i]);
                        } else {
                            $(ytContainer).addClass('cookieconsent-optin-marketing rs_preserve');
                        }
                        $(languagePackJSON.videoTextConsent).insertBefore(ytContainer);
                    }
                }
                this.checkVideoState();
                this.addRStoCB();
                this.updateDataPrivacyLink();
            });

            this.cbIframeData = document.querySelectorAll(".cb-script-container iframe");
            for (var i = 0; i < this.cbIframeData.length; i++) {
                const str = $(this.cbIframeData[i]).data('src');
                const cbCookieconsent = $(this.cbIframeData[i]).data('cookieconsent');
                const cbCategoryname = $(this.cbIframeData[i]).data('cbcategoryname');
                const cboverlayclass = $(this.cbIframeData[i]).data('cboverlayclass');
                const cbText = $(this.cbIframeData[i]).data('cbtext');

                let jsonUrl = jsonPath + cbText + '_';

                if (typeof cbText !== 'undefined' && cbText.length > 0) {
                    console.log(jsonUrl);
                }


                if ((typeof str !== 'undefined' && str.length > 0)) {
                    let scriptContainer:any = $(this.cbIframeData[i]).closest('.cb-script-container');
                    if (scriptContainer.length == 0) {
                        scriptContainer = this.cbIframeData[i];
                    } else {
                        $(scriptContainer).addClass('cookieconsent-optin-' + cbCookieconsent + ' rs_preserve');
                    }
                    $.getJSON(jsonUrl + this.cookiebotLanguage + ".json", function () { }).done((languagePackJSON) => {
                        $('<div class="cookieconsent-optout-' + cbCookieconsent + ' rs_preserve"><div>' + languagePackJSON.text1 + '<a href="/' + languagePackJSON.dataPrivacyPage + '">' + languagePackJSON.text2 + '</a></div> <div class="customCBOverlay ' + cboverlayclass + ' aria-hidden="true"><div class="customCBButton" aria-hidden="true"><button onclick="Cookiebot.renew();">' + languagePackJSON.overlayButtonText + cbCategoryname + '</button></div></div></div>').insertBefore(scriptContainer);
                    });
                }
            }
            this.checkVideoState();
            this.updateDataPrivacyLink();
        }
        // only allow reload of page after settings are changed inside the Dialog
        // otherwise this will result in an endles loop because
        // CookiebotCallback_OnAccept() is also called on every page load
        // author mk@rms, 2020-05-26
        var CB_allow_page_reload = false;

        if (typeof window.Cookiebot != "undefined" && !window.Cookiebot.consent.statistics) {
            CB_allow_page_reload = true;
        }
    }

    addRStoCB() {
        //add readspeaker class to cookie banner  initially
        $('#CybotCookiebotDialogBodyContent').addClass('ib-rs-content');
        $('#CybotCookiebotDialogBodyLevelButtonLevelOptinAllowallSelectionWrapper').addClass('rs_preserve');
        $('#CybotCookiebotDialogBodyLevelDetailsWrapper').addClass('rs_preserve');
    }

    removeRSfromCB() {
        $('#CybotCookiebotDialogBodyContent').removeClass('ib-rs-content');
    }

    /**
    * update consent overlay dataprivacy link
    */
    updateDataPrivacyLink() {
        var dataprivacyLink = $('body').data('dataprivacyurl');
        $(".cbOverlayDPLink").each(function (index) {
            $(this).attr('href', dataprivacyLink);
        });

    }

    //show/hide on video state, additional check since accordion visibility
    checkVideoState() {

        if (window.Cookiebot.consent.marketing) {
            $('.video-responsive.cookieconsent-optin-marketing').css('display', 'block');
            $('.cb-script-container.cookieconsent-optin-marketing').css('display', 'block');
        }
        else {
            $('.cb-script-container.cookieconsent-optin-marketing').hide();
            $('.video-responsive.cookieconsent-optin-marketing').hide();
        }
        if (window.Cookiebot.consent.preferences) {
            $('.cb-script-container .cookieconsent-optin-preferences').css('display', 'block');
        }
        else {
            $('.cb-script-container .cookieconsent-optin-preferences').hide();
        }
    }


    //add readspeaker class to cookie banner on renew/change
    CookiebotCallback_OnDialogDisplay() {
        this.addRStoCB();
    }

    //remove readspeaker class from cookie banner on renew/change
    CookiebotCallback_OnAccept() {

        this.removeRSfromCB();
        this.checkVideoState();

    }
}

export default IBCookieBot;
