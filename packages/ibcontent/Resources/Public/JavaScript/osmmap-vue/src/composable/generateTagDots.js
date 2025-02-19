/**
 *
 * @param {*} tags
 * @returns String
 */

import tagConfig from "../assets/tags_config.json";

export function generateTagDots(tags) {
    var content = "";
    var tmpTags = tags.replace(/-/g, "");
    tmpTags = tmpTags.split(",");
    tmpTags.forEach((element) => {
        if (element) {
            var tmpColor = tagConfig[element].fillColor;
            content += `<span class="tagItemIcon"><i class="fas fa-bookmark" style="color:${tmpColor};"></i></span>`;
        }
    });
    return content;
}
