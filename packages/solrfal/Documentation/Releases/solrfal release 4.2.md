### Version 4.2.0

- Make "siteExclusiveRecordTables" configurable: By now the tables "pages", "pages_language_overlay" and "tt_content" where treated as "site exclusive records". A change on these record only triggers the detectors of the current site because checking references to other sites is not needed. Now this setting is configurable and the table "sys_file_reference" was added to the default tables since we also know from this table, that is only related to the current site.
- Make compatible with EXT:solr 7.0.0