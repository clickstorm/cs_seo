.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: ../Images.txt

.. _admin-configuration:

Extension Manager Configuration
-------------------------------

In the extension manager you can make some configurations.

|img-3|

Properties
^^^^^^^^^^

.. container:: ts-properties

	==================================== ===================================== ====================
	Property                             Data type                             Default
	==================================== ===================================== ====================
	`basic.enablePathSegment`_           :ref:`t3tsref:data-type-boolean`      true
	`basic.realURLAutoConf`_             :ref:`t3tsref:data-type-boolean`      true
	`basic.tsConfigPid`_                 :ref:`t3tsref:data-type-integer`      1
	`page.maxTitle`_                     :ref:`t3tsref:data-type-integer`      57
	`page.maxDescription`_               :ref:`t3tsref:data-type-integer`      156
	`page.maxNavTitle`_                  :ref:`t3tsref:data-type-integer`      50
	`evaluation.inPageModule`_           :ref:`t3tsref:data-type-integer`      0
	`evaluation.evaluationDoktypes`_     :ref:`t3tsref:data-type-string`       1
	`evaluation.evaluators`_             :ref:`t3tsref:data-type-string`       Title,Description,H1,H2,Images,Keyword
	`evaluation.minTitle`_               :ref:`t3tsref:data-type-integer`      40
	`evaluation.minDescription`_         :ref:`t3tsref:data-type-integer`      140
	`evaluation.maxH2`_                  :ref:`t3tsref:data-type-integer`      6
	==================================== ===================================== ====================

Basic configurations
^^^^^^^^^^^^^^^^^^^^

.. _basic.enablePathSegment:

Set path segments
"""""""""""""""""

.. container:: table-row

   Property
         enablePathSegment
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         If enabled, a JS is insert in the page settings, so that the RealURL pathsegment will be filled, if it is empty.
         This prevents, that if an editor changes the URL, the link also changes.

.. _basic.realURLAutoConf:

RealURL AutoConf
""""""""""""""""

.. container:: table-row

   Property
         realURLAutoConf
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         If set, sitemap.xml and robots.txt maps to the page types from cs_seo.

.. _basic.tsConfigPid:

TsConfig PID
""""""""""""

.. container:: table-row

   Property
         tsConfigPid
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         Enter the uid of the page where the page TSConfig is stored to extend records, e.g. news.


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


Evaluation
^^^^^^^^^^

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
         The Evaluation can also be disabled via TSconfig: mod.web_layout.tx_csseo.disable = 1.

.. _evaluation.evaluationDoktypes:

Doktypes
""""""""

.. container:: table-row

   Property
         evaluationDoktypes
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Comma separated list of allowed page doktypes for the evaluation. This doktypes must be callable in the frontend!


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

.. _evaluation.minTitle:

Min characters of an optimal title
""""""""""""""""""""""""""""""""""

.. container:: table-row

   Property
         minTitle
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         The recommended minimum number of characters for the meta title tag. Only used for evaluation.

.. _evaluation.minDescription:

Min characters of an optimal description
""""""""""""""""""""""""""""""""""""""""

.. container:: table-row

   Property
         minDescription
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         The recommended minimum number of characters for the meta description tag. Only used for evaluation.

.. _evaluation.maxH2:

Max number of h2 headlines in one page
""""""""""""""""""""""""""""""""""""""

.. container:: table-row

   Property
         maxH2
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         Determine how many headings h2 are allowed. Only used for evaluation.


Please take also a look at the next chapter for TypoScript configurations.