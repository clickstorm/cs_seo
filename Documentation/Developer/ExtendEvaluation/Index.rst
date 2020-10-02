.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _extend-evaluation:

Write your own evaluators
^^^^^^^^^^^^^^^^^^^^^^^^^

In the extension manager you can define which evaluators should be available on the current page.
If you like to, you can also add your own analysis scripts and show the results in the backend.

.. _MyEvaluator.php:

1. Create a new evaluator
-------------------------

.. code-block:: php

	<?php
	namespace Vendor\ExtensionName\Evaluation;

	use Clickstorm\CsSeo\Evaluation\AbstractEvaluator;

	class MyEvaluator extends AbstractEvaluator
	{

		public function evaluate() {
			$state = self::STATE_RED;

			$count = $this->domDocument->getElementsByTagName('h1')->length;

			if($count > 0 && $count < 2) {
				$state =  self::STATE_GREEN;
			}

			return [
				'count' => $count,
				'state' => $state
			];
		}

	}

The class must extend the AbstractEvaluator class. You get the current domDocument with $this->domDocument.
You must return an arry that contains at least the key state. This is important for the final percentage calculation.

.. _ext_localconf.php:

2. Register the evaluator
-------------------------

Add the following configurition in your ext_localconf.php.

.. code-block:: php

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cs_seo']['evaluators']['My'] =
		\Vendor\ExtensionName\Evaluation\MyEvaluator::class;

The key is important. In the results the partial with the path Results\\My will be called for the output.


.. _partials:

3. Create a partial and add the partialRootPaths
------------------------------------------------

.. code-block:: typoscript
   
   module.tx_csseo.view.partialRootPaths.10 = EXT:my_ext/Resources/Private/Partials


In the partial folder create a folder called Results. In this folder insert your partial file My.html.

.. code-block:: html

	<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
	      xmlns:f="http://typo3.org/ns/fluid/ViewHelpers">
	<body>
	<f:section name="Main">
		<li class="cs-icon csseo-icon-state-{result.state}">
			<b><f:translate key="evaluation.my" extensionName="my_extension" /></b> <f:translate key="evaluation.my.{result.state}" arguments="{0: result.count}" extensionName="my_extension" />.
		</li>
	</f:section>
	</body>

.. _extconf:

4. Add the evaluator in the extension manager settings
------------------------------------------------------

Add the evaluator in the extension manager to the available evaluators, e.g. H1,H2,My. The key equals the key in the array where you registered the evaulator.
