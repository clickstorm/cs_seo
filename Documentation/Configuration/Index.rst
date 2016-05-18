.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _c_configuration:

Configuration Reference
=======================

.. _configuration-typoscript:

TypoScript Reference
--------------------

Properties
^^^^^^^^^^

.. container:: ts-properties

	============================= ===================================== ======================= ====================
	Property                      Data type                             :ref:`t3tsref:stdwrap`  Default
	============================= ===================================== ======================= ====================
	`hreflang.enable`_            :ref:`t3tsref:data-type-boolean`      no
	`hreflang.ids`_               :ref:`t3tsref:data-type-list`         no
	`hreflang.keys`_              :ref:`t3tsref:data-type-list`         no
	`social.twitter.creator`_     :ref:`t3tsref:data-type-string`       no
	`tracking.googleAnalytics`_   :ref:`t3tsref:data-type-string`       no
	`tracking.piwik`_             :ref:`t3tsref:data-type-string`       no
	`tracking.piwik.id`_          :ref:`t3tsref:data-type-string`       no                      1
	============================= ===================================== ======================= ====================

href="lang"
^^^^^^^^^^^

plugin.tx_csseo.hreflang.

.. _hreflang.enable:

Enable
""""""

.. container:: table-row

   Property
         enable
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         If set, hreflang tags will be inserted to the page header section. Please don't forget to set the ids and keys, too.

.. _hreflang.ids:

IDs
"""

.. container:: table-row

   Property
         ids
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         List the language uids, which should be linked with a hreflang-Tag (e.g. 0,2,3).

.. _hreflang.keys:

Keys
""""

.. container:: table-row

   Property
         keys
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         List the language keys for the hreflang tags - same order as hreflang language uids (e.g. en,de,ch).

Social
^^^^^^

plugin.tx_csseo.social.

.. _social.twitter.creator:

Twitter Creator
"""""""""""""""

.. container:: table-row

   Property
         twitter.creator
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Default twitter account as creator (without @)

Tracking
^^^^^^^^

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

