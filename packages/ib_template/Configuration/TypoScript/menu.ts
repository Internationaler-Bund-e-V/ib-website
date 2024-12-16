# ------------------------------------------------------------
# FOOTER MENUS
# ------------------------------------------------------------
lib.footerCentral = HMENU
lib.footerCentral {
    special = directory
    special.value = {$const_navigation.footernav_central_folder_id}
    1 = TMENU
    1 {
        wrap = |
        noBlur = 1
        NO = 1
        NO {
            allWrap = <li class="columns medium-6">|</li>
            allWrap.insertData = 1
            sdtWrap.htmlSpecialChars = 1
            ATagTitle.field = title
        }
        ACT < .NO
        ACT = 1
        ACT {
            wrapItemAndSub = <li class="columns medium-6">|</li>
        }
    }
}

lib.footerMisc = HMENU
lib.footerMisc {
    special = directory
    special.value = {$const_navigation.footernav_misc_folder_id}
    1 = TMENU
    1 {
        wrap = |
        noBlur = 1
        NO = 1
        NO {
            allWrap = <li class="columns medium-12">|</li>
            allWrap.insertData = 1
            sdtWrap.htmlSpecialChars = 1
            ATagTitle.field = title
        }
        ACT < .NO
        ACT = 1
        ACT {
            wrapItemAndSub = <li class="columns medium-12">|</li>
        }
    }
}

lib.footerMeta = HMENU
lib.footerMeta {
    special = directory
    special.value = {$const_navigation.footernav_meta_folder_id}
    1 = TMENU
    1 {
        wrap = |
        noBlur = 1
        NO = 1
        NO {
            allWrap = <li class="ib-footer-meta-item">|</li>
            allWrap.insertData = 1
            sdtWrap.htmlSpecialChars = 1
            ATagTitle.field = title
        }
        ACT < .NO
        ACT = 1
        ACT {
            #wrapItemAndSub = <li class="columns medium-1">|</li>
        }
    }
}

# -----------------------------------------------------------------------------
# include language menu from extra file
# -----------------------------------------------------------------------------
#<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ib_template/Configuration/TypoScript/language_menu.ts">
@import 'EXT:ib_template/Configuration/TypoScript/language_menu.ts'

# -----------------------------------------------------------------------------
# bubble
# -----------------------------------------------------------------------------

#DE
bubble.link = TEXT
bubble.link.value = {$portalSettings.main_bubble.text}
bubble.link.typolink {
    parameter = {$portalSettings.main_bubble.id}
    additionalParams.rawUrlEncode = 1
    additionalParams.if.isTrue.data = GP:mainsite
    #title = Kontaktieren Sie uns hier bei Fragen, Wünsche und Anregungen
    title = {$portalSettings.main_bubble.title_text}
    #useCacheHash = 1
    target = {$portalSettings.main_bubble.link_target}
    extTarget = {$portalSettings.main_bubble.link_target}
}
bubble.linkText = TEXT
bubble.linkText.value = {$portalSettings.main_bubble.text}
bubble.linkID = TEXT
bubble.linkID.value = {$portalSettings.main_bubble.id}

#EN
bubble.link_en = TEXT
bubble.link_en.value = {$portalSettings.main_bubble_en.text}
bubble.link_en.typolink {
    parameter = {$portalSettings.main_bubble_en.id}
    additionalParams.rawUrlEncode = 1
    additionalParams.if.isTrue.data = GP:mainsite
    #title = Kontaktieren Sie uns hier bei Fragen, Wünsche und Anregungen
    title = {$portalSettings.main_bubble_en.title_text}
    #useCacheHash = 1
    target = {$portalSettings.main_bubble_en.link_target}
    extTarget = {$portalSettings.main_bubble_en.link_target}
}
bubble.linkText_en = TEXT
bubble.linkText_en.value = {$portalSettings.main_bubble_en.text}
bubble.linkID_en = TEXT
bubble.linkID_en.value = {$portalSettings.main_bubble_en.id}

###############################


lib.searchPageLink = TEXT
lib.searchPageLink.typolink {
    parameter = {$portalSettings.misc.searchResultId}
    returnLast = url
    forceAbsoluteUrl = 1
}
