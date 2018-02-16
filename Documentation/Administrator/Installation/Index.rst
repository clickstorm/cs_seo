.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: ../Images.txt

.. _admin-installation:

Installation
------------

To install the extension, perform the following steps:

#. Download and install the extension via Extension Manager or Composer
#. Include the TypoScript from the extension! This is shown in the screenshot below. Include
   optionally "Sitemap.xml for news" if you use tx_news.
#. Insert a domain record at the root page.
#. Make some initial configurations, see :ref:`c_configuration`.
#. Run the Scheduler Task to evaluate all pages initially, see :ref:`scheduler`.
#. Take a look to :ref:`admin-faq`. **Be aware** that we force L=0 in URLs. Furthermore we fill in the speaking path
   segment of realURL. See :ref:`basic.realURLAutoConf` if you don't need this.

|img-1|

**!If you forgot to include the TypoScript, you will get an error if you open the page settings!**

.. _scheduler:

Scheduler Task
^^^^^^^^^^^^^^

After the extensions is configured you can run a scheduler task to evaluate all pages at once. Therefore
choose the **Extbase-CommandController-Task (extbase)** and then the task **CsSeo Evaluation: update**. Save
and run the task.