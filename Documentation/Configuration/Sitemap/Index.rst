.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _sitemap.xml:

Sitemap.xml
-----------

Here we show the constants which are available to configure the sitemap.xml. If you want to know how to extend the
sitemap for example for your own extension or tx_news see also: :ref:`c_configuration` and :ref:`extend-sitemap`.

Properties
^^^^^^^^^^

.. container:: ts-properties

	================================ ===================================== ======================= ====================
	Property                         Data type                             :ref:`t3tsref:stdwrap`  Default
	================================ ===================================== ======================= ====================
	`sitemap.pages.rootPid`_         :ref:`t3tsref:data-type-int`          no                      1
	`sitemap.pages.languageUids`_    :ref:`t3tsref:data-type-list`         no                      0
	`sitemap.pages.doktypes`_        :ref:`t3tsref:data-type-list`         no                      1
	`sitemap.additional`_            :ref:`t3tsref:data-type-string`       no
	`sitemap.view.layoutRootPath`_   :ref:`t3tsref:data-type-string`       no
	`sitemap.view.partialRootPath`_  :ref:`t3tsref:data-type-string`       no
	`sitemap.view.templateRootPath`_ :ref:`t3tsref:data-type-string`       no
	================================ ===================================== ======================= ====================

plugin.tx_csseo.sitemap.

.. _sitemap.pages.rootPid:

Root Pid
""""""""

.. container:: table-row

   Property
         rootPid
   Data type
         :ref:`t3tsref:data-type-int`
   Description
         Set the root pid for the current domain. The sitemap.xml will have this page as root.

.. _sitemap.pages.languageUids:

Language Uids
"""""""""""""

.. container:: table-row

   Property
         languageUids
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         List the language uids (e.g. 0,2,3). For each language an extra sub sitemap.xml will be generated.

.. _sitemap.pages.doktypes:

Doktypes
""""""""

.. container:: table-row

   Property
         languageUids
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         Comma separated list without whitespaces of allowed doktypes in the sitemap.xml (e.g. 1 for pages).

.. _sitemap.additional:

Additional
""""""""""

.. container:: table-row

   Property
         additional
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         If you would like to add an external sub sitemap.xml enter the complete URL here. More URLs can be added in the TypoScript Setup.

.. _sitemap.view.layoutRootPath:

Layout Root Path
""""""""""""""""

.. container:: table-row

   Property
         view.layoutRootPath
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Path to layout files. If set this path will be checked first for layouts used in all sitemaps. Layouts in `EXT:cs_seo/Resources/Private/Layouts` are always the fallback.

.. _sitemap.view.partialRootPath:

Partial Root Path
"""""""""""""""""

.. container:: table-row

   Property
         view.partialRootPath
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Path to partial files. If set this path will be checked first for partials used in all sitemaps. Layouts in `EXT:cs_seo/Resources/Private/Partials` are always the fallback.

.. _sitemap.view.templateRootPath:

Template Root Path
""""""""""""""""""

.. container:: table-row

   Property
         view.templateRootPath
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Path to template files. If set this path will be checked first for templates used in all sitemaps. Templates in `EXT:cs_seo/Resources/Private/Templates/Sitemap` are always the fallback.

.. _sitemap.xml.news:

Sitemap for news records
^^^^^^^^^^^^^^^^^^^^^^^^

The following constants are available, if you include the TypoScript from the extension for tx_news.

.. container:: ts-properties

	============================= ===================================== ======================= ====================
	Property                      Data type                             :ref:`t3tsref:stdwrap`  Default
	============================= ===================================== ======================= ====================
	`news.storagePid`_            :ref:`t3tsref:data-type-list`          no
	`news.detailPid`_             :ref:`t3tsref:data-type-int`           no
	`news.languageUids`_          :ref:`t3tsref:data-type-list`          no
	`news.categories`_            :ref:`t3tsref:data-type-list`          no
	============================= ===================================== ======================= ====================

plugin.tx_csseo.extensions.news.

.. _news.storagePid:

Storage Pid
"""""""""""

.. container:: table-row

   Property
         storagePid
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         The storage pid(s) where the news are saved.

.. _news.detailPid:

Detail Pid
""""""""""

.. container:: table-row

   Property
         detailPid
   Data type
         :ref:`t3tsref:data-type-int`
   Description
         Required! The page uid where the detail view of the news is shown.

.. _news.languageUids:

Language Uids
"""""""""""""

.. container:: table-row

   Property
         languageUids
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         List the language uids (e.g. 0,2,3). For each language an extra sub sitemap.xml will be generated.

.. _news.categories:

Categories
""""""""""

.. container:: table-row

   Property
	     categories
   Data type
         :ref:`t3tsref:data-type-list`
   Description
         List the category uids (e.g. 1,2,3). If set, only the news which belongs to at least one of this category uid were shown.
