TypoScript Reference
--------------------

pageContext
~~~~~~~~~~~

:Type: Boolean
:TS Path: plugin.tx_solr.index.enableFileIndexing.pageContext
:Default: 1
:Function: enables indexing for files attached to Content-Element-Records while indexing the frontend pages

contentElementTypes
```````````````````

:Type: Array (key: CType from tt_content, value: fields in which file attachments should be extracted);
:TS Path: plugin.tx_solr.index.enableFileIndexing.pageContext.contentElementTypes
:Default: text => bodytext, header_link;  textpic =>bodytext, header_link; uploads => media
:Function: For different content element types you may want files detected in different fields. The default configuration is to behave like solrfile.

        If you have added a custom content element, you may want to configure fields here.

fileExtensions
``````````````

:Type: String, comma separated values (file extensions without .) or \*
:TS Path: plugin.tx_solr.index.enableFileIndexing.pageContext.fileExtensions
:Default: \*
:Function: allows to restrict the files being indexed by the file extension

enableFields
````````````

:Type: array (of column names in pages)
:TS Path: plugin.tx_solr.index.enableFileIndexing.pageContext.enableFields
:Allowed keys: endtime, accessGroups
:Function: Use the enableFields from the Page it is referenced at for file

contentEnableFields
```````````````````

:Type: array (of column names in pages)
:TS Path: plugin.tx_solr.index.enableFileIndexing.pageContext.contentEnableFields
:Allowed keys: accessGroups
:Function: Use the enableFields from the page content elements referencing the file

storageContext
~~~~~~~~~~~~~~

:Type: boolean (1/0);
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext
:Default: 0
:Function: enables indexing of all files in a storage

[StorageUid]
````````````

:Type: array
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext.[StorageUid]
:Index: affected StorageUid (f.e. fileadmin/ generally 1)
:Function: Enables a detailed indexing configuration per Storage, see properties for details

languages
`````````

:Type: string, comma separated values (integers list of sys_language uids)
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext.[StorageUid].languages
:Default: 0
:Function: define for which languages this storge should be indexed

fileExtensions
``````````````

:Type: string, comma separated values (file extensions without .) or \*
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext.[StorageUid].fileExtensions
:Default: \*
:Function: allows to restrict the files being indexed by the file extension

enableFields
````````````

:Type: array (of column names in sys_file_metadata)
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext.[StorageUid].enableFields
:Allowed keys: endtime, accessGroups
:Function: FAL generally does not have enable fields, but metadata ships fields which can be used for that purpose. With this configuration you define "enableFields" just for indexation.

folders
```````

:Type: string, comma separated values
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext.[StorageUid].folders
:Default: \*
:Function: List of valid directories, relative to storage root directory
:Since: 3.1


excludeFolders
``````````````

:Type: string, comma separated values
:TS Path: plugin.tx_solr.index.enableFileIndexing.storageContext.[StorageUid].excludeFolders
:Default:
:Function: List of directories to exclude, relative to storage root directory
:Since: 3.1

recordContext
~~~~~~~~~~~~~

:Type: boolean
:TS Path: plugin.tx_solr.index.enableFileIndexing.recordContext
:Default: 1
:Function: enables indexing of all file attachment at records;

    needs further configuration in the index queue: a, table needs to indexed at all, b, attachment indexation needs to be activated for that table


attachments
~~~~~~~~~~~

:Type: boolean
:TS Path: plugin.tx_solr.index.queue.[indexingConfiguration].attachments
:Default: 0
:Function: enables file attachment detection when indexing the record


fields
~~~~~~

:Type: string, comma separated values (column names of tables)
:TS Path: plugin.tx_solr.index.queue.[indexingConfiguration].attachments.fields
:Default: \*
:Function: define in which columns of an record files should be detected

plugin.tx_solr.index.queue._FILES
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Configuration array to configure the index.queue processing for files. The configuration will be merged.
This means that every context specific configuration inherits the default configuration. In addition, if there is a special configuration within context (like per table or storage) these will inherit the base configuration of the context. Each configuration is to be defined like plugin.tx_solr.index.queue.[indexingConfiguration].fields

default
~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.default
:Function: Mapping of Solr field names on the left side to database table field names or content objects on the right side. Used for every file indexed
:Default:

.. code-block:: typoscript

    title = title
    description = description
    altText_stringS = alternative
    width_intS = width
    height_intS = height

    category_stringM = SOLR_RELATION
    category_stringM {
    	localField = categories
    	foreignLabelField = uid
    	enableRecursiveValueResolution = 1
    	multiValue = 1
    }

pageContext
~~~~~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.pageContext
:Function: Additional mapping of Solr field names on the left side to database table field names or content objects on the right side. Used for every file indexed in pageContext


storageContext
~~~~~~~~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.storageContext
:Default: empty
:Function: See introduction and following two entries.

default
~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.storageContext.default
:Function: Additional mapping of Solr field names on the left side to database table field names or content objects on the right side. Used for every file indexed in storageContext


[StorageUid]
~~~~~~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.storageContext.[StorageUid]
:Function: Additional mapping of Solr field names on the left side to database table field names or content objects on the right side. Used for every file of Storage [StorageUid] indexed in storageContext


recordContext
~~~~~~~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.recordContext
:Default: empty
:Function: See introduction and following two entries.


default
~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.recordContext.default
:Function: Additional mapping of Solr field names on the left side to database table field names or content objects on the right side. Used for every file indexed  as attachment in recordContext

[__tablename__]
~~~~~~~~~~~~~~~

:Type: array
:TS Path: plugin.tx_solr.index.queue._FILES.recordContext.[__tablename__]
:Function: Additional mapping of Solr field names on the left side to database table field names or content objects on the right side. Used for every file found attached to record of  [__tablename__] indexed in recordContext
