<?php
/**
 * Created by PhpStorm.
 * User: mhirdes
 * Date: 18.08.2016
 * Time: 10:54
 */

namespace Clickstorm\CsSeo\Evaluation;


interface EvaluationInterface {

	/**
	 * Evaluate the html to a specific function
	 *
	 * @return array
	 * @api
	 */
	public function evaluate();
}