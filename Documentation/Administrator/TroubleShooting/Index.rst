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
Please insert a domain record at the the root page.

I can't find a robots meta tag with index,follow. Is this a bug?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Not at all. index,follow is the default setting. So if no robot tag is specified, the crawler has no restrictions
for indexing. More information you'll get by
`Google <https://developers.google.com/search/reference/robots_meta_tag?hl=en/>`_.

.htaccess disallow frontend access. There are no evaluation results. What should I do?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Give your server access to the frontend. Include the following line in your .htaccess file and replace
the x with the IP from the server.

Order allow,deny
Allow from xxx.xxx.xxx.xxx

You could also use the domain instead of the IP.

Allow from .mydomian.com

The canonical URL adds &L=0 to all URLs. How can I prevent this?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
We have to force the language for the correct language fallback. To prevent the param in the URL you can
configure RealURL as it is shown here:

::

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT'] = [
	    'preVars' => [
	        [
	            'GETvar' => 'L',
	            'valueMap' => [
	                'de' => '1',
	            ],
	            'noMatch' => 'bypass',
	        ]
	    ]
	];


So the L param will only be added to the URL, if the value is defined in the valueMap.