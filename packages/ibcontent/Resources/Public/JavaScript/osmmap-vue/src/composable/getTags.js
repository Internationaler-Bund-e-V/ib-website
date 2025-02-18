import tagConfig from "../assets/tags_config.json";
export function generateTags(Tags, locations) {
    Tags.value = [];
    //add "placeholder"
    Tags.value.push({
        title: "Alle",
        id: "Alle",
        color: "transparent",
    });
    for (let i = 0; i < locations.value.length; i++) {
        if (locations.value[i].Tag.length > 0) {
            for (let j = 0; j < locations.value[i].Tag.length; j++) {
                let tmpTag = locations.value[i].Tag[j];
                Tags.value.push({
                    title: tmpTag.name,
                    id: tmpTag.LocationsTag.tag_id,
                    color: tagConfig[tmpTag.LocationsTag.tag_id].fillColor,
                });
            }
        }
    }
    Tags.value = [...new Map(Tags.value.map((item) => [item["id"], item])).values()];

    // sort by name
    Tags.value.sort((a, b) => {
        const nameA = a.title.toUpperCase(); // ignore upper and lowercase
        const nameB = b.title.toUpperCase(); // ignore upper and lowercase
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
