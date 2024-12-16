config.index_enable = 1
config.index_externals = 1
config.index_metatags = 1

 # Plugin configuration
plugin.tx_indexedsearch {

    # disable chash check for indexedsearch
    # if this check is enabled, we cannot send requests from our custom extension (ib_search)
    # @author 10-01-2018 mk
    features.requireCHashArgumentForActionArguments = 0

    settings {
        
        # show the rules
        displayRules = 0

        # show a link to the advanced search
        displayAdvancedSearchLink = 0

        # show the number of results
        displayResultNumber = 0

        breadcrumbWrap = / || /

        # show the parse times
        displayParsetimes = 0
        displayLevel1Sections = 0
        displayLevel2Sections = 0
        displayLevelxAllTypes = 0
        displayForbiddenRecords = 0
        alwaysShowPageLinks = 0
        mediaList =

        rootPidList = {$portalSettings.root_id}
        page_links = 0

        # various crop/offset settings for single result items
        results {
            titleCropAfter = 150
            titleCropSignifier = ...
            summaryCropAfter = 180
            summaryCropSignifier =
            hrefInSummaryCropAfter = 60
            hrefInSummaryCropSignifier = ...
            markupSW_summaryMax = 300
            markupSW_postPreLgd = 60
            markupSW_postPreLgd_offset = 5
            markupSW_divider = ...
            markupSW_divider.noTrimWrap = | | |
        }

        # Blinding of option-selectors / values in these (advanced search)
        blind {
            searchType = 1
            defaultOperand = 1
            sections = 1
            freeIndexUid = 1
            mediaType = 1
            sortOrder = 1
            group = 1
            languageUid = 1
            desc = 0
            # List of available number of results. First will be used as default.
            numberOfResults = 10,25,50,100
            # defaultOperand.1 = 1
            # extResume=1
        }

        defaultOptions {
            defaultOperand = 0
            sections = 0
            freeIndexUid = -1
            mediaType = -1
            sortOrder = rank_flag
            languageUid = -1
            sortDesc = 0
            searchType = 0
            extResume = 0
        }

    }

    view {
        templateRootPaths {
            100 = EXT:ib_template/Resources/Private/Template/html/ext/indexed_search/Templates/
        }
        partialRootPaths {
            100 = EXT:ib_template/Resources/Private/Template/html/ext/indexed_search/Partials/
        }
        layoutRootPaths {
            100 = EXT:ib_template/Resources/Private/Template/html/ext/indexed_search/Layouts/
        }
    }
}
