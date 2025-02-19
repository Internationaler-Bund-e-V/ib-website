.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _releases_10-0:

==========================================
Apache Solr for TYPO3 - Console tools 10.0
==========================================

We are happy to release EXT:solrconsole 10.0.0.
The focus of this release was the compatibility with TYPO3 10 LTS and EXT:solr v11.1.x.

**Note**: As we're planning to simplify the extension and dependency handling we're harmonize the extension version with TYPO3. So the next version of EXT:solrconsole is 10.0.0 and compatible with TYPO3 10 LTS and EXT:solr v 11.1.x.

New in this release
===================

[TASK] Fix TYPO3 and EXT:Solr dependencies on current dev line
--------------------------------------------------------------

This change provides proper requirement constraints for EXT:solrconsole dependencies:

* EXT:solr >=11.1.0
* TYPO3 >=10.4.9
* helhum/typo3-console >=6.3.4


[BUGFIX] solrfal:queue:progress command is not registered
---------------------------------------------------------

Command solrfal:queue:progress was not registered in previous versions of EXT:solrconsole.

[FEATURE] Add command solr:index:flush
--------------------------------------

The command solr:index:delete already deletes documents from a core that belong to that language/site/etc. But sometimes you want to flush the whole core e.g. to cleanup leftovers. The command solr:index:flush performs a deletion on all documents of a core.

[BUGFIX] Ignore shortcut and access restricted pages on index:verify
--------------------------------------------------------------------

This change makes it possible to verify the pages properly. Previously following edge cases were not covered:

* The shortcut pages targeted to pages of same page tree, were assumed to be in index. Which is wrong since EXT:solr indexes the target pages only as is.
* The access restricted variants of Solr documents were considered as unique records, which is wrong. **NOTE**: EXT:solr console can not recognize missing access restricted variants.

[TASK] Fix tests within solr-ddev-site
--------------------------------------

This change allows running CI tests on solr-ddev-site or othe environments.


Contributors
============

Like always this release would not have been possible without the help from our
awesome community. Here are the contributors to this release.

(patches, comments, bug reports, reviews, ... in alphabetical order)

* Rafael Kähm

Also a big thanks to our partners that have joined the EB2021 program:

* +Pluswerk AG
* 711media websolutions GmbH
* Abt Sportsline GmbH
* ACO Severin Ahlmann GmbH & Co. KG
* AVM Computersysteme Vertriebs GmbH
* cosmoblonde GmbH
* creativ clicks GmbH
* cron IT GmbH
* CS2 AG
* CW Media & Systems
* Earlybird GmbH & Co KG
* Earlybird GmbH & Co KG
* FLOWSITE GmbH
* form4 GmbH & Co. KG
* Getdesigned GmbH
* Granpasso Digital Strategy GmbH
* Ikanos GmbH
* internezzo ag
* Intersim AG
* Ion2s GmbH
* Leitgab Gernot
* mellowmessage GmbH
* Moselwal Digitalagentur UG (haftungsbeschränkt)
* network.publishing Möller-Westbunk GmbH
* OST Ostschweizer Fachhochschule
* Plan.Net Suisse AG
* Provitex GmbH
* punkt.de GmbH
* queo GmbH
* Rechnungshof
* Schoene neue kinder GmbH
* SIT GmbH
* SIZ GmbH
* Stämpfli AG
* Triplesense Reply Frankfurt
* TWT reality bytes GmbH
* visol digitale Dienstleistungen GmbH
* Web Commerce GmbH
* webconsulting business services gmbh
* webschuppen GmbH
* Webstobe GmbH
* Webtech AG
* wow! solution
* XIMA MEDIA GmbH
