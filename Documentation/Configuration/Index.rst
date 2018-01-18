.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _c_configuration:

Configuration
=============

Here you find information for integrators, e.g how to set up the sitemap.xml or the hreflang.

Main Topics
-----------

Meta tags
^^^^^^^^^
The meta tags were automatically inserted from the page settings. Fallback for the social meta tags are already defined
- :ref:`user-faq`.

Browser title prefix/suffix
"""""""""""""""""""""""""""

Generally the sitetitle is defined in the TypoScript Root Template. This is the prefix/suffix for the browser title.
If you wan't to have a specific phrase for each language, look here :ref:`sitetitle`.

Extend news and other extension records
"""""""""""""""""""""""""""""""""""""""
It is possible to extend other records like pages with seo properties. If this is done, the meta tag genaration will
override the page meta tags, if the parameter for the detail view was found in the URL. More information here:
:ref:`extend-existing-models`.

Evaluation
^^^^^^^^^^
All constants for the evaluation, like the minimum length of an description and so on are configurable in the extension
manager (:ref:`admin-configuration`). Some max values were also used to define the max number of characters in the TCA.
You can also define if the evaluation should be visible in the page module and at which position. You can say, which
evaluators are available and you can write your own ones (:ref:`extend-evaluation`).

If you want to hide the evalutation for some editors or pages, you can use TSconfig.

::

	# page TSconfig
	page.mod.web_layout.tx_csseo.disable = 0

	# user TSconfig
	mod.web_layout.tx_csseo.disable = 0


Sitemap.xml
^^^^^^^^^^^
Automatically a sitemap.xml for the default language is generated. No extra fields in the page settings were defined.
If No Index is set, the page is also not visible in the sitemap - :ref:`user-faq`.

Pages
"""""
If you need more languages or the website has an other root pid than 1 use the TypoScript settings: :ref:`sitemap.xml`.

News
""""
If you would like to show also a sitemap for the news, include the TypoScript called "Sitemap.xml for news". Then configure
at least the detail pid. More here: :ref:`sitemap.xml.news`.

Other Extensions and external sitemaps
""""""""""""""""""""""""""""""""""""""
For other extension records or additional, external sitemaps take a look here: :ref:`extend-sitemap`.

RealURL
"""""""
If you use RealURL, cs_seo automatically configures that the path sitemap.xml maps to the page type 655. If you wan't to disable
this, use the setting in the extension manager: :ref:`basic.realURLAutoConf`.

Robots.txt
^^^^^^^^^^
The default robots.txt is configured via TypoScript.

Domain Records
""""""""""""""
You can also define a different content for each domain. Therefore use the extra field in the **sys domain records**
or a TypoScript Condition.

RealURL
"""""""
If you use RealURL, cs_seo automatically configures that the path robots.txt maps to the page type 656. If you wan't to disable
this, use the setting in the extension manager: :ref:`basic.realURLAutoConf`.

User Tracking
^^^^^^^^^^^^^
Easily activate the user tracking via TypoScript, see :ref:`tracking`.

TypoScript Settings Reference
-----------------------------

.. toctree::
	:maxdepth: 5
	:titlesonly:

	Language/Index
	Sitemap/Index
	Social/Index
	Tracking/Index