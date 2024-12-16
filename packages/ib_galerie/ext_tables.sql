#
# Table structure for table 'tx_ibgalerie_domain_model_galerie'
#
CREATE TABLE tx_ibgalerie_domain_model_galerie (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	code varchar(255) DEFAULT '' NOT NULL,
	images int(11) unsigned NOT NULL default '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

-- phpMyAdmin SQL Dump
-- version 4.7.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 18, 2018 at 10:13 AM
-- Server version: 5.6.35
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: intbundtypo3
--

-- --------------------------------------------------------

--
-- Table structure for table cf_ib_galerie
--

CREATE TABLE cf_ib_galerie (
  id int(11) UNSIGNED NOT NULL,
  identifier varchar(250) NOT NULL DEFAULT '',
  expires int(11) UNSIGNED NOT NULL DEFAULT '0',
  content mediumblob
);

-- --------------------------------------------------------

--
-- Table structure for table cf_ib_galerie_tags
--

CREATE TABLE cf_ib_galerie_tags (
  id int(11) UNSIGNED NOT NULL,
  identifier varchar(250) NOT NULL DEFAULT '',
  tag varchar(250) NOT NULL DEFAULT ''
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table cf_ib_galerie
--
ALTER TABLE cf_ib_galerie
  ADD PRIMARY KEY (id),
  ADD KEY cache_id (identifier,expires);

--
-- Indexes for table cf_ib_galerie_tags
--
ALTER TABLE cf_ib_galerie_tags
  ADD PRIMARY KEY (id),
  ADD KEY cache_id (identifier),
  ADD KEY cache_tag (tag);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table cf_ib_galerie
--
ALTER TABLE cf_ib_galerie
  MODIFY id int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table cf_ib_galerie_tags
--
ALTER TABLE cf_ib_galerie_tags
  MODIFY id int(11) UNSIGNED NOT NULL AUTO_INCREMENT;