.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

===========================================
Apache Solr for TYPO3 - Console tools 4.0.0
===========================================

We are happy to release EXT:solrconsole 4.0.0.
The focus of this release was the compatibility with TYPO3 10 LTS and EXT:solr v11.0.x.

New in this release
===================

[BUGFIX] Fixes for latest symfony console changes
-------------------------------------------------

BUGFIX: Install all dependencies in one command
-----------------------------------------------

[TASK] Remove removed commands from documentation
-------------------------------------------------

Removed following commands from documentation:

* solr:connection:update
* solr:connection:flushall


[TASK] Drop support of legacy site mode
---------------------------------------


As already announced in solrconsole 4 the legacy site support is dropped,
this commit deletes the deprecated tasks.

The following commands are dropped and no more available:
* solr:connection:update
* solr:connection:flushall


Contributors
============

Like always this release would not have been possible without the help from our
awesome community. Here are the contributors to this release.

(patches, comments, bug reports, reviews, ... in alphabetical order)

* Markus Friedrich
* Rafael Kähm
* Timo Hund

Also a big thanks to our partners that have joined the EB2020 program:

* +Pluswerk AG
* .hausformat GmbH
* 3m5. Media GmbH
* 4eyes GmbH
* Agora Energiewende Smart Energy for Europe Platform (SEFEP) gGmbH
* Amedick & Sommer Neue Medien GmbH
* AUSY SA
* b13 GmbH
* BARDEHLE PAGENBERG Partnerschaft mbB
* BIBUS AG Group
* Bitmotion GmbH
* brandung GmbH & Co. KG
* cab services ag
* clickstorm GmbH
* comwrap GmbH
* cron IT GmbH
* CS2 AG
* cyperfection GmbH
* digit.ly GmbH
* Digitale Offensive GmbH Internetagentur
* E-Magineurs
* Eidg. Forschungsanstalt WSL
* FGTCLB GmbH
* FTI Touristik GmbH
* GAYA - Manufacture digitale
* Hochschule für Polizei und öffentliche Verwaltung Nordrhein-Westfalen
* hotbytes GmbH & Co. KG
* IHK Neubrandenburg
* in2code GmbH
* Inotec Sicherheitstechnik GmbH
* jweiland.net
* Kassenzahnärztliche Vereinigung Bayerns (KZVB)
* Kassenärztliche Vereinigung Rheinland-Pfalz
* Landeskriminalamt Thüringen
* LfdA – Labor für digitale Angelegenheiten GmbH
* Macaw Germany Cologne GmbH
* Marketing Factory Consulting GmbH
* Masterflex SE
* mehrwert intermediale kommunikation GmbH
* mm Online Service
* netlogix GmbH & Co. KG
* Open New Media GmbH
* plan.net - agence conseil en stratégies digitales
* plan2net GmbH
* PROFILE MEDIA GmbH
* ressourcenmangel dresden GmbH
* RKW Rationalisierungs- und Innovationszentrum der Deutschen Wirtschaft e. V.
* ruhmesmeile GmbH
* Sandstein Neue Medien GmbH
* Stadt Wien - Wiener Wohnen Kundenservice GmbH
* Stefan Galinski Internetdienstleistungen
* TOUMORØ
* Typoheads GmbH
* unternehmen online GmbH & Co. KG
* VisionConnect GmbH
* werkraum Digitalmanufaktur GmbH
* WIND Internet
* zimmer7 GmbH

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
