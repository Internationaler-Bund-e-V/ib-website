.. include:: /Includes.rst.txt

.. _releases_12:


==========
Version 12
==========

Release 12.0.2
==============

..  note::
    This release requires EXT:solr >=12.0.4.

This is a maintenance release for TYPO3 12.4, containing:

- [BUGFIX] Avoid type errors (thanks to Benni Mack)
- [TASK] Use new template module API (thanks to Stefan Frömken)
- [TASK] Remove ext_emconf.php autoloader (thanks to Stefan Frömken)
- [TASK] Update authors (thanks to Stefan Frömken)
- [TASK] Remove superflous @author annotations in sources (thanks to Rafael Kähm)
- [BUGFIX] Make DocumentFactory::getTranslatedRecordForItemAndTable nullable (thanks to Rafael Kähm)
- [BUGFIX] Static files in FileCollection not detected correctly (thanks to Rafael Kähm)
- [BUGFIX] Respect result of TextExtractorRegistry (thanks to Markus Friedrich)
- [TASK] Remove FrontendGroupRestriction workaround (thanks to Markus Friedrich)

Release 12.0.1
==============

This is a maintenance release for TYPO3 12.4, containing:

- [BUGFIX] Fix returned queue initialisation status (thanks to Markus Friedrich)
- [TASK] Use ext-tika branch alias instead of dev-main (thanks to Rafael Kähm)
- [TASK] Use composer exec global instead of $(composer config home)/vendor/bin (thanks to Rafael Kähm)
- [TASK] Add tests for documentation (thanks to Markus Friedrich)
- [TASK] Replace cibuild.sh (thanks to Markus Friedrich)
- [BUGFIX] Non error-proof array key check for EXTCONF array (thanks to Rafael Kähm)
- [BUGFIX] Correct initialization of aspect (thanks to Georg Ringer)
- [TASK] Update testing framework (thanks to Markus Friedrich)

Release 12.0.0
==============

EXT:solrfal 12.0 is the new release for TYPO3 12.4 and EXT:solr 12.0.

New in this release:
--------------------

!!![TASK] Replace signal-slot with event dispatcher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The previously available Extbase Signals have been removed from EXT:solrfal in favor of PSR-14 Events.

* The signal :php:`\ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory::emitAddedSolrFileInformation`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileInfoHasBeenAddedToDocumentEvent`
* The signal :php:`\ApacheSolrForTypo3\Solrfal\Indexing\DocumentFactory::emitFileMetaDataRetrieved`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileMetaDataHasBeenRetrievedEvent`
* The signal :php:`ApacheSolrForTypo3\Solrfal\Indexing\Indexer::addGroupDocumentsToIndex::emitIndexedFileToSolr`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterSingleFileDocumentOfItemGroupHasBeenIndexedEvent`
* The signal :php:`\ApacheSolrForTypo3\Solrfal\Queue\ItemRepository::emitItemRemovedFromQueue`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Repository\AfterFileQueueItemHasBeenRemovedEvent`
* The signal :php:`\ApacheSolrForTypo3\Solrfal\Queue\ItemRepository::emitMultipleItemsRemovedFromQueue`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Repository\AfterMultipleFileQueueItemsHaveBeenRemovedEvent`
* The signal :php:`\ApacheSolrForTypo3\Solrfal\Queue\ItemRepository::emitBeforeItemRemovedFromQueue`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeFileQueueItemHasBeenRemovedEvent::__construct`
* The signal :php:`\ApacheSolrForTypo3\Solrfal\Queue\ItemRepository::emitBeforeMultipleItemsRemovedFromQueue`
  has been replaced by :php:`\ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeMultipleFileQueueItemsHaveBeenRemovedEvent::__construct`


All changes:
~~~~~~~~~~~~

