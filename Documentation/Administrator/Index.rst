.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt
.. include:: Images.txt

.. _admin-manual:

Administrator Manual
====================

Here are some hints for admins.


.. _admin-installation:

Installation
------------

- How should the extension be installed?
- Are there dependencies to resolve?
- Is it a static template file to be included?

To install the extension, perform the following steps:

#. Download and install the extension via Extension Manager or Composer
#. Include the TypoScript from the extension!
#. Insert a domain record at the root page
#. Make some configurations via TypoScript.

|img-1|

**!If you forgot to include the TypoScript, you will get an error if you open the page settings!**

.. _admin-migration:

Migration
---------

If you had already installed an extension for SEO (metaseo or seo_basics) you can migrate properties
like the browser title or the canonical URL. Therefor you have to install cs_seo and run the update script.

|img-2|

After that you can delete the other SEO extension and update your database


.. _admin-configuration:

Configuration
-------------

In the extension manager you can make some configurations.

|img-3|

Properties
^^^^^^^^^^

.. container:: ts-properties

	============================= ===================================== ====================
	Property                      Data type                             Default
	============================= ===================================== ====================
	`basic.enablePathSegment`_    :ref:`t3tsref:data-type-boolean`      true
	`page.maxTitle`_              :ref:`t3tsref:data-type-integer`      57
	`page.maxDescription`_        :ref:`t3tsref:data-type-integer`      156
	`page.maxNavTitle`_           :ref:`t3tsref:data-type-integer`      50
	`evaluation.inPageModule`_    :ref:`t3tsref:data-type-integer`      0
	`evaluation.evaluators`_      :ref:`t3tsref:data-type-string`       Title,Description,H1,H2,Images,Keyword
	============================= ===================================== ====================

Basic configurations
^^^^^^^^^^^^^^^^^^^^

.. _basic.enablePathSegment:

Enable the JS for automatic filling of the path segment, if this is empty
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

.. container:: table-row

   Property
         enable
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         If enabled, a JS is insert in the page settings, so that the RealURL pathsegment will be filled, if it is empty.
         This prevents, that if an editor changes the URL, the link also changes.


Page configurations
^^^^^^^^^^^^^^^^^^^

In this section we provide default settings for maximum characters of the meta data which were recommended.
The recommendation is the result of a research by ourself. So if you don't agree with them, you can override them here.

.. _page.maxTitle:

Max characters of title
"""""""""""""""""""""""

.. container:: table-row

   Property
         maxTitle
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         The recommended maximum number of characters for the meta title tag.


.. _page.maxDescription:

Max characters of description
"""""""""""""""""""""""""""""

.. container:: table-row

   Property
         maxDescription
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         The recommended maximum number of characters for the meta description tag.

.. _page.maxNavTitle:

Max characters of nav title
"""""""""""""""""""""""""""

.. container:: table-row

   Property
         maxNavTitle
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         The recommended maximum number of characters for the nav title and URL.


.. _evaluation.inPageModule:

Show evaluation in the page module
""""""""""""""""""""""""""""""""""

.. container:: table-row

   Property
         inPageModule
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         Show the dialog of the evaluation in the page module. (0: In the head of the page module, 1: in the footer, 2: none).


.. _evaluation.evaluators:

Evaluators
""""""""""

.. container:: table-row

   Property
         evaluators
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Comma separated list of the evaluators which should analyse the page. You can also add your own evaluators or change the sorting.



Please take also a look at the next chapter for TypoScript configurations.

.. _admin-faq:

Trouble shooting
----------------

I get an error when I edit a page. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Please include the TypoScript from the extension in your root ts.

There is no domain displayed in the google preview. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Please insert a domain record at the the root page.