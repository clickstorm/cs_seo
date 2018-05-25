.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: ../Images.txt

.. _scheduler-tasks:

Evaluation
----------

To evaluate muliple items at oncec hoose the **Extbase-CommandController-Task (extbase)** and then the task
**CsSeo Evaluation: update**. Save and run the task.

Properties
^^^^^^^^^^

.. container:: ts-properties

	==================================== ===================================== ====================
	Property                             Data type                             Default
	==================================== ===================================== ====================
	`evaluation.uid`_                    :ref:`t3tsref:data-type-integer`      0
	`evaluation.tableName`_              :ref:`t3tsref:data-type-integer`      pages
	==================================== ===================================== ====================

Basic configurations
^^^^^^^^^^^^^^^^^^^^

.. _evaluation.uid:

uid
"""

.. container:: table-row

   Property
         Uid
   Data type
         :ref:`t3tsref:data-type-integer`
   Description
         If you want to evaluate only a sinlge item, then enter here the uid. Otherwise all not deleted and disabled
         items will be analysed.


.. _evaluation.tableName:

table name
""""""""""

.. container:: table-row

   Property
         tableName
   Data type
         :ref:`t3tsref:data-type-string`
   Description
         Enter a table name if you want to evaluate another table then pages. Localizations e.g. page_language_overlay
         will be fetched automatically.