#
# Table structure for table 'pages'
#
CREATE TABLE pages (
  tx_csseo_title_only tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tx_csseo_keyword varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_tw_creator varchar(255) DEFAULT '' NOT NULL,
  tx_csseo_tw_site varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_csseo_domain_model_meta'
#
CREATE TABLE tx_csseo_domain_model_meta (
	title varchar(255) DEFAULT '' NOT NULL,
	title_only tinyint(1) unsigned DEFAULT '0' NOT NULL,
	keyword varchar(255) DEFAULT '' NOT NULL,
	description text,
	canonical varchar(255) DEFAULT '' NOT NULL,
	no_index tinyint(1) unsigned DEFAULT '0' NOT NULL,
	no_follow tinyint(1) unsigned DEFAULT '0' NOT NULL,
	og_title varchar(255) DEFAULT '' NOT NULL,
	og_description text,
	og_image int(11) unsigned NOT NULL default '0',
	tw_title varchar(255) DEFAULT '' NOT NULL,
	tw_description text,
	tw_image int(11) unsigned NOT NULL default '0',
	tw_creator varchar(255) DEFAULT '' NOT NULL,
	tw_site varchar(255) DEFAULT '' NOT NULL,

	uid_foreign int(11) DEFAULT '0' NOT NULL ,
	tablenames varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_csseo_domain_model_evaluation'
#
CREATE TABLE tx_csseo_domain_model_evaluation (
	results text,
	url varchar(255) DEFAULT '' NOT NULL,

	uid_foreign int(11) DEFAULT '0' NOT NULL ,
	tablenames varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for external table 'tx_myext_domain_model_mymod'
#
# CREATE TABLE tx_myext_domain_model_mymod (
#	  tx_csseo int(11) unsigned NOT NULL default '0',
# );