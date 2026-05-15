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

Frontend access is restricted (IP allow list or HTTP Basic Auth). There are no evaluation results. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
cs_seo fetches the rendered frontend page to evaluate it, so the server must be able to
reach it. You have three options:

**1. Allow the server's own IP** (works for both IP restrictions and Basic Auth):

.. code-block:: apache

   Require ip xxx.xxx.xxx.xxx
   # When combined with Basic Auth, also add:
   Require valid-user


**2. Use the X-CS-SEO request header** to selectively bypass restrictions
(see next FAQ entry).

**3. Reuse Basic Auth credentials of the current backend session**:
cs_seo automatically forwards ``$_SERVER['PHP_AUTH_USER']`` / ``$_SERVER['PHP_AUTH_PW']``
to the evaluation request, so no extra configuration is needed.

Limitations of option 3:

* Only works in the web context. The CLI command ``cs_seo:evaluate`` has no Basic Auth
  session - use option 1 for cron jobs.
* Some PHP-FPM / FastCGI setups do not populate ``PHP_AUTH_*``. Forward the
  ``Authorization`` header in your web server config, e.g. for Apache:

  .. code-block:: apache

     RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]


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
