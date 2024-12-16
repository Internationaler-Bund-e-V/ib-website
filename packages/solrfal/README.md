Apache Solr for TYPO3 - Indexing the File Abstraction Layer
============================================================


This repository hosts the TYPO3 CMS Extension "solrfal". EXT:solrfal is an add-on to EXT:solr with the purpose of file
indexing using the File Abstraction Layer of TYPO3 CMS. This extension - as of branch 3.0 - requires TYPO3 CMS 7.6 LTS
and EXT:solr in version 4.0.0.

Comparison to solrfile
------------------------

1. The Status report has not been migrated since the requirements, which are checked there, are already perequesites for
the File Abstraction Layer and therefore TYPO3 itself since TYPO3 CMS 6.0

2. In addition to solrfile, the Garbage-collection also is registered to the postDelete Signal.

3. solrfal supports only indexed files in defined storages; "classic file fields" placing files in uploads are not supported anymore


How to run the UnitTests
------------------------

First you need to set some environment variables and boostrap the system with the bootstrap script (you only need to do this once):

```bash
chmod u+x ./Build/Test/*.sh
source ./Build/Test/bootstrap.sh --local
```

Now you can run the complete test suite:

```bash
./Build/Test/cibuild.sh
```


Breaking changes & Update instructions
----------------

### Version 3.0.0

- In versions below 3.0.0 items in die filelist have been queued without the index queue configuration.
This was fixed in version 3.0.0. If there are leftovers in the queue without an index configuration name, you should
delete them.

### Version 2.1.0

- Breaking change from 08.07.2014 reverted
The change from summer 2014 has been replaced by an solution with less side affects and increased
usability (no need to configure the storage indexation at two different places).
You should remove the additional configuration again.

- Extension now able to handle indexing configurations correctly
  The RecordContext was not able to deal with indexing configuratons correctly
  when the name did not equal the table name.
  In order to fix this database schema was changed and a full purge of all RecordContext
  files is needed. Please run the upgrade wizard in the extension manager.

### Version 2.0.1

- TypoScript option renamed (dc48ef4d47f1027666c0cdf00790b6ce85e90d90)
In ext:solrfile the queue-option for attachment-detection on indexed records was named correctly.
While developing EXT:solrfal a typo slipped in, renaming that option to "attachement".
This typo is now fixed. You need to adapt your TypoScript configuration, since only the correct version will be recognized.

- Reverted 8fea1fc3444d5e1c58c92d4fc183702cecaf9156 fileReferenceUrl special treatment.
  As a replacement it is now possible to define fieldConfigurations which are executed with
  data of the original record instead of the file meta data. Within the fieldConfiguration there now
  is an additional Scope \_\_RecordContext which allows to manually fill fields like fileReferenceUrl and fileReferenceTitle.
  See example in TypoScript-Setup.
  If this section is not configured (__RecordContext is not set at all) then it will be created automatically and fields
  "title" and "url" of the original record will be taken as "fileReferenceTitle" and "fileReferenceUrl".
  **NOTE:** As soon as you configure at least one field in __RecordContext you also manually need to copy/reference the
  configuration for "fileReferenceTitle" and "fileReferenceUrl"

### Revision 4f78d57c33c1f3318c2bce1bcdc65441b6d73b17 (08.07.2014)

- File-Storages are only indexed (queue-initialization) if they are configured additionally as normal indexation queue.
Have a look at Configuration/TypoScript/Basic/setup.typoscript for an example.
