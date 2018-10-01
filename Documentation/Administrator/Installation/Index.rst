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
#. Include the TypoScript from the extension! This is shown in the screenshot below.
#. Insert a domain record at the root page.
#. Make some initial configurations, see :ref:`c_configuration`.
#. Run the Scheduler Task to evaluate all pages initially, see :ref:`scheduler-tasks`.
#. Take a look to :ref:`admin-faq`. **Be aware** that we force L=0 in URLs. Also check the content of the robots.txt.

|img-1|

**!If you forgot to include the TypoScript, you will get an error if you open the page settings!**