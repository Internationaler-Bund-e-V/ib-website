function addRStoCB() {
  //add readspeaker class to cookie banner  initially
  $('#CybotCookiebotDialogBodyContent').addClass('ib-rs-content');
  $('#CybotCookiebotDialogBodyLevelButtonLevelOptinAllowallSelectionWrapper').addClass('rs_preserve');
  $('#CybotCookiebotDialogBodyLevelDetailsWrapper').addClass('rs_preserve');
}

function removeRSfromCB() {
  $('#CybotCookiebotDialogBodyContent').removeClass('ib-rs-content');
}

//show/hide on video state, additional check since accordion visibility
function checkVideoState() {

  if (Cookiebot.consent.statistics) {
    $('.video-responsive.cookieconsent-optin-statistics').css('display', 'block');
  } else {
    $('.video-responsive.cookieconsent-optin-statistics').hide();
  }
  if (Cookiebot.consent.marketing) {
    $('.cb-script-container.cookieconsent-optin-marketing').css('display', 'block');
  }
  else {
    $('.cb-script-container.cookieconsent-optin-marketing').hide();
  }
  if (Cookiebot.consent.preferences) {
    $('.cb-script-container .cookieconsent-optin-preferences').css('display', 'block');
  }
  else {
    $('.cb-script-container .cookieconsent-optin-preferences').hide();
  }
}

$(document).ready(function () {

  var publicURL = $('#SrbMainContent').data('publicurl');
  //check if cookiebot is available , Youtube
  if (typeof Cookiebot != "undefined") {
    var cookiebotLanguage = $('body').data('cblanguage');
    $.getJSON(publicURL + "/lang/customCookiebot/" + cookiebotLanguage + ".json", function () { }).done(function (languagePackJSON) {
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
    });

  }

  //check generic iframes
  //check if cookiebot is available
  if (typeof Cookiebot != "undefined") {
    var cookiebotLanguage = $('body').data('cblanguage');
    $.getJSON(publicURL + "lang/customCookiebot/" + cookiebotLanguage + ".json", function () { }).done(function (languagePackJSON) {
      var data = document.querySelectorAll(".cb-script-container iframe");

      for (var i = 0; i < data.length; i++) {
        var str = $(data[i]).data('src');
        var cbCookieconsent = $(data[i]).data('cookieconsent');
        var cbCategoryname = $(data[i]).data('cbcategoryname');
        var cboverlayclass = $(data[i]).data('cboverlayclass');
        if ((typeof str !== 'undefined' && str.length > 0)) {
          var scriptContainer = $(data[i]).closest('.cb-script-container');
          if (scriptContainer.length == 0) {
            scriptContainer = data[i];
          } else {
            $(scriptContainer).addClass('cookieconsent-optin-' + cbCookieconsent + ' rs_preserve');
          }
          $('<div class="cookieconsent-optout-' + cbCookieconsent + ' rs_preserve"><div>' + languagePackJSON.text1 + '<a href="/' + languagePackJSON.dataPrivacyPage + '">' + languagePackJSON.text2 + '</a></div> <div class="customCBOverlay ' + cboverlayclass + ' aria-hidden="true"><div class="customCBButton" aria-hidden="true"><button onclick="Cookiebot.renew();">' + languagePackJSON.overlayButtonText + cbCategoryname + '</button></div></div></div>').insertBefore(scriptContainer);
        }
      }
      checkVideoState();
    });
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
