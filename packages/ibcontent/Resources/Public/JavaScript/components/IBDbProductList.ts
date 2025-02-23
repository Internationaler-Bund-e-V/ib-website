import $ from 'jquery';

class IBDbProductList
{
    protected element: Element | HTMLElement | null;
    protected searchField: HTMLInputElement | null;

    constructor(element:Element|HTMLElement) {
        this.element = element;
        this.searchField = (element.querySelector('.citySearchInput') as HTMLInputElement)
        if (this.searchField) {
            this.searchField.addEventListener('input', () => {
                this.searchTitle();
            });
            this.searchField.addEventListener('focus', () => {
                this.searchTitle();
            });
        }
    }

	searchTitle() {
		var searchTag = this.searchField!.value.toLowerCase();
        this.element!.querySelectorAll('.citytitle.enabled').forEach((city:Element) => {
            const cityElement:HTMLElement = city as HTMLElement;

            if (cityElement.dataset.citysearch!.toLowerCase().indexOf(searchTag) >= 0) {
                cityElement.style.display = 'block';
			} else {
                cityElement.style.display = 'none';
			}
		});
	}

}
export default IBDbProductList;
