.. include:: /Includes.rst.txt

.. _releases_archive_6-0:


===========
Version 6.0
===========

Release 6.0.0
=============

- Feature: EXT:solr 9 follow up, differ between reading and writing connections
- Bugfix: Catch indexing exceptions
- Bugfix: Catch solarium HttpException
- Feature: EXT:solr 9 follow up, use new location of Site class
- Feature: Allow to limit the indexing on a certain siteId (for solrconsosle)
- Feature: Implement ItemRepository::findBy for solrconsole
- Bugfix: Reindex files for PageContext after changing sys_file_metadata
- Feature: Add method "deleteBy" to ItemRepository for solrconsole