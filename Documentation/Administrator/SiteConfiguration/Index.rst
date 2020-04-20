.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: ../Images.txt

.. _site-configuration:

Site Configuration
------------------

In the site configuration you can make some configurations.

|img-3|

Properties
^^^^^^^^^^

.. container:: ts-properties

	======================================================= ===================================== ====================
	Property                                                Data type                             Default
	======================================================= ===================================== ====================
	`language.txCsseoXdefault`_                             :ref:`t3tsref:data-type-integer`      0
	======================================================= ===================================== ====================

Language configurations
^^^^^^^^^^^^^^^^^^^^^^^

.. _language.txCsseoXdefault:

x-default
"""""""""

.. container:: table-row

   Property
         txCsseoXdefault
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         Signal for search engines to use this language if no other is better suited.
		 If you want to have another language as the default you can change this here.
         **Important**! If the x-default is not the default language, cs_seo will generate
         the canonicals and hreflang for pages and adds only specified parameters.
         Take a look here:
	     :ref:`basic.useAdditionalCanonicalizedUrlParametersOnly`.