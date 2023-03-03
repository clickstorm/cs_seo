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

	======================================================= ===================================== ====================
	Property                                                Data type                             Default
	======================================================= ===================================== ====================
	`basic.useAdditionalCanonicalizedUrlParametersOnly`_    :ref:`t3tsref:data-type-boolean`      false
	`page.maxTitle`_                                        :ref:`t3tsref:data-type-integer`      57
	`page.maxDescription`_                                  :ref:`t3tsref:data-type-integer`      156
	`page.cropDescription`_                                 :ref:`t3tsref:data-type-boolean`      false
	`page.maxNavTitle`_                                     :ref:`t3tsref:data-type-integer`      50
    `page.showDescriptionsInTCA`_                           :ref:`t3tsref:data-type-boolean`      true
	`evaluation.inPageModule`_                              :ref:`t3tsref:data-type-integer`      0
	`evaluation.evaluationDoktypes`_                        :ref:`t3tsref:data-type-string`       1
	`evaluation.evaluators`_                                :ref:`t3tsref:data-type-string`       Title,Description,H1,H2,Images,Keyword
	`evaluation.minTitle`_                                  :ref:`t3tsref:data-type-integer`      40
	`evaluation.minDescription`_                            :ref:`t3tsref:data-type-integer`      140
	`evaluation.forceMinDescription`_                       :ref:`t3tsref:data-type-boolean`      true
	`evaluation.maxH2`_                                     :ref:`t3tsref:data-type-integer`      6
	`file.modFileColumns`_                                  :ref:`t3tsref:data-type-string`       title,description
	======================================================= ===================================== ====================

Basic configurations
^^^^^^^^^^^^^^^^^^^^

.. _basic.useAdditionalCanonicalizedUrlParametersOnly:

use AdditionalCanonicalizedUrlParameters only
"""""""""""""""""""""""""""""""""""""""""""""

.. container:: table-row

   Property
         useAdditionalCanonicalizedUrlParametersOnly
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         Only the [FE][additionalCanonicalizedUrlParameters] are considered for canonical and hreflang. All other
		 parameters are ignored, also config.linkVars.


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


.. _page.cropDescription:

Crop description
""""""""""""""""

.. container:: table-row

   Property
         cropDescription
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         Crop description for extended tables, if it exceeds "Max characters of description".


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

.. _page.showDescriptionsInTCA:

Show Descriptions in TCA
""""""""""""""""""""""""

.. container:: table-row

   Property
         showDescriptionsInTCA
   Data type
         :ref:`t3tsref:data-type-boolean`
   Description
         Display the cs_seo descriptions for SEO fields in pages TCA. The descriptions will be shown below the labels
         in the TYPO3 backend. They help editors to understand the several fields.


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

.. _evaluation.forceMinDescription:

Force the min length in TCA
"""""""""""""""""""""""""""

.. container:: table-row

   Property
         forceMinDescription
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         If true, the editor can only save the description if the minimum length is reached.

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


File configurations
^^^^^^^^^^^^^^^^^^^

.. _file.modFileColumns:

File Columns
""""""""""""

.. container:: table-row

   Property
         modFileColumns
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Comma separated list of additional sys_file_metadata columns to show in the file module.
