.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.txt

.. _commands_solr_queue:

Solr Queue
==========

This section describes all solr queue related commands("solr:queue:\*")

.. contents::
   :local:

solr:queue:delete
-----------------

:Command: solr:queue:delete
:Options: --sites, --configurations, --item-types, --item-uids, --uids, --no-interaction
:Since: 1.0

The command "solr:queue:delete" allows you to delete items from the index queue.

By default it deletes **all** items from the queue. The deleted items can be filtered by passing the filter options.

**Note**: This command only deletes the item from the index queue, not from the solr index itself. This needs to
be done with the command "solr:index:delete".

**Options**:

+------------------+----------+--------------------+----------------------------------------------+------------+
| Options          | Shortcut | Default            | Description                                  | Example    |
+------------------+----------+--------------------+----------------------------------------------+------------+
| --sites          | -s       | all sites          | Comma separated list of siteroot uids.       | -s 1,4     |
+------------------+----------+--------------------+----------------------------------------------+------------+
| --configurations | -c       | all configurations | Comma separated list of queue configuration. | -c mypages |
+------------------+----------+--------------------+----------------------------------------------+------------+
| --item-types     | -t       | all types          | Comma separated list of item types(tables).  | -t pages   |
+------------------+----------+--------------------+----------------------------------------------+------------+
| --item-uids      | -i       | all item uids      | Comma separated list of types(db tables).    | -i 11      |
+------------------+----------+--------------------+----------------------------------------------+------------+
| --uids           | -u       | all uids           | Comma separated list of queue item uids      | -u 4711    |
+------------------+----------+--------------------+----------------------------------------------+------------+
| --no-interaction | -n       | 0                  | Skips all confirmations, e.g for automation. | -n         |
+------------------+----------+--------------------+----------------------------------------------+------------+

.. code-block:: bash

   typo3cms solr:queue:delete -t tx_news_domain_model_news -i 12



solr:queue:get
--------------

:Command: solr:queue:get
:Options: --sites, --configurations, --item-types, --item-uids, --uids, --no-interaction, --page, --per-page
:Since: 1.0

The command "solr:queue:get" allows you to retrieve items from the queue to see their data and state.

By default it retrieves **all** items from the queue.

**Options**:

+------------------+----------+--------------------+----------------------------------------------+---------------+
| Options          | Shortcut | Default            | Description                                  | Example       |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --sites          | -s       | all sites          | Comma separated list of siteroot uids.       | -s 1,4        |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --configurations | -c       | all configurations | Comma separated list of queue configuration. | -c mypages    |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --item-types     | -t       | all types          | Comma separated list of item types(tables).  | -t pages      |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --item-uids      | -i       | all item uids      | Comma separated list of types(db tables).    | -i 11         |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --uids           | -u       | all uids           | Comma separated list of queue item uids      | -u 4711       |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --page           |          | page of output     | The page                                     | --page 2      |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --per-page       |          | count per page     | Items per page that should be shown          | --per-page 20 |
+------------------+----------+--------------------+----------------------------------------------+---------------+
| --no-interaction | -n       | 0                  | Skips all confirmations, e.g for automation. | -n            |
+------------------+----------+--------------------+----------------------------------------------+---------------+

.. code-block:: bash

   typo3cms solr:queue:get -t tx_news_domain_model_news --page 2 --per-page 20


solr:queue:index
----------------

:Command: solr:queue:index
:Options: --sites, --count, --no-interaction

The command "solr:queue:index" allows you to process the index queue and index a given amount of items to the solr index.


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


solr:queue:initialize
---------------------

:Command: solr:queue:initialize
:Options: --sites, --configurations, --no-interaction
:Since: 1.0

The command "solr:queue:initialize" allows you to initialize the index queue for a set of sites and index queue configurations.

By default it initializes the queue for **all** sites and **all** configurations, by settings *--sites* or *--configurations* you can limit the initialized items.

**Options**:

+------------------+----------+--------------------+----------------------------------------------+----------+
| Options          | Shortcut | Default            | Description                                  | Example  |
+------------------+----------+--------------------+----------------------------------------------+----------+
| --sites          | -s       | all sites          | Comma separated list of siteroot uids.       | -s 1,4   |
+------------------+----------+--------------------+----------------------------------------------+----------+
| --configurations | -c       | all configurations | Comma separated list of queue configuration. | -c pages |
+------------------+----------+--------------------+----------------------------------------------+----------+
| --no-interaction | -n       | 0                  | Skips all confirmations, e.g for automation. | -n       |
+------------------+----------+--------------------+----------------------------------------------+----------+


Example (Add's all items from the news configuration of site 1 without confirmation to the index queue):

.. code-block:: bash

   typo3cms solr:queue:initialize -s 1 -c news -n


solr:queue:progress
-------------------

:Command: solr:queue:initialize
:Options: --sites
:Since: 1.0

Shows the indexing progress of several sites on the command line.


**Options**:

+------------------+----------+-----------+----------------------------------------------+---------+
| Options          | Shortcut | Default   | Description                                  | Example |
+------------------+----------+-----------+----------------------------------------------+---------+
| --sites          | -s       | all sites | Comma separated list of siteroot uids.       | -s 1,4  |
+------------------+----------+-----------+----------------------------------------------+---------+
| --no-interaction | -n       | 0         | Skips all confirmations, e.g for automation. | -n      |
+------------------+----------+-----------+----------------------------------------------+---------+

solr:queue:reset-errors
-----------------------

:Command: solr:queue:reset-errors
:Options: --sites
:Since: 1.0

Resets the error flag for items in the queue. When the error flag was reset, the item will be indexed again.


**Options**:

+------------------+----------+-----------+----------------------------------------------+---------+
| Options          | Shortcut | Default   | Description                                  | Example |
+------------------+----------+-----------+----------------------------------------------+---------+
| --sites          | -s       | all sites | Comma separated list of siteroot uids.       | -s 1,4  |
+------------------+----------+-----------+----------------------------------------------+---------+
| --no-interaction | -n       | 0         | Skips all confirmations, e.g for automation. | -n      |
+------------------+----------+-----------+----------------------------------------------+---------+
