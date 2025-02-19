.. include:: /Includes.rst.txt

.. _releases_archive_2-1:

===========
Version 2.1
===========

Release 2.1.3
=============

- Fixed typo in __RecordContext configuration example

Release 2.1.2
=============

- Added hook in FileAttachmentResolver (FileAttachmentResolverAspectInterface) that can be used to modify the result of "detectFilesInField"
- Fix index queue initialization, since the site root wasn't considered entries of all sites were deleted or files of other site roots got detected
- ContextFactory provided context record field name instead of expected index configuration name. Fixed this by using the right database column
- Fixed Bug that space in "plugin.tx_solr.enableFileIndexing.pageContext.fileExtensions", was not handled properly

Release 2.1.1
=============

- Set dependency of EXT:solr to 3.1.1 because 3.1.0 contains an invalid composer.json file without a version number
