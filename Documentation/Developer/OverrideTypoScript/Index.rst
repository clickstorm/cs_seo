.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

Override TypoScript
^^^^^^^^^^^^^^^^^^^

Most of the metatags are added via TypoScript. Therefore the behaviour can be overridden in a simple way.

.. _developer-ts:

Exclude meta tags for own extensions
------------------------------------

If you set some meta tags in your own extension you can easily remove some of our functions by TS.

::

    # check if detail view
    [globalVar = GP:tx_news_pi1|news > 0]

    # remove all metatags from cs_seo
    page.headerData.654 >

    # remove canonical
    page.headerData.654.10 >

    # remove href lang
    page.headerData.654.20 >

    # disable title tag
    config.noPageTitle = 1

    # remove meta description
    page.meta.description >

    # remove robots
    page.meta.robots >

    [end]

    ### Disable Tracking if Backend User detected (included by default) ###
    [globalVar = TSFE:beUserLogin > 0]
        page.jsFooterInline.654 >
        page.includeJSFooter.654 >
    [end]

You'll find more examples in the extension directory typo3conf/ext/cs_seo/Configuration/TypoScript/Extensions/.

.. _developer-global:

Global noindex
--------------

If you wish to set the whole page to noindex, e.g. for development, you can use the following TS.

::

	# insert meta robots="noindex,follow" on every page
	page.meta.robots.if >

Don't to forget to remove this TypoScript in production.
