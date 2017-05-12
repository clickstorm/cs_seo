#
# Table structure for table 'pages'
#
CREATE TABLE pages (
  tx_csseo_title varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_title_only tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tx_csseo_keyword varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_canonical varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_no_index tinyint(1) unsigned DEFAULT '0' NOT NULL,
  tx_csseo_no_follow tinyint(1) unsigned DEFAULT '0' NOT NULL,
  tx_csseo_og_title varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_og_description text NOT NULL,
  tx_csseo_og_image int(11) unsigned NOT NULL default '0',
  tx_csseo_tw_title varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_tw_description text NOT NULL,
  tx_csseo_tw_image int(11) unsigned NOT NULL default '0',
  tx_csseo_tw_creator varchar(255) DEFAULT '' NOT NULL

);

#
# Table structure for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
  tx_csseo_title varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_title_only tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tx_csseo_keyword varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_canonical varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_og_title varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_og_description text NOT NULL,
  tx_csseo_og_image int(11) unsigned NOT NULL default '0',
  tx_csseo_tw_title varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_tw_description text NOT NULL,
  tx_csseo_tw_image int(11) unsigned NOT NULL default '0',
  tx_csseo_tw_creator varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_csseo_domain_model_meta'
#
CREATE TABLE tx_csseo_domain_model_meta (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	title_only tinyint(1) unsigned DEFAULT '0' NOT NULL,
	keyword varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	canonical varchar(255) DEFAULT '' NOT NULL,
	no_index tinyint(1) unsigned DEFAULT '0' NOT NULL,
	no_follow tinyint(1) unsigned DEFAULT '0' NOT NULL,
	og_title varchar(255) DEFAULT '' NOT NULL,
	og_description text NOT NULL,
	og_image int(11) unsigned NOT NULL default '0',
	tw_title varchar(255) DEFAULT '' NOT NULL,
	tw_description text NOT NULL,
	tw_image int(11) unsigned NOT NULL default '0',
	tw_creator varchar(255) DEFAULT '' NOT NULL,

	uid_foreign int(11) DEFAULT '0' NOT NULL ,
	tablenames varchar(255) DEFAULT '' NOT NULL,

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
	KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);

#
# Table structure for table 'tx_csseo_domain_model_evaluation'
#
CREATE TABLE tx_csseo_domain_model_evaluation (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	results text NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,

	uid_foreign int(11) DEFAULT '0' NOT NULL ,
	tablenames varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

# Table structure for table "sys_domain"
#
CREATE TABLE sys_domain (
	tx_csseo_robots_txt text NOT NULL
);

#
# Table structure for external table 'tx_myext_domain_model_mymod'
#
# CREATE TABLE tx_myext_domain_model_mymod (
#	  tx_csseo int(11) unsigned NOT NULL default '0',
# );