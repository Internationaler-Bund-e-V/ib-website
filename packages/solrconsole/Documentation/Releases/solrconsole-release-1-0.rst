.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

===========================================
Apache Solr for TYPO3 - Console tools 1.0.0
===========================================

We are happy to release EXT:solrconsole 1.0.0.

New in this release
===================

[TASK] Initial release of solrconsole.
--------------------------------------

Initial release of solrconsole.

Solrconsole provides a powerful CLI for EXT:solr to automate maintenance tasks

[TASK] Optimize ext_emconf.php
------------------------------

* Allow to install with TYPO3 9
* Remove dependency to scheduler

[TASK] Adjust composer dependencies
-----------------------------------


[TASK] Add solr:connection:update command
-----------------------------------------

[TASK] Add solr:connection:get command
--------------------------------------

Adds the command "solr:connection:get" including documentation and tests

[TASK] Add command solr:queue:reset-errors
------------------------------------------

[TASK] Add command solr:queue:index
-----------------------------------

Adds the commane solr:queue:index
Adds an integration test for a console workflow (init, index, progress, delete index, delete queue items)

[TASK] Fix spelling and headline of solr:index:delete command docs
------------------------------------------------------------------

[TASK] Update tests for current solr:index:* commands
-----------------------------------------------------

[TASK] Add exception handling
-----------------------------

[TASK] Update phpdoc
--------------------

[TASK] Apply formatting
-----------------------

[BUGFIX] Build should fail when unit tests fail
-----------------------------------------------

* Evaluates the exit code of php unit in the cibuild.sh script
* Marks the missing tests as incomplete

[TASK] Minor corrections to testing environment
-----------------------------------------------

[TASK] Remove double // in paths
--------------------------------

[TASK] Apply correct test names and format the test xml files
-------------------------------------------------------------

[TASK] Refactor SolrQueueDelete and SolrQueueGet command to implement loadOptions method
----------------------------------------------------------------------------------------

[TASK] Implement the command solr:index:delete
----------------------------------------------

[TASK] Refactor confimation abort check
---------------------------------------

[TASK] Fix variable type for command in SolrIndexGetCommandTest
---------------------------------------------------------------

[TASK] Fix options list for solr:index:get in docs
--------------------------------------------------

[TASK] Add missing phpdoc params
--------------------------------

[TASK] Remove unused field
--------------------------

[TASK] Remove :void from setLabel method
----------------------------------------

[BUGFIX] Fixes the description in the solr:queue:get command
------------------------------------------------------------

[TASK] Streamline confirmation output for the options lists
-----------------------------------------------------------

[TASK] Implement the command "solr:index:get"
---------------------------------------------

 [TASK] Add Jens Jacobsen to composer.json

[TASK] Implement the command "solr:queue:progress"
--------------------------------------------------

* Implements the command "solr:queue:progress" that can be used to render the progress
* Adds documentation and testcase

[TASK] Add public method to customize helper labels
---------------------------------------------------

[TASK] Add command "solr:queue:get"
-----------------------------------

[TASK] Finished command "solr:queue:delete"
-------------------------------------------

[TASK] Add first implementation for "solr:queue:delete"
-------------------------------------------------------

[TASK] Add documentation for "solr:queue:initialize"
----------------------------------------------------

[TASK] Implement command solr:queue:initialize
----------------------------------------------

[TASK] Added something to the readme
------------------------------------

[TASK] Initial commit
---------------------

Contributors
============

Like always this release would not have been possible without the help from our
awesome community. Here are the contributors to this release.

(patches, comments, bug reports, reviews, ... in alphabetical order)

* Jens Jacobsen
* Timo Hund

Also a big thanks to our partners that have joined the EB2018 program:

* Albervanderveen
* Amedick & Sommer
* AUSY SA
* bgm Websolutions GmbH
* Citkomm services GmbH
* Consulting Piezunka und Schamoni - Information Technologies GmbH
* Cows Online GmbH
* food media Frank Wörner
* FTI Touristik GmbH
* Hirsch & Wölfl GmbH
* Hochschule Furtwangen
* JUNGMUT Communications GmbH
* Kreis Coesfeld
* LOUIS INTERNET GmbH
* L.N. Schaffrath DigitalMedien GmbH
* Mercedes AMG GmbH
* Petz & Co
* Pluswerk AG
* ressourcenmangel an der panke GmbH
* Site'nGo
* Studio B12 GmbH
* systime
* Talleux & Zöllner GbR
* TOUMORO
* TWT Interactive GmbH

Special thanks to our premium EB 2018 partners:

* b13 http://www.b13.de/
* dkd http://www.dkd.de/
* jweiland.net http://www.jweiland.net/

Thanks to everyone who helped in creating this release!

Outlook
=======

In the next release we want to focus on the move to solarium and the support of the lastest Apache Solr version.

How to Get Involved
===================

There are many ways to get involved with Apache Solr for TYPO3:

* Submit bug reports and feature requests on [GitHub](https://github.com/TYPO3-Solr/ext-solr)
* Ask or help or answer questions in our [Slack channel](https://typo3.slack.com/messages/ext-solr/)
* Provide patches through Pull Request or review and comment on existing [Pull Requests](https://github.com/TYPO3-Solr/ext-solr/pulls)
* Go to [www.typo3-solr.com](http://www.typo3-solr.com) or call [dkd](http://www.dkd.de) to sponsor the ongoing development of Apache Solr for TYPO3

Support us in 2017 by becoming an EB partner:

http://www.typo3-solr.com/en/contact/

or call:

+49 (0)69 - 2475218 0