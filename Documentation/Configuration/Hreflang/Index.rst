.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

href="lang"
-----------

Properties
^^^^^^^^^^

.. container:: ts-properties

	============================= ===================================== ======================= ====================
	Property                      Data type                             :ref:`t3tsref:stdwrap`  Default
	============================= ===================================== ======================= ====================
	`hreflang.enable`_            :ref:`t3tsref:data-type-boolean`      no
	`hreflang.ids`_               :ref:`t3tsref:data-type-list`         no
	`hreflang.keys`_              :ref:`t3tsref:data-type-list`         no
	============================= ===================================== ======================= ====================

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

