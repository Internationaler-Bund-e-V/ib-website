plugin.tx_news {
    view {
        templateRootPaths >
        templateRootPaths {
            0 = EXT:news/Resources/Private/Templates/
            1 = typo3conf/ext/ib_template/Resources/Private/Template/html/ext/news/Templates/
        }
        partialRootPaths >
        partialRootPaths {
            0 = EXT:news/Resources/Private/Partials/
            1 = typo3conf/ext/ib_template/Resources/Private/Template/html/ext/news/Partials/
        }
        layoutRootPaths >
        layoutRootPaths {
            0 = EXT:news/Resources/Private/Layouts/
            1 = typo3conf/ext/ib_template/Resources/Private/Template/html/ext/news/Layouts/
        }
    }
    settings{
        displayDummyIfNoMedia = 0
        detail.showPrevNext = 1
        ### add dynamic backPIDs
        useStdWrap = backPid,actbackPid
        backPid.data = GP : tx_news_pi1 | actbackPid
        actbackPid = TEXT
        actbackPid.data = page:uid

        # --------------
        #  Detail
        # --------------
        detail {
            media {
                image {
                        maxWidth = 600
                }
            }
        }
        # --------------
        #  List
        # --------------
        list {
                # media configuration
            media {
                image {
                        maxWidth = 320
                        maxHeight = 150
                }
                #dummyImage = typo3conf/ext/news/Resources/Public/Images/dummy-preview-image.png
            }
        }
    }
}

plugin.tx_news._LOCAL_LANG.default.more-link = more
plugin.tx_news._LOCAL_LANG.default.back-link = Back to: News
plugin.tx_news._LOCAL_LANG.default.start-page-headline = Press & News
plugin.tx_news._LOCAL_LANG.de.more-link = Mehr erfahren
plugin.tx_news._LOCAL_LANG.de.back-link = Zurück zur Übersicht
plugin.tx_news._LOCAL_LANG.de.paginate_overall = Seite %s von %s.
plugin.tx_news._LOCAL_LANG.de.paginate_next = vor >
plugin.tx_news._LOCAL_LANG.de.paginate_previous = < zurück
plugin.tx_news._LOCAL_LANG.de.start-page-headline = Presse & News

plugin.tx_news.settings.cropMaxCharacters =350
plugin.tx_news.settings.list.paginate {
    itemsPerPage = 10
    insertAbove = 0
    insertBelow = 1
    prevNextHeaderTags = 1
    maximumNumberOfLinks = 10
}
plugin.tx_news._LOCAL_LANG.en.dateFormat = %d.%m.%Y
plugin.tx_news._LOCAL_LANG.de.dateFormat = %d.%m.%Y
plugin.tx_news.settings.customSingleDateFormat = d.m.Y H:i

plugin.tx_news.features.requireCHashArgumentForActionArguments  = 0

#plugin.tx_news.settings.cssFile >
