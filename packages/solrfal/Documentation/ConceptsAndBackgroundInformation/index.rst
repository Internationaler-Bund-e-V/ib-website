.. include:: /Includes.rst.txt

===================================
Concepts and Background information
===================================

Indexing contexts
-----------------

A context describes the "place" where a file is detected to be indexed.

EXT:solrfal knows three contexts of indexing.

- **PageContext**: files which are found during indexation of a frontend-page
- **RecordContext**:files which are attached to records (which are indexed by solr)
- **StorageContext**: files which just reside in a file-system of a storage

Site exclusive records
----------------------

A file in fal can be referenced in any record across multiple sites. Therefore a contexts are checked for changes.
In some cases we know, that a change will only effect the contexts of the current site, because the are only related to the current site by nature
(e.g. pages, tt_content, sys_files_references). When you want to configure tables to be treated the same way, you can configure the tables in "siteExclusiveRecordTables".


Schema fields for files in Solr
-------------------------------

EXT:solr extends the schema with file specific attributes which are automatically added to the solr document on indexing. See the following list of available and indexed fields:

- **fileStorage**: uid of the Storage the file resides in
- **fileUid**: uid of the file
- **fileMimeType**: the mimetype of the file
- **fileName**: filename (including extensions)
- **fileSize**: file size in bytes
- **fileExtension**: file extension (without .)
- **fileSha1**: content hash of the file (SHA1),
- **filePublicUrl**: publicUrl of the file object
- **fileReferenceType**: table name from which the file was referenced (e.g. tt_content, or tx_new)
- **fileReferenceUid**: uid of the record the file was referenced from
