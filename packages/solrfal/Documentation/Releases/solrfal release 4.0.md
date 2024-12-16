### Version 4.0.0

- Document merging to avoid duplicates of multiple files
- Performance optimization in AbstractRecordDetector
- Fixed TS path in default template plugin.tx_solr.index.queue.__TABLENAME__.fields.url
- Performance fix in backend: When content or pages are updated, now only the detectors for the affected site are triggered instead of all. This gives a huge performance boost on systems with many sites.
