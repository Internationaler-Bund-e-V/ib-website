
#
# Table structure for table 'tx_solr_indexqueue_file'
#
CREATE TABLE tx_solr_indexqueue_file (
	uid int(11) NOT NULL auto_increment,

	last_update int(11) DEFAULT '0' NOT NULL,
	last_indexed int(11) DEFAULT '0' NOT NULL,

	file INT(11) DEFAULT '0' NOT NULL,
	merge_id varchar(255) DEFAULT '' NOT NULL,

	context_type varchar(255) DEFAULT '' NOT NULL,
	context_site int(11) DEFAULT '0' NOT NULL,
	context_access_restrictions varchar(255) DEFAULT 'c:0' NOT NULL,
	context_language int(11) DEFAULT '0' NOT NULL,

	context_record_indexing_configuration varchar(255) DEFAULT '' NOT NULL,
	context_record_uid int(11) unsigned DEFAULT '0' NOT NULL,
	context_record_table varchar(255) DEFAULT '' NOT NULL,
	context_record_field varchar(255) DEFAULT '' NOT NULL,
	context_record_page int(11) unsigned DEFAULT '0' NOT NULL,
	context_additional_fields text,

	error_message text NOT NULL,
	error tinyint(1) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY file (file)
) ENGINE=InnoDB;

