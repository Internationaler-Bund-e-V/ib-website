.. include:: /Includes.rst.txt

==========
How to use
==========


1. **Setup EXT:solr** >=3.0.0
    * configure page indexing and/or record indexing according to your needs
    * make sure you have the compatible EXT:solr version, please see  `version compatibility matrix <https://github.com/TYPO3-Solr/ext-solr/blob/master/Documentation/Appendix/VersionMatrix.rst>`_
    * EXT:solrfal depends on system extension "filemetadata", which will be activated automatically while activating the EXT:solrfal extension.

2. **Include the Static TypoScript** "Search - FAL File Indexing (solrfal)"

3. **Add scheduler task** for EXT:solrfal
    * The TASK "File Index Queue Worker" needs to be set up.

4. Check scheduler TASKs for FAL in Core
    * If you have external storages and/or it is possible that files change in a storage in ways, the TYPO3 backend is not involved it is mandatory to have the "File Abstraction Layer: Update storage index" running frequently (a.k.a every 5-10 minutes).
    * Solr uses the MetaData of FAL; if you have extractors for MetaData you also might run the "File Abstraction Layer: Extract metadata in storage" frequently.

5. Do your **individual TypoScript configuration**

6. **Setup EXT:tika** to enable search within file contents and metadata.