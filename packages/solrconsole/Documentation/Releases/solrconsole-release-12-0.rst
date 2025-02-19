.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -**   coding: utf-8 -**   with BOM.

.. include:: ../Includes.txt

.. _releases_12-0:

==========================================
Apache Solr for TYPO3 - Console tools 12.0
==========================================

Release 12.0.1
==============

This is a maintenance release for TYPO3 12.4 LTS, containing:

*   [TASK] Simplify and automate release via CI by @dkd-friedrich
*   [TASK] Add tests for documentation by @dkd-friedrich
*   [TASK] Allow PHP 8.3+ by @dkd-friedrich

Release 12.0.0
==============

We are happy to release EXT:solrconsole 12.0.0.
The focus of this release was the compatibility with TYPO3 12 LTS and EXT:solr v12.x.

New in this release
-------------------

Support of TYPO3 12 LTS
"""""""""""""""""""""""

With EXT:solrconsole 12.0 we provide the support of TYPO3 12 LTS and EXT:solr 12.


Improved solrfal integration
""""""""""""""""""""""""""""

Commands related to solrfal are no longer registered if solrfal is not available, this avoids confusions and keeps
the list of available commands simple.


TYPO3 testing framework 8 and PHPUnit 10
""""""""""""""""""""""""""""""""""""""""

Test coverage is an important component in our eyes. With solrconsole 12 we rely on the TYPO3 testing framework 8 and PHPUnit 10, which already prepares us to a certain extent for the upcoming TYPO3 13.


SolrIndexDeleteCommand considers all languages by default
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Since version 12 the SolrIndexDeleteCommand command considers the all languages by default, you have to use the languages option to restrict to a certain language. With option "languages" set to "0" the
behaviour will match prior versions.

Contributors
============

Like always this release would not have been possible without the help from our
awesome community. Here are the contributors to this release.

(patches, comments, bug reports, reviews, ... in alphabetical order)

*  Jens Jacobsen
*   Markus Friedrich
*   Rafael Kähm

Also a big thanks to our partners that have joined the Apache Solr EB für TYPO3 12 LTS (Feature) program:

*   .hausformat
*   711media websolutions GmbH
*   ACO Ahlmann SE & Co. KG
*   AVM Computersysteme Vertriebs GmbH
*   Ampack AG
*   Amt der Oö Landesregierung
*   Autorité des Marchés Financiers (Québec)
*   b13 GmbH
*   Beech IT
*   CARL von CHIARI GmbH
*   clickstorm GmbH Apache Solr EB für TYPO3 12 LTS (Feature)
*   Connecta AG
*   cosmoblonde GmbH
*   cron IT GmbH
*   CS2 AG
*   cyperfection GmbH
*   digit.ly
*   DMK E-BUSINESS GmbH
*   DP-Medsystems AG
*   DSCHOY GmbH
*   Deutsches Literaturarchiv Marbach
*   EB-12LTS-FEATURE
*   F7 Media GmbH
*   Forte Digital Germany GmbH
*   FTI Touristik GmbH
*   gedacht
*   GPM Deutsche Gesellschaft für Projektmanagement e. V.
*   Groupe Toumoro inc
*   HEAD acoustics GmbH
*   helhum.io
*   Hochschule Koblenz *   Standort Remagen
*   in2code GmbH
*   Internezzo
*   IW Medien GmbH
*   jweiland.net
*   keeen GmbH
*   KONVERTO AG
*   Kassenärztliche Vereinigung Rheinland-Pfalz
*   Kreis Euskirchen
*   L.N. Schaffrath DigitalMedien GmbH
*   LOUIS INTERNET GmbH
*   Leuchtfeuer Digital Marketing GmbH
*   Lingner Consulting New Media GmbH
*   Macaw Germany Cologne GmbH
*   Marketing Factory Consulting GmbH
*   mehrwert intermediale kommunikation GmbH
*   morbihan.fr *   Commande  BDC_99143_202404081250
*   ochschule Furtwangen
*   pietzpluswild GmbH
*   plan2net GmbH
*   ProPotsdam GmbH
*   Québec.ca gouv.qc.ca Apache Solr EB für TYPO3 12 LTS (Feature)
*   Red Dot GmbH & Co. KG
*   rocket-media GmbH & Co KG
*   Schoene neue kinder GmbH
*   Snowflake Productions GmbH Apache Solr EB für TYPO3 12 LTS (Feature)
*   Stadtverwaltung Villingen-Schwenningen
*   Stämpfli AG
*   studio ahoi *   Weitenauer Schwardt GbR
*   Systime/Gyldendal A/S
*   THE BRETTINGHAMS GmbH
*   Typoheads GmbH
*   UEBERBIT GmbH
*   Universität Regensburg
*   VisionConnect.de
*   WACON Internet GmbH
*   webconsulting business services gmbh
*   werkraum Digitalmanufaktur GmbH
*   WIND Internet BV
*   XIMA MEDIA GmbH
*   wow! solution
