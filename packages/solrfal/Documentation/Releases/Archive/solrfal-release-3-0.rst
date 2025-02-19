.. include:: /Includes.rst.txt

.. _releases_archive_3-0:


===========
Version 3.0
===========

Release 3.0.0
=============

- Set EXT:solr dependency to 4.0.0 since TypoScript configuration object is not available until version 4
- Removed usage of deprecated method to get the table name ("getTableToIndexByIndexingConfigurationName()")  and switched to TypoScript configuration object
- Fix file detection in record context to ensure file detection in translated records
- Added validity check to the file attachment resolver, to ensure that hidden references and missing files are not added to the index queue
- Registered new signal to remove files marked as missing
- Updates of sys_file records are respected
- Add file extension configuration option to record context
- Added check for non existing index queue items on updates to storage context detector
- Fix incomplete definition of index configuration name in storage context
