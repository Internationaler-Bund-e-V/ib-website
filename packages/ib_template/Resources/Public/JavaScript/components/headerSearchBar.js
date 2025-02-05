import $ from 'jquery';

$(function () {
    $('.autocomplete-suggestions').first().addClass('searchHeaderBarFocus');
    var solrACElement = $('.autocomplete-suggestions').first().detach();

    $('.headerSearchBarSearch').append(solrACElement);
    $('.ib-hsb-actionButton').on('click', function () {
        $('#ib-container, #ib-footer').toggleClass('dimContent');
        $('body').toggleClass('pauseScrolling');
        $('#ib-headerSearchBar').toggleClass('ib-hsb-fullWidth');
        $('.headerSearchBarIcon i').toggleClass('ib-hsb-hide');
        $('#inputSearchIcon').toggle();
        $('.headerSearchBarSearch').toggleClass('expanded');
        var status = $('.headerSearchBarSearch input').prop('disabled');
        $('.headerSearchBarSearch input').prop('disabled', !status).focus().val("");
    })
});
