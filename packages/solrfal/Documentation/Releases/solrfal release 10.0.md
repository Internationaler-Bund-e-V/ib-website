# Version 10.0.0

**Note**:
As we're planning to simplify the extension and dependency
handling we're harmonize the extension version with TYPO3.
So the next version of EXT:solrfal is 10.0.0 and
compatible with TYPO3 10 LTS and EXT:solr v 11.1.x.

## New in this release:

### [TASK!!!] Clean up of legacy notation <link file:2016 syntax detection

* removes old style TYPO3 file links syntax `<link file:2016 ...`
  replaces in all fixtures to `t3://file?uid=` syntax

Impact:
No support for `<link file:2016 ` anymore,
please use `t3://file?uid=2016 ` instead.

Fixes: #72


### [BUGFIX] RecordContextDetector uses always default language

The RecordContextDetector always queued the files in default language parallel to the correct language entry.
The file for default language MUST NOT be queued, if translation for record exists.

Fixes: #129


### [TASK] Solarium 6 folow-ups

Adjust method calls according to Solarium method signature.

Fixes: 123


### [TASK] Drop TYPO3 9.5 support : via Util::getIsTYPO3VersionBelow10()

* drops TYPO3 9.5 support in PHP code
* adjusts travis version matrix
* changes EXT:Solr dependency to dev-master
* changes dependencies TYPO3 9.5 in `ext_emconf.php` und `composer.json`

Followup of: [TYPO3-Solr/ext-solr#2889](https://github.com/TYPO3-Solr/ext-solr/issues/2889)


### [BUGFIX] Fix type hinting issues

This pull Request fixes following issues:

* ... ConsistencyAspect::issueCommandOnDetectors()
  must be of the type int, string given ...
* ... ReferenceIndexEntry::getRecord() must be of the type array, null returned ...
* ... Argument 4 passed to
  RequeueItemHandler::postProcessIndexQueueUpdateItem()
  must be of the type integer, null given

Fixes: #108


### [BUGFIX] Fix access evaluation in page context

Combinations of page and content element access settings are not
evaluated correctly, the indexing currently relies on the access
settings set in the file index queue. Unfortunately due to the
functioning of the page indexing this information may be faulty,
especially if there are several permission combinations.

This commit fixes this issue by evaluation the page and content
element permissions in the page context itself, instead of using
the permissions from indexed page document.

Additionally a new TypoScript configuration is added, to allow to
configure the considered page content permissions:

```
plugin.tx_solr.index.enableFileIndexing {
  pageContext.contentEnableFields.accessGroup = fe_group
}
```

Fixes: #119

#### [BUGFIX] Access settings in PageContext ignored

EXT:solrfal versions (8.0.0+) did not respect the access settings of pages and/or content elements in PageContext.
Required code parts and settings to consider page access settings were deactivated, see PageContext and default TypoScript.

Access settings of pages and content elements are respected again.

Fixes: #98


### [BUGFIX] Ignore invalid sys_refindex entries

Fixes errors caused by invalid sys_refindex by ignoring invalid sys_refindex entries. This is achieved by setting a nullable return type getRecord and handling invalid references in the detector.

Fixes: #115


### [TASK] Change configuration files to TYPO3 file extensions

This Pull-Request change the file extensions from TypoScript files.
Previously TypoScript files use the file extension `txt` which is replaced with `typoscript`.

Hints within code and documentations are updated according to these changes.

All changes them self should no effect current installations, since the old files are still in place and include the new ones.

*Note:* There are no changes to the TypoScript itself.


#### Changes:

* Move TypoScript configuration
  Move TypoScript configuration files from file extension 'txt' to 'typoscript'.
  Old files now import the new files in order to avoid a breaking change.
* Documentation update

Change includes in examples from text file into TypoScript file.

Fixes: #109

### [BUGFIX] print_r() expects bool, but int used in ConsistencyAspect

See [TYPO3-Solr/ext-solr#2753](https://github.com/TYPO3-Solr/ext-solr/issues/2753)

Fixes: #99

### [BUGFIX] Do not fail on PageContextDetector if file is not in database.

Previously EXT:solrfal failed on files, which were not present in sys_file or file system.
This change prevents EXT:solrfal to fail on this scenario.


