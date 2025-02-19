.. include:: /Includes.rst.txt

================
Available Events
================

In case you need to adapt or extend the behaviour of solrfal the following events exist you may consume in your listeners.

Indexing
========

AfterFileInfoHasBeenAddedToDocumentEvent
----------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileInfoHasBeenAddedToDocumentEvent` is dispatched
after the file information are mapped to Apache Solr document belonging to processed file.
This event allows third party extensions to replace or modify the file document after the file info has been added to the document.

AfterFileMetaDataHasBeenRetrievedEvent
--------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterFileMetaDataHasBeenRetrievedEvent` is dispatched
after the (translated) MetaData record of a file is retrieved.
The listeners can take the MetaData array and may modify it.
The modified MetaData will then be hand over to the TypoScript "Service".
As an result fields added in the listener to that event can be addressed from your regular TypoScript setup.

AfterSingleFileDocumentOfItemGroupHasBeenIndexedEvent
-----------------------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Indexing\AfterSingleFileDocumentOfItemGroupHasBeenIndexedEvent` is dispatched
after a single Apache Solr document has been indexed.
This event allows third party extensions to react on properties provided by this event.

..  note::
    This event can not be used to modify provided properties.

Repository
==========

BeforeFileQueueItemHasBeenRemovedEvent
--------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeFileQueueItemHasBeenRemovedEvent` is dispatched
before the file-index-queue-item will be removed from queue.
This event allows third party extensions to react on properties provided by this event.

AfterFileQueueItemHasBeenRemovedEvent
-------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Repository\AfterFileQueueItemHasBeenRemovedEvent` is dispatched
after a single file-index-queue-item has been removed.

..  note::
    You can not fetch those items from database anymore, because they are really deleted.
    This event allows third party extensions to react on properties provided by this event.

BeforeMultipleFileQueueItemsHaveBeenRemovedEvent
------------------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Repository\BeforeMultipleFileQueueItemsHaveBeenRemovedEvent` is dispatched
before the list of file-index-queue-items is removed from index queue.
This event allows third party extensions to react on properties provided by this event.

AfterMultipleFileQueueItemsHaveBeenRemovedEvent
-----------------------------------------------

The event :php:`ApacheSolrForTypo3\Solrfal\Event\Repository\AfterMultipleFileQueueItemsHaveBeenRemovedEvent` is dispatched
after the list of file-index-queue-items is removed from index queue.
**Note:** You can not fetch those items from database anymore, because they are really deleted.
This event allows third party extensions to react on properties provided by this event.
