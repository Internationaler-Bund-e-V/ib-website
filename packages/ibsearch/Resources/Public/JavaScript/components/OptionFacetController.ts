import $ from 'jquery';

class OptionFacetController
{
    public init() {
        this.initToggle();
        this.initFilter();

    }
    initToggle() {

        $('.tx-solr-facet-hidden').hide();
        $('a.tx-solr-facet-show-all').on('click', (event) => {
            if ($(event.currentTarget).parent().siblings('.tx-solr-facet-hidden:visible').length == 0) {
                $(event.currentTarget).parent().siblings('.tx-solr-facet-hidden').show();
                $(event.currentTarget).text($(event.currentTarget).data('label-less'));
            } else {
                $(event.currentTarget).parent().siblings('.tx-solr-facet-hidden').hide();
                $(event.currentTarget).text($(event.currentTarget).data('label-more'));
            }

            return false;
        });
    }

    initFilter() {
        const filterableFacets = $(".facet-filter-box").closest('.facet');

        filterableFacets.each((index:number, element:HTMLElement) => {
            const searchBox = $(element).find('.facet-filter-box');
            const searchItems = $(element).find('.facet-filter-item');

            searchBox.on('keyup', () => {
                const value = (searchBox.val() as string).toLowerCase();

                searchItems.each((index:number, item:HTMLElement) => {
                    var filteredItem = $(item);
                    filteredItem.toggle(filteredItem.text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    }
}

export default OptionFacetController;
