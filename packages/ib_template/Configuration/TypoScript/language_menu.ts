/**
* ---------------------------------------------------
* LANGUAGE MENU
* ---------------------------------------------------
* an example can be found at the end of this file
* please use constants to configure the single links inside the menu
* this can be done for each portal
*
* @see constant $const_language_menu
* @author mkettel, 2018-20-23
*/
lib.languageMenu = COA
lib.languageMenu.10 = HMENU
lib.languageMenu.10 {
    special = language
    special.value = 0,1,2
    addQueryString = 1
    addQueryString {
        method = GET
        exclude = cHash, no_cache, id, FE_SESSION_KEY, L
    }

    1 = TMENU
    1 {
        NO = 1
        NO {
            doNotLinkIt = 1
            stdWrap {
                override = DE || EN || IT
                typolink {
                    parameter.data = page:uid
                    additionalParams = &L=0 || &L=1 || &L=2
                    ATagParams = hreflang="de-DE" || hreflang="en-EN" || hreflang="it-IT"
                    addQueryString = 1
                    addQueryString.exclude = L,id,no_cache
                    addQueryString.method = GET
                    no_cache = 0
                }
            }
        }

        ACT < .NO

        # do not show if no translation exists
        USERDEF1 = 1
        USERDEF1 {
            doNotLinkIt = 1
            stdWrap.cObject = TEXT
            stdWrap.cObject.value =
        }
    }
}

// --------------------------------------------------
// overwrite above settings with portal constants
// --------------------------------------------------
// @see const_language_menu in /ts/constants_general.ts
lib.languageMenu.10 {
    special.value = {$const_language_menu.active_language_ids}
    1.NO.stdWrap {
        override = {$const_language_menu.active_language_labels}
        typolink.additionalParams = {$const_language_menu.active_language_link_ids}
        typolink.ATagParams = {$const_language_menu.active_language_link_hreflang}
    }
}

// hide language menu if set in constants
[{$const_language_menu.show_language_menu} == 1]
    lib.languageMenu >
[global]


//
// example: only show DE and EN language links
//
#const_language_menu {
#   hide_language_menu = 0
#   active_language_ids = 0,1
#   active_language_labels = DE || EN
#   active_language_link_ids = &L=0 || &L=1
#   active_language_link_hreflang = hreflang="de-DE" || hreflang="en-EN"
#}
