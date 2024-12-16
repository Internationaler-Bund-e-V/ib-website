.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

===========================================
Apache Solr for TYPO3 - Console tools 2.0.0
===========================================

We are happy to release EXT:solrconsole 2.0.0.
The focus of this release was the compatibility with EXT:solr 9.0 and EXT:solrfal

New in this release
===================

[TASK] Add documentation for solr:connection:flushall
-----------------------------------------------------

[TASK] Add command solr:connection:flushall
-------------------------------------------

Adds a new command solr:connection:flushall that deletes all connections from the sys_registry

[BUGFIX] Wrong colors for errors used
-------------------------------------

[TASK] Have shared base classes for solr and solrfal commands
-------------------------------------------------------------

Adds AbstractCommand, AbstractSolrCommand and AbstractSolrfalCommand to allow implementation of shared functionallity

[TASK] Optimize connection update for all sites
-----------------------------------------------

* Initializes the connection one by one, instead all in one call
* This has the advantage that you get an interactive progress on the command line

[FEATURE] Add new command solr:index:verify
-------------------------------------------

Adds a new command 'solr:index:verify' to the console. This command helps to check the differences between solr and TYPO3, it can delete documents from solr that do not exist in TYPO3 and queues documents in TYPO3 that are missing in Solr.

[TASK] Apply changes from ext:solr 9.0.0-dev regarding site entity
------------------------------------------------------------------

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

How to Get Involved
===================

There are many ways to get involved with Apache Solr for TYPO3:

* Submit bug reports and feature requests on [GitHub](https://github.com/TYPO3-Solr/ext-solr)
* Ask or help or answer questions in our [Slack channel](https://typo3.slack.com/messages/ext-solr/)
* Provide patches through Pull Request or review and comment on existing [Pull Requests](https://github.com/TYPO3-Solr/ext-solr/pulls)
* Go to [www.typo3-solr.com](http://www.typo3-solr.com) or call [dkd](http://www.dkd.de) to sponsor the ongoing development of Apache Solr for TYPO3

Support us by becoming an EB partner:

http://www.typo3-solr.com/en/contact/

or call:

+49 (0)69 - 2475218 0

