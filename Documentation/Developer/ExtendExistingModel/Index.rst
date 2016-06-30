.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

Extend existing models
^^^^^^^^^^^^^^^^^^^^^^

To extend an already existing extbase model with a detail view, e.g. news or job offers,
easily complete the following two steps.

.. _ext_tables:

1. Add the field in the ext_tables.sql
--------------------------------------

.. code-block:: sql

	CREATE TABLE tx_myext_domain_model_mymod (
		tx_csseo int(11) unsigned NOT NULL default '0',
	);

Maybe we'll find an other solution. But at the current time this is necessary. Otherwise an error will be thrown,
if the editor would like to save changes in the model data.

Of course you need to **update the database in install tool** afterwards.

2. Add model in Page TSconfig
-----------------------------

Add the following page TSconfig to the page with ID = 1.

::

	tx_csseo {
		# new index and table name of the model
		1 = tx_news_domain_model_news

		# if the get parameter is set in the URL the cs_seo properties will be shown
		1.data = GP:tx_news_pi1|news

		# if the model already has fields like title etc. define them as fallback
		1.fallback {

		    # cs_seo title field fallback = news title field
		    title = title

		    # cs_seo description field fallback = news description field
		    description = description
		}
	}

**Clear the system cache** and done. A new tab is in the backend visible called *SEO*.
