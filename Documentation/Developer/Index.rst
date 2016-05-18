.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

Developer Corner
================




.. _developer-ts:

Exclude meta tags for own extensions
------------------------------------

If you set some meta tags in your own extension you can easily remove some of our functions by TS.

::

    # check if detail view
    [globalVar = GP:tx_news_pi1|news > 0]

    # remove all metatags from cs_seo
    page.headerData.tx_csseo >

    # remove canonical
    page.headerData.tx_csseo.10 >

    # remove href lang
    page.headerData.tx_csseo.20 >

    # disable title tag
    config.noPageTitle = 1

    # remove meta description
    page.meta.description >

    # remove robots
    page.meta.robots >

    [end]

    ### Disable Tracking if Backend User detected (included by default) ###
    [globalVar = TSFE:beUserLogin > 0]
        page.jsFooterInline.tx_csseo >
        page.includeJSFooter.tx_csseo >
    [end]