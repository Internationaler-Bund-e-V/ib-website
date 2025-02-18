import '../Css/app.scss';

var jobs;
var jobContainer;
var results;
var jobTemplate;
var jobtype = "IB";
var srJobTemplate;
var trfTemplate = "";
var $window = $(window);
var lazyOffset = 0;
var currentJob = 0;
var currentResults = 0;
var lazyMarker;
var alternateClass;
var resultCounter;
var regex;
var clients;
var stickyWidth;
var searchExtended = false;
var scrollIndicator;
var ibHeader;
var distance;
var targetPage;
var baseUrl;
var extendedFilterActive = false;

$(document).ready(function () {

  lazyMarker = $('.ib-footer-social');
  jobContainer = $('#ib-jobs-lazy-container');
  alternateClass = "odd";
  resultCounter = $('#ib-jobs-results-count');
  scrollIndicator = $('.ib-jobs-scroll-indicator');
  targetPage = $('#ib-jobs-data').data('target');
  clients = $('#ib-jobs-data').data('clients');
  srclients = $('#ib-jobs-data').data('srclients');
  intern = $('#ib-jobs-data').data('intern');
  locations = $('#ib-jobs-data').data('locations');
  categories = $('#ib-jobs-data').data('categories');
  titles = $('#ib-jobs-data').data('titles');
  prefilter = $('#ib-jobs-data').data('prefilter');
  rmBaseUrl = $('#ib-jobs-data').data('baseurl');
  baseUrl = "/typo3conf/ext/ibjobs/Resources/Public/php/proxy.php?";
  requestURL = "";

  if (prefilter == '1') {
    requestURL = baseUrl + "clients=" + clients + "&sr_clients="+ srclients + "&intern=" + intern + "&locations=" + locations + "&categories=" + categories + "&titles=" + titles + "&baseurl=" + rmBaseUrl;
  } else {
    requestURL = baseUrl + "clients=" + clients + "&sr_clients="+ srclients + "&intern=" + intern + "&baseurl=" + rmBaseUrl;
  }


  $.ajax({
    url: requestURL,
    context: document.body
  }).done(function (data) {
    $('.ib-jobs-loader, .ib-jobs-container').toggle();
    setStickyWidth();
    jobs = JSON.parse(data);
    jobs = jobs.data;
    results = jobs;
    initJobs();
    setEvents();

    $window.scroll(function () {

      /*
      if (currentJob < currentResults) {
          if (isScrolledIntoView(lazyMarker)) {
              addJobRow(results[currentJob]);
          }
      }
      */
      if ($('.ib-jobs-search-container').is(":visible")) {
        checkFixed();
      }

      //checkScrollIndicator();
    });

  });



});

