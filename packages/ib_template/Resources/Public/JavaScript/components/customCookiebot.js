function addRStoCB() {
  //add readspeaker class to cookie banner  initially
  $('#CybotCookiebotDialogBodyContent').addClass('ib-rs-content');
  $('#CybotCookiebotDialogBodyLevelButtonLevelOptinAllowallSelectionWrapper').addClass('rs_preserve');
  $('#CybotCookiebotDialogBodyLevelDetailsWrapper').addClass('rs_preserve');
}

function removeRSfromCB() {
  $('#CybotCookiebotDialogBodyContent').removeClass('ib-rs-content');
}

/**
 * update consent overlay dataprivacy link
 */
function updateDataPrivacyLink() {
  var dataprivacyLink = $('body').data('dataprivacyurl');
  $(".cbOverlayDPLink").each(function (index) {
    $(this).attr('href', dataprivacyLink);
  });

}

//show/hide on video state, additional check since accordion visibility
function checkVideoState() {

  if (Cookiebot.consent.marketing) {
    $('.video-responsive.cookieconsent-optin-marketing').css('display', 'block');
    $('.cb-script-container.cookieconsent-optin-marketing').css('display', 'block');
  }
  else {
    $('.cb-script-container.cookieconsent-optin-marketing').hide();
    $('.video-responsive.cookieconsent-optin-marketing').hide();
  }
  if (Cookiebot.consent.preferences) {
    $('.cb-script-container .cookieconsent-optin-preferences').css('display', 'block');
  }
  else {
    $('.cb-script-container .cookieconsent-optin-preferences').hide();
  }
}

$(document).ready(function () {
  var cbIframeData;

  //check if cookiebot is available , Youtube
  if (typeof Cookiebot != "undefined") {
    var cookiebotLanguage = $('body').data('cblanguage');
    $.getJSON("/typo3conf/ext/ib_template/Resources/Public/lang/customCookiebot/" + cookiebotLanguage + ".json", function () { }).done(function (languagePackJSON) {
      var data = document.querySelectorAll(".video-responsive iframe");
      for (var i = 0; i < data.length; i++) {
        var str = $(data[i]).data('src');
        var cbBlockSrc = $(data[i]).data('cookieblock-src');
        var strSrc = $(data[i]).attr('src');
        if ((typeof str !== "undefined" && str.toLowerCase().indexOf('youtube') >= 0) || (typeof strSrc !== "undefined" && strSrc.toLowerCase().indexOf('youtube') >= 0) || (typeof cbBlockSrc !== "undefined" && cbBlockSrc.toLowerCase().indexOf('youtube') >= 0)) {
          var ytContainer = $(data[i]).closest('.video-responsive');
          if (ytContainer.length == 0) {
            ytContainer = data[i];
          } else {
            $(ytContainer).addClass('cookieconsent-optin-marketing rs_preserve');
          }
          $(languagePackJSON.videoTextConsent).insertBefore(ytContainer);
        }
      }
      checkVideoState();
      addRStoCB();
      updateDataPrivacyLink();
    });

  }

  //check generic iframes
  //check if cookiebot is available
  if (typeof Cookiebot != "undefined") {
    var cookiebotLanguage = $('body').data('cblanguage');
    var jsonPath = "/typo3conf/ext/ib_template/Resources/Public/lang/customCookiebot/";
    this.cbIframeData = document.querySelectorAll(".cb-script-container iframe");
    for (var i = 0; i < this.cbIframeData.length; i++) {
      var str = $(this.cbIframeData[i]).data('src');
      var cbCookieconsent = $(this.cbIframeData[i]).data('cookieconsent');
      var cbCategoryname = $(this.cbIframeData[i]).data('cbcategoryname');
      var cboverlayclass = $(this.cbIframeData[i]).data('cboverlayclass');
      var cbText = $(this.cbIframeData[i]).data('cbtext');
      if (typeof cbText !== 'undefined' && cbText.length > 0) {
        jsonPath = jsonPath + cbText + '_';
        console.log(jsonPath);
      }


      if ((typeof str !== 'undefined' && str.length > 0)) {
        var scriptContainer = $(this.cbIframeData[i]).closest('.cb-script-container');
        if (scriptContainer.length == 0) {
          scriptContainer = this.cbIframeData[i];
        } else {
          $(scriptContainer).addClass('cookieconsent-optin-' + cbCookieconsent + ' rs_preserve');
        }
        $.getJSON(jsonPath + cookiebotLanguage + ".json", function () { }).done(function (languagePackJSON) {
          $('<div class="cookieconsent-optout-' + cbCookieconsent + ' rs_preserve"><div>' + languagePackJSON.text1 + '<a href="/' + languagePackJSON.dataPrivacyPage + '">' + languagePackJSON.text2 + '</a></div> <div class="customCBOverlay ' + cboverlayclass + ' aria-hidden="true"><div class="customCBButton" aria-hidden="true"><button onclick="Cookiebot.renew();">' + languagePackJSON.overlayButtonText + cbCategoryname + '</button></div></div></div>').insertBefore(scriptContainer);
        });
      }



    }
    checkVideoState();
    updateDataPrivacyLink();

  }

})


// only allow reload of page after settings are changed inside the Dialog
// otherwise this will result in an endles loop because
// CookiebotCallback_OnAccept() is also called on every page load
// author mk@rms, 2020-05-26
var CB_allow_page_reload = false;

if (typeof Cookiebot != "undefined" && !Cookiebot.consent.statistics) {
  CB_allow_page_reload = true;
}

//add readspeaker class to cookie banner on renew/change
function CookiebotCallback_OnDialogDisplay() {
  addRStoCB();
}

//remove readspeaker class from cookie banner on renew/change
function CookiebotCallback_OnAccept() {

  removeRSfromCB();
  checkVideoState();

}
