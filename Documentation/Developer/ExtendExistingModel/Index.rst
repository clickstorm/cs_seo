.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _extend-existing-models:

Extend existing models
^^^^^^^^^^^^^^^^^^^^^^

To extend an already existing extbase model with a detail view, e.g. news or job offers,
and offer the evaluation of this records, easily complete the following two steps.


If the hreflang tag is enabled in TypoScript, the hreflang tag is set for all existing languages of the extended
extbase item (not for the languages of the detail page). It checks the languages for
the extbase item, which is given by the get parameter from the page TSconfig in step 2.
If a fallback item is displayed because of sys_language_mode content_fallback, the hrefang tag will be removed and
the canonical tag points to the url of the displayed fallback item.

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
		1 = tx_myext_domain_model_mymod

		# if the get parameter is set in the URL the cs_seo properties will be shown
		1.enable = GP:tx_myext_pi1|mymod

		# if the model already has fields like title etc. define them as fallback
		1.fallback {

		    # cs_seo title field fallback = mymod title field
		    title = title

		    # cs_seo description field fallback = mymod description field
		    description = description
		}

		# enable evaluation for news
		1.evaluation {
			# additional params to initialize the detail view, the pipe will be replaced by the uid
			getParams = &tx_myext_pi1[controller]=MyController&tx_myext_pi1[action]=MyAction&tx_myext_pi1[mymod]=|

			# detail pid for the current records, only if set the table will be available
			detailPid =
		}
	}

**Clear the system cache** and done. A new tab is in the backend visible called *SEO*.
