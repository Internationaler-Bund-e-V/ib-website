.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.txt

.. _releases_archive_2-0:

=========================================
Apache Solr for TYPO3 - Console tools 2.0
=========================================

We are happy to release EXT:solrconsole 2.0.0.
The focus of this release was the compatibility with EXT:solr 9.0 and EXT:solrfal

New in this release
===================

[TASK] Add command solr:connection:flushall
-------------------------------------------

Adds a new command solr:connection:flushall that deletes all connections from the sys_registry

[TASK] Have shared base classes for solr and solrfal commands
-------------------------------------------------------------

Adds AbstractCommand, AbstractSolrCommand and AbstractSolrfalCommand to allow implementation of shared functionallity

[TASK] Optimize connection update for all sites
-----------------------------------------------

*   Initializes the connection one by one, instead all in one call
*   This has the advantage that you get an interactive progress on the command line

[FEATURE] Add new command solr:index:verify
-------------------------------------------

Adds a new command 'solr:index:verify' to the console. This command helps to check the differences between solr and TYPO3, it can delete documents from solr that do not exist in TYPO3 and queues documents in TYPO3 that are missing in Solr.

[TASK] Show read and write connections in solr:connection:get
-------------------------------------------------------------

Adapts the get command to show the read and the write connections

[TASK] Add command solrfal:queue:progress
-----------------------------------------

Adds command solrfal:queue:progress


[TASK] Add command solrfal:queue:index
--------------------------------------

Adds the command solrfal:queue:index

[TASK] Add command solrfal::queue::get
--------------------------------------

Adds the command solrfal:queue:get

[TASK] Implement command solrfal:queue:delete
---------------------------------------------

Implements the command solrfal:queue:delete

[TASK] Add implementation for solrfal::queue::reset-errors
----------------------------------------------------------

[TASK] Make compatible with EXT:solr 9.0.0
------------------------------------------

 Applies the required changes for the compatibility with EXT:solr 9 and Solarium

Small improvements and bugfixes
-------------------------------

*   [TASK] Add documentation for solr:connection:flushall
*   [BUGFIX] Wrong colors for errors used
*   [TASK] Apply changes from ext:solr 9.0.0-dev regarding site entity

Contributors
============

Like always this release would not have been possible without the help from our
awesome community. Here are the contributors to this release.

(patches, comments, bug reports, reviews, ... in alphabetical order)

* Jens Jacobsen
* Timo Hund

Also a big thanks to our partners that have joined the EB2019 program:

* Amedick & Sommer Neue Medien GmbH
* BIBUS AG Group
* Bitmotion GmbH
* CS2 AG
* Gernot Leitgab
* Getdesigned GmbH
* Hirsch & Wölfl GmbH
* ITK Rheinland
* Kassenärztliche Vereinigung Bayerns (KZVB)
* TOUMORO
* Ueberbit Gmbh
* XIMA MEDIA GmbH
* b13 GmbH
* bgm business websolutions GmbH & Co KG
* datamints GmbH
* medien.de mde GmbH
* mehrwert intermediale kommunikation GmbH
* mellowmessage GmbH
* plan2net GmbH
* punkt.de GmbH

Special thanks to our premium EB 2019 partners:

* jweiland.net
* sitegeist media solutions GmbH
