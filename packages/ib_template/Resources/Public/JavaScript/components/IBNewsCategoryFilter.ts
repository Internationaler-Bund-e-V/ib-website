import $ from 'jquery';
import 'select2';

class IBNewsCategoryFilter
{
    constructor() {
        ($("#ibNewsCategories") as any).select2({
            placeholder: "Kategorie auswählen",
            allowClear: true
        });

        $('#ibCategoryFilterButton').on('click', function () {
            var url = $('#ibNewsCategories').val();
            if (url == "") {
                url = $('#ibNewsCategories').data('ibnewsmainurl');
            }
            window.location.href = (url as string);
        })
    }
}

export default IBNewsCategoryFilter;
