.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: ../Images.txt

.. _admin-migration:

Migration
---------

If you had already installed an extension for SEO (metaseo or seo_basics) you can migrate properties
like the browser title or the canonical URL. Therefor you have to install cs_seo < 5.0 and run the update script.

.. image:: /_Images/Administrator/Screen2.png
   :width: 1268
   :alt: Configure the extension or run the update script

After that you can delete the other SEO extension and update your database.

Page properties in TYPO3 v9
^^^^^^^^^^^^^^^^^^^^^^^^^^^
If you use TYPO3 v8 and want to update to TYPO3 v9 or higher, please use cs_seo version 4, to migrate the page properties
from cs_seo to core, e.g 'tx_csseo_title' to 'seo_title'.

From older versions
^^^^^^^^^^^^^^^^^^^

Please take a look at our :ref:`_changelog`.
