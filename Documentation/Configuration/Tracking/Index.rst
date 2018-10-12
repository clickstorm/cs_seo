.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _tracking:

Tracking
--------

Properties
^^^^^^^^^^

.. container:: ts-properties

	============================= ===================================== ======================= ====================
	Property                      Data type                             :ref:`t3tsref:stdwrap`  Default
	============================= ===================================== ======================= ====================
	`tracking.googleAnalytics`_   :ref:`t3tsref:data-type-string`       no
	`tracking.googleTagManager`_  :ref:`t3tsref:data-type-string`       no
	`tracking.piwik`_             :ref:`t3tsref:data-type-string`       no
	`tracking.piwik.id`_          :ref:`t3tsref:data-type-string`       no                      1
	============================= ===================================== ======================= ====================

plugin.tx_csseo.tracking.

.. _tracking.googleAnalytics:

Google Analytics
""""""""""""""""

.. container:: table-row

   Property
         googleAnalytics
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         If set a JS for Google Analytics inc. download tracking is set with the given id (e.g. UX-XXXXXXX-XXX).
         Furthermore, you can add a link to your website to disable Google Analytics, e.g.

.. code-block:: html

	<a onclick="alert('Google Analytics has been disabled.');" href="javascript:gaOptout()">disable Google Analytics</a>

.. _tracking.googleTagManager:

Google Tag Manager
""""""""""""""""""

.. container:: table-row

   Property
         googleTagManager
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         If set (e.g. 'GTM-XXXXXXXX'), the Google Tag Manager is enabled. Furthermore Google Tag Manager requires an
         additional fallback for clients who disabled JS
         - `Google Tag Manager Quickstart <https://developers.google.com/tag-manager/quickstart/>`_ . Therefore
         cs_seo offers an extra TypoScript file which you can include (like here :ref:`admin-installation`) or copy and
         modify. **!If you include the TS file check your body tag afterwards!**

.. _tracking.piwik:

Piwik (Matomo)
""""""""""""""

.. container:: table-row

   Property
         piwik
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         If set a JS for Piwik tracking is set with domain.

.. _tracking.piwik.id:

Piwik SiteId
""""""""""""

.. container:: table-row

   Property
         piwik.id
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         This siteId is inserted in the JS for Piwik.

