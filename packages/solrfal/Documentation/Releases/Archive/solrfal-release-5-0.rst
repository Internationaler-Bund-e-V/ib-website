.. include:: /Includes.rst.txt

.. _releases_archive_5-0:

===========
Version 5.0
===========

Release 5.0.0
=============

- Feature: Add backend module to see the file indexing queue status
- Bugfix: PageContextDetector::addDetectedFilesToPage should return empty array, when page context is disabled
- Feature: Refactor connection handling to differ between read and write connections
- Feature: Add hook in PageContextDetector ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solrfal']['PageContextDetectorAspectInterface']) to force added files in pageContext
- Bugfix: Prevent interruption of indexer when file is missing
- Feature: Allow to requeue from EXT:solr index inspector
- Feature: Added command "solrfal:resetQueueError" to reset errored items in the queue
- Bugfix: Prevent interruption of indexer with invalid connection
- Bugfix: findAllOutStandingMergeIdSets() does not recognize errored items
- Task: Remove Migrations\IntroduceIndexingConfiguration
- Task: Migrate queries to doctrine dbal