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
	tx_csseo_og_description text,
	tx_csseo_og_image int(11) unsigned NOT NULL default '0',
	tx_csseo_tw_title varchar(255) DEFAULT '' NOT NULL,
	tx_csseo_tw_description text,
	tx_csseo_tw_image int(11) unsigned NOT NULL default '0',
	tx_csseo_tw_creator varchar(255) DEFAULT '' NOT NULL,
	tx_csseo_tw_site varchar(255) DEFAULT '' NOT NULL
);