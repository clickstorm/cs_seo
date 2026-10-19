CREATE TABLE tx_csseo_domain_model_meta (
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	tablenames varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_csseo_domain_model_evaluation (
	results text,
	url varchar(255) DEFAULT '' NOT NULL,

	uid_foreign int(11) DEFAULT '0' NOT NULL,
	tablenames varchar(255) DEFAULT '' NOT NULL
);