function isScrolledIntoView(elem) {
  var $elem = $(elem);
  var $window = $(window);

  var docViewTop = $window.scrollTop();
  var docViewBottom = docViewTop + $window.height();

  var elemTop = $elem.offset().top - lazyOffset;
  var elemBottom = elemTop + $elem.height();

  return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

function searchTerms(terms, strict) {
  if (strict === undefined) {
    strict = false;
  }

  results = [];


  /*
   ** strict search for select options
   */

  if (strict) {
    var found = false;
    $.each(jobs, function (key, val) {
      if (
        (terms[0].text == "true" || val[terms[0].val] == terms[0].text) &&
        (terms[1].text == "true" || val[terms[1].val] == terms[1].text) &&
        (terms[2].text == "true" || val[terms[2].val] == terms[2].text)
      ) {
        results.push(jobs[key]);
      }
    });
  }


  /*
   *  normal search
   */
  else {
    terms = terms.match(/\S+/g);
    var tmpRegex = "";
    var limitTerms = 0;
    if (terms && terms.length) {
      limitTerms = terms.length;
      for (var i = 0; i < limitTerms; i++) {
        terms[i] = terms[i].replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
        tmpRegex += "(?=.*" + terms[i] + ")";
      }
      $.each(jobs, function (key, val) {
        regex = new RegExp(tmpRegex, "i");
        if (val[15].search(regex) != -1) {
          results.push(jobs[key]);
        }
      });
    } else {
      results = jobs;
    }




  }

  //checkScrollIndicator();
  initJobs();
}

function addJobRow(job) {
  /*
   *   attrib list
   */
  // 0 : description
  // 1 : city
  // 2 : category
  // 3 : chiffre
  // 4 : federal state
  // 5 : public since
  // 6 : region/client
  // 7 : job id
  // 8 : trf_key
  // 9 : trf_description
  // 10: intern
  // 11: name
  // 12: a_id
  // 13: jobtype
  // 14 : search values
  // 15 : External jobdetail URL

  if (job[10] == '1' && job[9] != "") {
    trfTemplate = '<span class="ib-font-size-12">TRF Bez: ' + job[9] + '</span>';
  } else {
    trfTemplate = "";
  }
  //check jobtype
  if (job[13] == "HAUFE") {
    this.jobTemplate = '<div class="' + this.alternateClass + ' animateJob row">' + '<div class="ib-jobs-description columns small-12 medium-8">' + '<span class="hide-for-small-only">' + job[6] + " | " + job[2] + '</span>' + '<a href="' + job[14] + '" target="_blank"">' + job[0] + '</a>' + trfTemplate + '<span class="hide-for-small-only ib-font-size-12">Chiffre: ' + job[3] + '</span>' + '</div>' + '<div class="ib-jobs-location columns small-12 medium-4">' + '<span>' + job[1] + '</span>' + '<span class="hide-for-small-only"><span class="RS_MESSAGE"><!-- Bundesland --></span>' + job[4] + '</span>' + '<span class="ib-font-size-12">Veröffentlicht am: ' + job[5] + '</span>' + '</div>' + '</div>';
  }
  else {
    this.jobTemplate = '<div class="' + this.alternateClass + ' animateJob row">' + '<div class="ib-jobs-description columns small-12 medium-8">' + '<span class="hide-for-small-only">' + job[6] + " | " + job[2] + '</span>' + '<a href="' + targetPage + "/" + job[7] + '" target="_blank"">' + job[0] + '</a>' + trfTemplate + '<span class="hide-for-small-only ib-font-size-12">Chiffre: ' + job[3] + '</span>' + '</div>' + '<div class="ib-jobs-location columns small-12 medium-4">' + '<span>' + job[1] + '</span>' + '<span class="hide-for-small-only"><span class="RS_MESSAGE"><!-- Bundesland --></span>' + job[4] + '</span>' + '<span class="ib-font-size-12">Veröffentlicht am: ' + job[5] + '</span>' + '</div>' + '</div>';
  }

  jobContainer.append(this.jobTemplate);

  if (this.alternateClass == "odd") {
    this.alternateClass = "even";
  } else {
    this.alternateClass = "odd";
  }

  currentJob++;

}

function initJobs() {
  jobContainer.empty();
  currentJob = 0;




  currentResults = results.length;
  limit = 1000;
  if (results.length < limit) {
    limit = results.length;
  }
  if (results.length <= 3) {
    /*
    $("html, body").animate({
      scrollTop: 0
    }, "fast");
    */
  }
  for (var i = 0; i < limit; i++) {
    addJobRow(results[i]);
  }
  updateResults();
}

function resetSearch() {
  $('#ib-jobs-search').val('');
  $('select').prop('selectedIndex', 0);
  searchTerms("");
}

function updateResults() {
  resultCounter.html(results.length);
}

function checkFixed() {

  ibHeader = $('#ib-header');



  if (ibHeader.height() == 0) {
    ibHeader = $('.jetmenu');
  }

  distance = $('#toStick').offset().top - ibHeader.offset().top;



  if (distance <= ibHeader.height()) {
    $('.ib-jobs-search-container').addClass('ib-jobs-sticky');
    if (searchExtended) {
      $('#ib-jobs-lazy-container').addClass('ib-job-container-spacer-extended');
    } else {
      $('#ib-jobs-lazy-container').addClass('ib-job-container-spacer-default');
    }


  } else {
    $('.ib-jobs-search-container').removeClass('ib-jobs-sticky');
    $('#ib-jobs-lazy-container').removeClass('ib-job-container-spacer-extended');
    $('#ib-jobs-lazy-container').removeClass('ib-job-container-spacer-default');
  }

  if ($('.ib-jobs-search-container').hasClass('ib-jobs-sticky')) {
    $('.ib-jobs-search-container').css('top', ibHeader.height());
  }

}

function setEvents() {

  //search extended
  $('.ib-jobs-extended-search-container').on('click', function () {
    searchExtended = !searchExtended;
    $('.ib-jobs-container #ib-jobs-extended-search').toggleClass('searchActive');
    $('.ib-jobs-container .ib-jobs-extended-search-container i').toggleClass('ib-icon-arrow-right ib-icon-arrow-down');

  });

  /*
   * search
   */


  //set delay for inputkey
  var delay = makeDelay(350);

  //input key up
  $('#ib-jobs-search').keyup(function () {
    delay(function () {
      searchTerms($('#ib-jobs-search').val());
    });

  });

  //select change
  $('.ib-jobs-ext-filter').on('change', function () {
    searchTerms(prepareExtFilterTerm(), true);
  });

  //reset button
  $('#ib-jobs-reset').on('click', function () {
    resetSearch();
  });

  $(window).resize(function () {
    setStickyWidth();
  });
}

function prepareExtFilterTerm() {
  var term = [];

  //var term = "";
  /*
  $.each($('.ib-jobs-ext-filter'), function (key, val) {
      if ($("option:selected", this).index() !== 0) {
          term = term + " " + $("option:selected", this).text();
      }
  });
  */

  $.each($('.ib-jobs-ext-filter'), function (key, val) {
    var val = $(this).data('val');
    var data = {};
    if ($("option:selected", this).index() !== 0) {
      data.val = val;
      data.text = $("option:selected", this).text();
      term.push(data);
    } else {
      data.val = val;
      data.text = 'true';
      term.push(data);
    }
  });

  return term;

}

function setStickyWidth() {
  var tmpWidth = $('#toStick').width();
  $('.searchHeader').css('max-width', tmpWidth);
}

function checkScrollIndicator() {
  if (currentJob == currentResults) {
    scrollIndicator.fadeOut();
  } else {
    scrollIndicator.fadeIn();
  }

}

function makeDelay(ms) {
  var timer = 0;
  return function (callback) {
    clearTimeout(timer);
    timer = setTimeout(callback, ms);
  };
};
