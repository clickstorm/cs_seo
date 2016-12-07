.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

Tracking
--------

Properties
^^^^^^^^^^

.. container:: ts-properties

	============================= ===================================== ======================= ====================
	Property                      Data type                             :ref:`t3tsref:stdwrap`  Default
	============================= ===================================== ======================= ====================
	`tracking.googleAnalytics`_   :ref:`t3tsref:data-type-string`       no
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


.. _tracking.piwik:

Piwik
"""""

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

