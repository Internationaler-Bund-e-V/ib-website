.. include:: /Includes.rst.txt

.. _releases_archive_4-1:

===========
Version 4.1
===========

Release 4.1.0
=============

- Fixed file detection in pageContext, detected file uids have to be stored and considered in all indexing runs of a single page
- Fixed broken tests and initialize TSFE in pageContext since this is required in TYPO3 8 when the PageRepository is used
- Add CE textmedia to the list of content element types supported in the pageContext by default
- Add cache to consistency aspect to avoid multiple processing of the same record
- Followup of split of Site into Site and SiteRepository in EXT:solr
- Adapt Queue to implement method getStatisticsBySite to show number of files on file queue initialization (Deprecates Queue::getItemsCountBySite and ItemRepository::countBySiteAndIndexConfigurationName).
- Trigger no detector when a page or content element is updated outside a site root
- Adapt tests for TYPO3 8 LTS
- Add support for new link syntax(t3://file) support in FileAttachmentResolver
- Use the RootPageResolver instead of the Util functions
- To allow grouping accross different systems a systemHash was added to the variantId, see https://github.com/TYPO3-Solr/ext-solr/pull/929
- Wrapped CliEnvironment to be used only in cli-mode, see https://github.com/TYPO3-Solr/ext-solr/pull/936
- Documentation is now a part from Git Repo.
