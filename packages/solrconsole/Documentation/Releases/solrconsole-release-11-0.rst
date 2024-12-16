.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

============================================
Apache Solr for TYPO3 - Console tools 11.0.0
============================================

We are happy to release EXT:solrconsole 11.0.0.
The focus of this release was the compatibility with TYPO3 11 LTS and EXT:solr v11.5.x.

New in this release
===================

[FEATURE] Verbose and non verbose output for \*:queue:progress commands
------------------------------------------------------------------------

This feature makes the outpup of queue:progress less verbose and more concise by default.

..  code-block:: bash

   Site 1 (solr-ddev-site.ddev.site) ERRORS:0
     0/77 [>ooooooooooooooooooooooooooo]   0%

   Site 2 (2.solr-ddev-site.ddev.site) ERRORS:0
     0/34 [>ooooooooooooooooooooooooooo]   0%


[TASK] Fix linter issues :: part 1
----------------------------------

[BUGFIX] Fix type hinting issues not covered by tests
-----------------------------------------------------

[BUGFIX] queue:progress output for multiple sites mixing domains with previous site
-----------------------------------------------------------------------------------

The output of the queue:progress command is now more clearer and does not confuse users by mixing the attributes of sites.

[BUGFIX] Fix CI configuration
-----------------------------

Adapts and fixes the CI configuration, GitHub actions are replacing Travis CI.

[TASK] Standardize \*.php files header declaration
--------------------------------------------------

Since TYPO3 6.2 TYPO3 uses and recommends a simplified copyright notice, we should update our copyright notices as well. This commit updates the old copyright format, to ensure the new format is used. According to the TYPO3 coding guidelines
the copyright notice should be placed before the namespace declaration. This commit adapts all declarations to ensure the right order.

Additionally needless namespace includes were removed and some test class names corrected.

[TASK] Introduce service yaml
-----------------------------

Introduce new service configuration for console commands. This replaces the old configuration file 'Commands.php'

Contributors
============

Like always this release would not have been possible without the help from our
awesome community. Here are the contributors to this release.

(patches, comments, bug reports, reviews, ... in alphabetical order)

* Lars Tode
* Markus Friedrich
* Rafael Kähm

Also a big thank you to our partners who have already concluded one of our new development participation packages such as Apache Solr EB for TYPO3 11 LTS (Feature), Apache Solr EB for TYPO3 10 LTS (Maintenance)
or Apache Solr EB for TYPO3 9 ELTS (Extended):

* .hausformat GmbH
* ACO Ahlmann SE & Co. KG
* avenit AG
* b13 GmbH
* Cobytes B.V.
* cyperfection GmbH
* Earlybird GmbH & Co KG
* elancer-team GmbH
* GFE Media GmbH
* Hochschule Niederrhein
* in2code GmbH
* internezzo ag
* Intersim AG
* IW Medien GmbH
* L.N. Schaffrath DigitalMedien GmbH
* Leitgab Gernot
* LOUIS INTERNET GmbH
* Marketing Factory Consulting GmbH
* medien.de mde GmbH
* MEDIA::ESSENZ
* mehrwert intermediale kommunikation GmbH
* Neue Medien GmbH
* Provitex GmbH
* Proud Nerds
* Québec.ca
* SITE'NGO
* Stämpfli AG
* Studio 9 GmbH
* TOUMORØ
* we.byte GmbH
* wegewerk GmbH
* werkraum Digitalmanufaktur GmbH
* WIND Internet

How to Get Involved
===================

There are many ways to get involved with Apache Solr for TYPO3:

* Submit bug reports and feature requests on [GitHub](https://github.com/TYPO3-Solr/ext-solr)
* Ask or help or answer questions in our [Slack channel](https://typo3.slack.com/messages/ext-solr/)
* Provide patches through Pull Request or review and comment on existing [Pull Requests](https://github.com/TYPO3-Solr/ext-solr/pulls)
* Go to [www.typo3-solr.com](http://www.typo3-solr.com) or call [dkd](http://www.dkd.de) to sponsor the ongoing development of Apache Solr for TYPO3

Support us by becoming an EB partner:

https://shop.dkd.de/Produkte/Apache-Solr-fuer-TYPO3/

or call:

+49 (0)69 - 2475218 0