- [TASK] Prepare requirements for TYPO3 12 `d2a7f49 on @2023-06-23 <https://github.com/TYPO3-Solr/ext-solrfal/commit/d2a7f49>`_ (thanks to Rafael Kähm)
- [TASK] Integrate PHPStan in EXT:solfal :: initial `d644010 on @2023-07-13 <https://github.com/TYPO3-Solr/ext-solrfal/commit/d644010>`_ (thanks to Rafael Kähm)
- [BUGFIX] Set PSR-4 Namespaces properly `e891164 on @2023-07-01 <https://github.com/TYPO3-Solr/ext-solrfal/commit/e891164>`_ (thanks to Rafael Kähm)
- [TASK] add EXT:tika to require-dev, since PHPStan requires it `d102af3 on @2023-07-13 <https://github.com/TYPO3-Solr/ext-solrfal/commit/d102af3>`_ (thanks to Rafael Kähm)
- [TASK] Reuse EXT:solr cibuild.sh script `98d63a9 on @2023-07-17 <https://github.com/TYPO3-Solr/ext-solrfal/commit/98d63a9>`_ (thanks to Rafael Kähm)
- [TASK] Apply PHPStan level 3 and typo3-codings standards :: basics `44c7bd2 on @2023-07-01 <https://github.com/TYPO3-Solr/ext-solrfal/commit/44c7bd2>`_ (thanks to Rafael Kähm)
- [TASK] Apply PHPStan level 3 and typo3-codings standards :: basics 2 `162218d on @2023-07-17 <https://github.com/TYPO3-Solr/ext-solrfal/commit/162218d>`_ (thanks to Rafael Kähm)
- [TASK] Migrate fixtures from XML to CSV :: renaming part `a2ffc80 on @2023-07-17 <https://github.com/TYPO3-Solr/ext-solrfal/commit/a2ffc80>`_ (thanks to Rafael Kähm)
- [TASK] Make EXT:solrfal be module working `f30faa9 on @2023-08-04 <https://github.com/TYPO3-Solr/ext-solrfal/commit/f30faa9>`_ (thanks to Rafael Kähm)
- [TASK] Migrate fixtures from XML to CSV :: formating part `fadaa43 on @2023-07-17 <https://github.com/TYPO3-Solr/ext-solrfal/commit/fadaa43>`_ (thanks to Rafael Kähm)
- [FIX] Unit/Integration tests :: basics `82a67a3 on @2023-07-21 <https://github.com/TYPO3-Solr/ext-solrfal/commit/82a67a3>`_ (thanks to Rafael Kähm)
- [BUGFIX] Add `implements LoggerAwareInterface` to classes using LoggerAwareTrait `268c611 on @2023-07-28 <https://github.com/TYPO3-Solr/ext-solrfal/commit/268c611>`_ (thanks to Rafael Kähm)
- !!![TASK:T12] Replace signal-slot with event dispatcher `20cbc52 on @2023-07-21 <https://github.com/TYPO3-Solr/ext-solrfal/commit/20cbc52>`_ (thanks to Rafael Kähm)
- !!![TASK] Refactor page | record | storage context :: intetrates usage of core context `da84929 on @2023-08-06 <https://github.com/TYPO3-Solr/ext-solrfal/commit/da84929>`_ (thanks to Rafael Kähm)
- [TASK] Raise PHPStan to level 4 and fix its issues `bfe722a on @2023-08-11 <https://github.com/TYPO3-Solr/ext-solrfal/commit/bfe722a>`_ (thanks to Rafael Kähm)
-  [TASK:T12] Make new TCA type 'file' functional `f07912b on @2023-08-11 <https://github.com/TYPO3-Solr/ext-solrfal/commit/f07912b>`_ (thanks to Rafael Kähm)
- [TASK] Raise PHPStan to level 6 and fix its issues `2efcbb3 on @2023-08-11 <https://github.com/TYPO3-Solr/ext-solrfal/commit/2efcbb3>`_ (thanks to Rafael Kähm)
- [TASK] Raise PHPStan to level 7 and fix its issues `7ecdc56 on @2023-08-14 <https://github.com/TYPO3-Solr/ext-solrfal/commit/7ecdc56>`_ (thanks to Rafael Kähm)
- [TASK] Refactor postProcessIndexQueueInitialization hook to AfterIndexQueueHasBeenInitializedEvent listener `178821d on @2023-08-14 <https://github.com/TYPO3-Solr/ext-solrfal/commit/178821d>`_ (thanks to Rafael Kähm)
- [BUGFIX] Use AfterItemHasBeenIndexedEvent instead of its renamed version AfterIndexItemEvent `68cc885 on @2023-08-14 <https://github.com/TYPO3-Solr/ext-solrfal/commit/68cc885>`_ (thanks to Rafael Kähm)
- [BUGFIX] Regressions of : [TASK] Raise PHPStan to level 6 and fix its issues `111acc2 on @2023-08-14 <https://github.com/TYPO3-Solr/ext-solrfal/commit/111acc2>`_ (thanks to Rafael Kähm)
- [TASK] Refactor postProcessIndexQueueUpdateItem hook to AfterIndexQueueItemHasBeenMarkedForReindexing listener `d4840c7 on @2023-08-14 <https://github.com/TYPO3-Solr/ext-solrfal/commit/d4840c7>`_ (thanks to Rafael Kähm)
- [CI] Use the newest Composer 2.5.x `cfde511 on @2023-08-15 <https://github.com/TYPO3-Solr/ext-solrfal/commit/cfde511>`_ (thanks to Rafael Kähm)
- [TASK] Prepare releases for EXT:solrfal 12.0.0 `b78868a on @2023-08-15 <https://github.com/TYPO3-Solr/ext-solrfal/commit/b78868a>`_ (thanks to Rafael Kähm)
- [TASK] Streamline EXT:solrfal events to same naming `451b63d on @2023-08-15 <https://github.com/TYPO3-Solr/ext-solrfal/commit/451b63d>`_ (thanks to Rafael Kähm)
- [TASK] Include release 12 in release notes `e581327 on @2023-08-21 <https://github.com/TYPO3-Solr/ext-solrfal/commit/e581327>`_ (thanks to Markus Friedrich)
- [BUGFIX] Actions replace LOCAL_VOLUME_NAME with SOLR_VOLUME_NAME `a696be7 on @2023-09-22 <https://github.com/TYPO3-Solr/ext-solrfal/commit/a696be7>`_ (thanks to Rafael Kähm)
- [TASK] Fix PHP-CS for single_line_empty_body rule `ce5b689 on @2023-09-22 <https://github.com/TYPO3-Solr/ext-solrfal/commit/ce5b689>`_ (thanks to Rafael Kähm)
- [TASK] Sync with EXT:solr dev-main 2023.09.21 `d00e3f7 on @2023-09-21 <https://github.com/TYPO3-Solr/ext-solrfal/commit/d00e3f7>`_ (thanks to Rafael Kähm)
- [TASK] Enable page context detection on tt_content `9f96e3e on @2023-09-22 <https://github.com/TYPO3-Solr/ext-solrfal/commit/9f96e3e>`_ (thanks to Rafael Kähm)
- [BUGFIX] Sometimes the ctype seems to be empty `10ab740 on @2023-09-25 <https://github.com/TYPO3-Solr/ext-solrfal/commit/10ab740>`_ (thanks to Georg Ringer)
- [BUGFIX] pages_language_overlay table was still used for translated page attachments `1a57201 on @2023-10-06 <https://github.com/TYPO3-Solr/ext-solrfal/commit/1a57201>`_ (thanks to Rafael Kähm)
- [TASK] Add missing @throws on RecordContextDetector methods `6e34200 on @2023-10-13 <https://github.com/TYPO3-Solr/ext-solrfal/commit/6e34200>`_ (thanks to Rafael Kähm)
- [TASK] use Throwable instead of Exception and apply EXT:solr* inheritance in exceptions `fa4772b on @2023-10-13 <https://github.com/TYPO3-Solr/ext-solrfal/commit/fa4772b>`_ (thanks to Rafael Kähm)
- [TASK] Refresh README.md `e214c91 on @2023-10-13 <https://github.com/TYPO3-Solr/ext-solrfal/commit/e214c91>`_ (thanks to Rafael Kähm)

