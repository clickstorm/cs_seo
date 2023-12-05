﻿.. ==================================================
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
the extbase item, which is given by the get parameter from the page Yaml config in step 2.
If a fallback item is displayed because of sys_language_mode content_fallback, the hrefang tag will be removed and
the canonical tag points to the url of the displayed fallback item.

.. _ext_tables:

1. Add a Yaml Config File
-------------------------

Add the following yaml content to a file EXT:myext/Configuration/CsSeo/config.yaml.

.. code-block:: yaml

   records:

      # new index and table name of the model
      tx_myext_domain_model_mymod:

         # if the get parameter is set in the URL the cs_seo properties will be shown
         enable: 'GP:tx_myext_pi1|mymod'

         # if the model already has fields like title etc. define them as fallback
         fallback:

            # cs_seo title field fallback = mymod title field
            title: title

            # cs_seo description field fallback = mymod description field
            # you can use double slashes for multiple fallback options, e.g. 'teaser // abstract'
            # in this case the first field with content will be used
            # also more fields possible within curly brackets, e.g. description: {teaser} - {company}
            description: description

         # enable evaluation for mymod
         evaluation:

            # additional params to initialize the detail view, the pipe will be replaced by the uid
            getParams: >-
               &tx_myext_pi1[controller]=MyController&tx_myext_pi1[action]=MyAction&tx_myext_pi1[mymod]=|

            # detail pid for the current records, only if set the table will be available
            detailPid: 100


You have the possibility to merge multiple config files via `imports <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Yaml/Index.html>`__.

If you need another detail pid, for example for different storage pids, you can use the PSR-14 event \\Clickstorm\\CsSeo\\Event\\ModifyEvaluationPidEvent.
Furthermore you can also use
`environment variables <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/SiteHandling/UsingEnvVars.html>`__
for different pids in different environments like staging or production.

.. code-block:: yaml

   detailPid: '%env(TYPO3__TX_NEWS__PID_DETAIL)%'


You may take also a look at the
`dotenv-connector <https://github.com/helhum/dotenv-connector>`__.


2. Register the config file
---------------------------

Register the config file in your ext_localconf.php.

.. code-block:: php

   // Copy the current file
   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['yamlConfigFile'] = 'EXT:myext/Configuration/CsSeo/config.yaml';


**Clear the system cache** and done. A new tab is in the backend visible called *SEO*.

3. Update the database
----------------------

You need to **update the database in install tool** afterwards. We will automatically insert the following SQL.

.. code-block:: sql

	CREATE TABLE tx_myext_domain_model_mymod (
		tx_csseo int(11) DEFAULT '0' NOT NULL
	);

