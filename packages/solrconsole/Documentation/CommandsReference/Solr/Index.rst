.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.txt

.. _commands_solr_index:

Solr Index
==========

This section describes all solr index related commands("solr:index:\*")

.. contents::
   :local:

solr:index:get
--------------

:Command: solr:index:get
:Options: --sites, --types, --uids, --ids, --language, --no-interaction, --page, --per-page
:Since: 1.0

The command "solr:index:get" allows you to retrieve items from the solr index to see the stored data.

By default it retrieves **all** items from the given core defined by the language you selected (defaults to the site's default language).

**Options**:

+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| Options          | Shortcut | Default          | Description                                  | Example                                                    |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --sites          | -s       | all sites        | Comma separated list of siteroot uids.       | -s 1,4                                                     |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --types          | -t       | all types        | Comma separated list of types(tables).       | -t pages,tx_news_domain_model_news                         |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --uids           | -u       | all uids         | Comma separated list of record uids          | -u 4711                                                    |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --ids            | -i       | all document ids | Comma separated list of document ids.        | -i ca4dd635f094303a334959993f97549867f24d62/pages/25/0/1/0 |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --language       | -L       | 0                | Language uid                                 | -L 1                                                       |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --page           |          | page of output   | The page                                     | --page 2                                                   |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --per-page       |          | count per page   | Items per page that should be shown          | --per-page 20                                              |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --no-interaction | -n       | 0                | Skips all confirmations, e.g for automation. | -n                                                         |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+

.. code-block:: bash

   typo3cms solr:index:get -t tx_news_domain_model_news --page 2 --per-page 20


solr:index:delete
-----------------

:Command: solr:index:delete
:Options: --sites, --types, --uids, --ids, --languages, --no-interaction
:Since: 1.0

   The command "solr:index:delete" allows you to delete items from the solr index.

   By default it deletes **all** items from the given core defined by the languages you selected (defaults to all of the site's languages).

**Options**:

+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| Options          | Shortcut | Default          | Description                                  | Example                                                    |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --sites          | -s       | all sites        | Comma separated list of siteroot uids.       | -s 1,4                                                     |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --types          | -t       | all types        | Comma separated list of types(tables).       | -t pages,tx_news_domain_model_news                         |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --uids           | -u       | all uids         | Comma separated list of record uids          | -u 4711                                                    |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --ids            | -i       | all document ids | Comma separated list of document ids.        | -i ca4dd635f094303a334959993f97549867f24d62/pages/25/0/1/0 |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --languages      | -L       | all languages    | Comma separated list of language uids.       | -L 0,1                                                     |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+
| --no-interaction | -n       | 0                | Skips all confirmations, e.g for automation. | -n                                                         |
+------------------+----------+------------------+----------------------------------------------+------------------------------------------------------------+

.. code-block:: bash

   typo3cms solr:index:delete -t pages -u 45,42


solr:index:verify
-----------------

:Command: solr:index:verify
:Options: --sites, --configurations, --languages, --no-interaction, --fix
:Since: 2.0.0

   The command "solr:index:verify" allows you to verify items from the solr index with records from the database table.

   By default it shows differences for the given types from the given core defined by the languages you selected (defaults to all of the site's languages).

**Options**:

+------------------+----------+--------------------+----------------------------------------------+---------+
| Options          | Shortcut | Default            | Description                                  | Example |
+------------------+----------+--------------------+----------------------------------------------+---------+
| --sites          | -s       | all sites          | Comma separated list of siteroot uids.       | -s 1,4  |
+------------------+----------+--------------------+----------------------------------------------+---------+
| --configurations | -c       | all configurations | Comma separated list of queue configuration. | -c news |
+------------------+----------+--------------------+----------------------------------------------+---------+
| --languages      | -L       | all languages      | Comma separated list of language uids.       | -L 0,1  |
+------------------+----------+--------------------+----------------------------------------------+---------+
| --no-interaction | -n       | 0                  | Skips all confirmations, e.g for automation. | -n      |
+------------------+----------+--------------------+----------------------------------------------+---------+
| --fix            | -F       |                    | Fix differences between solr and TYPO3       | -F      |
+------------------+----------+--------------------+----------------------------------------------+---------+

.. code-block:: bash

   typo3cms solr:index:verify -c news
