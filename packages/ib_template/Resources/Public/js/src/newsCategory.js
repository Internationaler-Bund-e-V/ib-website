/**
 * select categories for news
 */


$(document).ready(function () {
    console.log("news categories...");
    $("#ibNewsCategories").select2({
        placeholder: "Kategorie auswählen",
        allowClear: true
    });

    $('#ibCategoryFilterButton').on('click', function () {
        var url = $('#ibNewsCategories').val();
        if (url == "") {
            url = $('#ibNewsCategories').data('ibnewsmainurl');
        }
        window.location = url;
    })
})