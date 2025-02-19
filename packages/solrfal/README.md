Apache Solr for TYPO3 - Indexing the File Abstraction Layer
============================================================


This repository hosts the TYPO3 CMS Extension "solrfal". EXT:solrfal is an add-on to EXT:solr with the purpose of file
indexing using the File Abstraction Layer of TYPO3 CMS.

How to run the Tests
--------------------

**Please note, the integration tests require deep setup,
which is already available within our [Solr-DDEV-site environment](https://github.com/TYPO3-Solr/solr-ddev-site).
and can be run after enabling this addon.**

Please refer to the README on [Solr-DDEV-site environment](https://github.com/TYPO3-Solr/solr-ddev-site/)


EXT:solrfal tests and suites for other environments(CI)
-------------------------------------------------------

EXT:solrfal provides following test and suites:

```bash
 t3
  t3:docs:build
  t3:docs:build:prod
  t3:docs:clean
  t3:standards:check
  t3:standards:fix
 tests
  tests:env
  tests:integration
  tests:lint-php
  tests:lint-xml
  tests:phpstan
  tests:restore-git
  tests:setup
  tests:setup:global-require
  tests:unit
```
