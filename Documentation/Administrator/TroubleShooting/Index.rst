.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _admin-faq:

Trouble shooting
----------------

I get an error when I edit a page. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Please include the TypoScript from the extension in your root ts.

There is no domain displayed in the google preview. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Please add a Site Configuration for your root page.

I can't find a robots meta tag with index,follow. Is this a bug?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Not at all. index,follow is the default setting. So if no robot tag is specified, the crawler has no restrictions
for indexing. More information you'll get by
`Google <https://developers.google.com/search/reference/robots_meta_tag?hl=en/>`_.

.htaccess disallow frontend access. There are no evaluation results. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Give your server access to the frontend. Include the following line in your .htaccess file and replace
the x with the IP from the server.

.. code-block:: apache

   Order allow,deny
   Allow from xxx.xxx.xxx.xxx


You could also use the domain instead of the IP.

.. code-block:: apache

   Allow from .mydomian.com


In my setup I need specific options to handle the evaluation request. How can I solve this?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
All requests from cs_seo get a specific request header "X-CS-SEO". So you can specify your own handles,
e.g. in your .htacces file.

.. code-block:: apache

   RewriteCond %{REQUEST_URI} ^/en [NC]
   RewriteCond %{HTTP:X-CS-SEO} !^1$
   RewriteRule ^(.*)$ https://en.example.org/$1 [L,R=301]


Every browser title has a pipe symbol | at the end. How can I remove this?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Usually we display after this symbol the title of your whole site. You can set the title in your TypoScript Template
record or via TypoScript

.. code-block:: typoscript

   plugin.tx_csseo.sitetitle = my site title


You can also change or remove the symbol via TypoScript, see also
`TSREF <https://docs.typo3.org/typo3cms/TyposcriptReference/Setup/Config/Index.html#pagetitleseparator/>`_

.. code-block:: typoscript

   config.pageTitleSeparator =
