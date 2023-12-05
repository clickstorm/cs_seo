.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _user-manual:

Users Manual
============

With this extension editors can easily set some advanced properties for SEO in the page settings.
With the Google search result preview the editor can see the effects of some properties directly.

We also change the max characters settings in some fields so that they are not too long for search results.
Furthermore the new configurations are good explained. You can see the explanation as usual by hovering the label.

Page Properties
---------------

.. container:: ts-properties

	============================== ================================== ======================= ====================
	Property                       Label                                 Tab                     Type
	============================== ================================== ======================= ====================
	`tx\_csseo\_title\_only`_      Title only                         SEO                     boolean
   `tx\_csseo\_keyword`_          Focus Keyword                      SEO                     string
	`tx\_csseo\_tw\_creator`_      Twitter Creator                    Social Media            string
	`tx\_csseo\_tw\_site`_         Twitter Site                       Social Media            string
	`tx\_csseo\_json\_ld`_         Structured Data (JSON-LD)          Metadata                string
	============================== ================================== ======================= ====================

Property details
^^^^^^^^^^^^^^^^

.. _tx_csseo_title_only:

Title only
""""""""""

.. container:: table-row

   Property
         tx_csseo_title_only
   Data type
         boolean
   Description
        Show the browser title without the site title in case the space isn't enough.

.. _tx_csseo_keyword:

Focus Keyword
"""""""""""""

.. container:: table-row

   Property
         tx_csseo_keyword
   Data type
         string
   Description
        Specify a word or phrase. In the SEO evaluation will be proofed wether this Focus Keyword is set in the browser
        title, the meta description and in the page content. You can specify multiple, comma-separated alternatives,
        e.g. for plural or stop words.

.. _tx_csseo_tw_creator:

Twitter Creator
"""""""""""""""

.. container:: table-row

   Property
         tx_csseo_tw_creator
   Data type
         string
   Description
        Enter a twitter username without the @ sign. If the user shares the page on twitter,
        this account will be shown as creator.

.. _tx_csseo_tw_site:

Twitter Site
""""""""""""

.. container:: table-row

   Property
         tx_csseo_tw_site
   Data type
         string
   Description
        Enter a twitter username without the @ sign. If the user shares the page on twitter, this account will be used
        in the card footer. The account should belong to your website.


.. _tx_csseo_json_ld:

Structured Data (JSON-LD)
"""""""""""""""""""""""""

.. container:: table-row

   Property
         tx_csseo_json_ld
   Data type
         string
   Description
        Add more information about the content of the current page with JSON-LD.
        `More information <https://developers.google.com/search/docs/guides/intro-structured-data>`_


.. _user-faq:

FAQ
---

**Do I have to insert open graph and twitter cards properties always?**
The title, description and an image should be available on every page. The title is default given by the page title.
You can set the description in the SEO tab. A default image for sharing can be set by the open graph image field in the
Social Media tab.

