.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.txt

.. _commands__referencesolrfal_queue:

Solrfal Queue
=============

This section describes all solrfal queue related commands("solrfal:queue:\*")

.. contents::
   :local:

solrfal:queue:delete
--------------------

:Command: solrfal:queue:delete
:Options: --sites, --configurations, --context-names, --item-uids, --uids, --languages, --no-interaction
:Since: 2.0

The command "solrfal:queue:delete" allows you to delete items from the file index queue.

By default it deletes **all** items from the file queue. The deleted items can be filtered by passing the filter options.

**Note**: This command only deletes the item from the file index queue, not from the solr index itself. This needs to
be done with the command "solr:index:delete".

**Options**:

+------------------+----------+--------------------+----------------------------------------------------------+------------+
| Options          | Shortcut | Default            | Description                                              | Example    |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --sites          | -s       | all sites          | Comma separated list of siteroot uids.                   | -s 1,4     |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --configurations | -c       | all configurations | Comma separated list of queue configuration.             | -c mypages |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --context-names  | -cn      | all contexts       | Comma separated list of contexts (page, record, storage) | -cn record |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --item-uids      | -i       | all item uids      | Comma separated list of types(db tables).                | -i 11      |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --uids           | -u       | all uids           | Comma separated list of queue item uids                  | -u 4711    |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --languages      | -l       | all language uids  | Comma separated list of language uids                    | -l 1       |
+------------------+----------+--------------------+----------------------------------------------------------+------------+
| --no-interaction | -n       | 0                  | Skips all confirmations, e.g for automation.             | -n         |
+------------------+----------+--------------------+----------------------------------------------------------+------------+

.. code-block:: bash

   typo3cms solrfal:queue:delete -l 1 -s 2

solrfal:queue:get
-----------------

:Command: solrfal:queue:get
:Options: --sites, --configurations, --context-names, --item-uids, --uids, --languages, --no-interaction, --page, --per-page
:Since: 2.0

The command "solrfal:queue:get" allows you to retrieve items from the file index queue to see their data and state.

By default it retrieves **all** items from the queue.

**Options**:

+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| Options          | Shortcut | Default            | Description                                              | Example       |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --sites          | -s       | all sites          | Comma separated list of siteroot uids.                   | -s 1,4        |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --configurations | -c       | all configurations | Comma separated list of queue configuration.             | -c mypages    |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --context-names  | -cn      | all contexts       | Comma separated list of contexts (page, record, storage) | -cn record    |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --item-uids      | -i       | all item uids      | Comma separated list of types(db tables).                | -i 11         |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --uids           | -u       | all uids           | Comma separated list of queue item uids                  | -u 4711       |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --page           |          | page of output     | The page                                                 | --page 2      |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --per-page       |          | count per page     | Items per page that should be shown                      | --per-page 20 |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --languages      | -l       | all language uids  | Comma separated list of language uids                    | -l 1          |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+
| --no-interaction | -n       | 0                  | Skips all confirmations, e.g for automation.             | -n            |
+------------------+----------+--------------------+----------------------------------------------------------+---------------+

.. code-block:: bash

   typo3cms solrfal:queue:get -s 1 --page 1 --per-page 20

solrfal:queue:index
-------------------

:Command: solrfal:queue:index
:Options: --sites, --count, --no-interaction
:Since: 2.0

The command "solrfal:queue:index" allows you to process the file index queue and index a given amount of items to the solr index.

**Options**:

+------------------+----------+-----------+----------------------------------------------+---------+
| Options          | Shortcut | Default   | Description                                  | Example |
+------------------+----------+-----------+----------------------------------------------+---------+
| --sites          | -s       | all sites | Comma separated list of siteroot uids.       | -s 1,4  |
+------------------+----------+-----------+----------------------------------------------+---------+
| --amount         | -a       | 10        | Amount of items that should be index.        | -a 100  |
+------------------+----------+-----------+----------------------------------------------+---------+
| --no-interaction | -n       | 0         | Skips all confirmations, e.g for automation. | -n      |
+------------------+----------+-----------+----------------------------------------------+---------+

solrfal:queue:progress
----------------------

:Command: solrfal:queue:initialize
:Options: --sites
:Since: 2.0

Shows the file indexing progress of several sites on the command line.

**Options**:

+------------------+----------+-----------+----------------------------------------------+---------+
| Options          | Shortcut | Default   | Description                                  | Example |
+------------------+----------+-----------+----------------------------------------------+---------+
| --sites          | -s       | all sites | Comma separated list of siteroot uids.       | -s 1,4  |
+------------------+----------+-----------+----------------------------------------------+---------+
| --no-interaction | -n       | 0         | Skips all confirmations, e.g for automation. | -n      |
+------------------+----------+-----------+----------------------------------------------+---------+

solrfal:queue:reset-errors
--------------------------

:Command: solrfal:queue:reset-errors
:Options: --sites
:Since: 2.0

Resets the error flag for items in the file index queue. When the error flag was reset, the item will be indexed again.

**Options**:

+------------------+----------+-----------+----------------------------------------------+---------+
| Options          | Shortcut | Default   | Description                                  | Example |
+------------------+----------+-----------+----------------------------------------------+---------+
| --sites          | -s       | all sites | Comma separated list of siteroot uids.       | -s 1,4  |
+------------------+----------+-----------+----------------------------------------------+---------+
| --no-interaction | -n       | 0         | Skips all confirmations, e.g for automation. | -n      |
+------------------+----------+-----------+----------------------------------------------+---------+
