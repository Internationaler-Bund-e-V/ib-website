export function generateCategories(Categories, locations, navID) {
    Categories.value = [];
    for (let i = 0; i < locations.value.length; i++) {
        if (locations.value[i].Category.length > 0) {
            for (let j = 0; j < locations.value[i].Category.length; j++) {
                let tmpCategory = locations.value[i].Category[j];
                if (tmpCategory.LocationsCategory.navigation_id == navID) {
                    Categories.value.push({
                        text: tmpCategory.name,
                        id: tmpCategory.id,
                    });
                }
            }
        }
    }
    Categories.value = [
        ...new Map(Categories.value.map((item) => [item["id"], item])).values(),
    ];
     // sort by name
     Categories.value.sort((a, b) => {
        const nameA = a.text.toUpperCase(); // ignore upper and lowercase
        const nameB = b.text.toUpperCase(); // ignore upper and lowercase
        if (nameA < nameB) {
            return -1;
        }
        if (nameA > nameB) {
            return 1;
        }

        // names must be equal
        return 0;
    });
}
