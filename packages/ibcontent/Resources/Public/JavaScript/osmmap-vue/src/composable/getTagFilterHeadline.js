/**
 * 
 * @param {*} tags 
 * @returns String
 */

import { inject } from "vue";

export function getTagFilterHeadline() {
    let TypoSettings = inject("TypoSettings");
    if (TypoSettings.tagFilterHeadline != "") {
        return TypoSettings.tagFilterHeadline;
    } else {
        return "Schlagwörter";
    }
}