Contributors
============

- Benni Mack
- Georg Ringer
- Markus Friedrich
- Rafael Kähm
- Stefan Frömken

Thanks to everyone who helped in creating this release!

Also a big thanks to our partners that have joined the Apache Solr EB für TYPO3 12 LTS (Feature) program:

- .hausformat
- +Pluswerk AG
- 711media websolutions GmbH
- ACO Ahlmann SE & Co. KG
- AVM Computersysteme Vertriebs GmbH
- Ampack AG
- Amt der Oö Landesregierung
- Autorité des Marchés Financiers (Québec)
- b13 GmbH
- Beech IT
- Bytebetrieb GmbH & Co. KG
- CARL von CHIARI GmbH
- clickstorm GmbH Apache Solr EB für TYPO3 12 LTS (Feature)
- Connecta AG
- cosmoblonde GmbH
- cron IT GmbH
- CS2 AG
- cyperfection GmbH
- digit.ly
- DGB Rechtsschutz GmbH
- DMK E-BUSINESS GmbH
- DP-Medsystems AG
- DSCHOY GmbH
- Deutsches Literaturarchiv Marbach
- EB-12LTS-FEATURE
- F7 Media GmbH
- Fachagentur Nachwachsende Rohstoffe fnr.de
- Forte Digital Germany GmbH
- FTI Touristik GmbH
- gedacht
- Getdesigned GmbH
- GPM Deutsche Gesellschaft für Projektmanagement e. V.
- Groupe Toumoro inc
- HEAD acoustics GmbH
- helhum.io
- Hochschule Koblenz - Standort Remagen
- in2code GmbH
- Internezzo
- IW Medien GmbH
- jweiland.net
- Kassenärztliche Vereinigung Rheinland-Pfalz
- keeen GmbH
- KONVERTO AG
- Kreis Euskirchen
- Kwintessens B.V.
- L.N. Schaffrath DigitalMedien GmbH
- LOUIS INTERNET GmbH
- Leuchtfeuer Digital Marketing GmbH
- Lingner Consulting New Media GmbH
- Macaw Germany Cologne GmbH
- Marketing Factory Consulting GmbH
- mehrwert intermediale kommunikation GmbH
- morbihan.fr - Commande  BDC_99143_202404081250
- ochschule Furtwangen
- pietzpluswild GmbH
- plan2net GmbH
- ProPotsdam GmbH
- Québec.ca gouv.qc.ca Apache Solr EB für TYPO3 12 LTS (Feature)
- Rechnungshof Österreich
- Red Dot GmbH & Co. KG
- rocket-media GmbH & Co KG
- Sandstein Neue Medien GmbH
- Schoene neue kinder GmbH
- SIWA Online GmbH
- Snowflake Productions GmbH Apache Solr EB für TYPO3 12 LTS (Feature)
- Stadtverwaltung Villingen-Schwenningen
- Stämpfli AG
- Statistik Österreich
- studio ahoi - Weitenauer Schwardt GbR
- Südwestfalen IT
- Systime/Gyldendal A/S
- THE BRETTINGHAMS GmbH
- Typoheads GmbH
- UEBERBIT GmbH
- Universität Regensburg
- VisionConnect.de
- WACON Internet GmbH
- webconsulting business services gmbh
- werkraum Digitalmanufaktur GmbH
- WIND Internet BV
- XIMA MEDIA GmbH
- wow! solution
