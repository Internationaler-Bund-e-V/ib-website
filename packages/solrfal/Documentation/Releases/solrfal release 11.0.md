# Version 11.0.0

EXT:solrfal 11.0 is the new release for TYPO3 11.5, our major versions are now matching the version of TYPO3. At this point we would like to express our thanks to all contribution partners
and especially Georg Ringer for the work on the TYPO3 11 LTS compatibility.

Note: Delayed update handling introduced with EXT:solr 11.2 is not yet supported by solrfal. Solrfal will consider the introduced UpdateEvents in an upcoming version and with this adaption also the
delayed update handling.

\#standwithukraine \#nowar

## New in this release:

### [TASK] Improve exception handling (#190)

It might occur that the storage page of an updated record couldn't be found, e.g. as already deleted or an inconsistent database. To ensure system won't be blocked and the index is up-to-date handling of this exceptions in the ConsistencyAspect is improved.
To simplify exception handling in general and also failure analysis, solrfal specific exceptions and individual error codes are introduced.

### [CLEANUP] Improve/simplify valid filePublicUrl

Simplifies and improves the determination of the correct path for dumpFile eID script and non-public storages. By adapting the url generation in the DocumentFactory it can be simplified and also allows a
separate usage of the DocumentFactory.

### [BUGFIX] Fix file detection in PageContext (#170)

Since signal preGeneratePublicUrl has been removed in TYPO3 11 LTS no files in PageContext can be detected. This is fixed by using the existing GeneratePublicUrlForResourceEventListener to detect the files.

### [BUGFIX] Undefined array key "_FILES" in StorageContextDetector line 167 (#172)

Starting the indexer on PHP 8+ with fileadmin lead to unwanted warnings.

### [BUGFIX] Fix notices during indexing (#174)

### [TASK] Fix DocumentFactoryTest (#160)

Adapts and fixes the DocumentFactoryTest case handleProtectedFilesInPageContext, as there were changes in the basic tests of solr and solrfal.

### [BUGFIX] Ensure valid filePublicUrl (#164)

Determining the correct path for dumpFile eID script for non-public storages might fail if indexing in CLI context. This issue is fixed
by using the site and language information stored in the solrfal contexts and build the url on that basis.

### [TASK:11.5] Sync EXT:solr and EXT:solrfal APIs

This change contains a sync to EXT:solr release-11.5.x API changes.

### [TASK] Apply TYPO3 Coding Standards on ext_*.php and Tests/Unit

TYPO3 Coding Standards introduced and required fixes carried out

### [BUGFIX] Fix progess bar styling

### [TASK] Remove not used ext_update file

### [TASK] Simplify controller's code

### [TASK] Use new namespace of PageRepository

### [TASK] Replace usage of TYPO3_REQUESTTYPE_CLI

### [TASK] Replace usage of TextExtractorRegistry::getInstance()

### [BUGFIX] Properly fake TSFE

### [TASK] Move extension icon

### [BUGFIX] Fix calls of getSolrConfigurationFromPageId

The order of arguments changed, however 2nd argument must be the language

### [BUGFIX] Correct link to index queue module

### [BUGFIX] Make the backend module work again

### [TASK] Migrate signals of core to events (#159)

Migrate signals of core to events

### [TASK] use TYPO3_VERSION and make it mandatory in composer scripts
