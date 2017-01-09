<?php

namespace Clickstorm\CsSeo\Hook;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Christian Forgács <christian@wunderbit.de>, wunderbit GmbH & Co. KG
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Hooks for RealUrl
 *
 * Class RealUrlHook
 * @package Clickstorm\CsSeo\Hook
 */
class RealUrlHook {

	/**
	 * RealURL configuration array for robots.txt and sitemap.xml
	 *
	 * @var array
	 */
	protected $defaultConfiguration = [
		'fileName' => [
			'index' => [
				'robots.txt' => [
					'keyValues' => [
						'type' => 656,
					],
				],
				'sitemap.xml' => [
					'keyValues' => [
						'type' => 655,
					],
				],
			],
		],
	];

	/**
	 * Generates additional RealURL configuration for realurl autoconf and merges it with provided configuration
	 * For detailed configuration documentation look into the manual ({@link https://wiki.typo3.org/Realurl/manual})
	 *
	 * @param array $parameters
	 * @param \DmitryDulepov\Realurl\Configuration\AutomaticConfigurator $parentObject
	 * @return array
	 */
	public function extensionConfiguration($parameters, &$parentObject) {
		return array_replace_recursive($parameters['config'], $this->defaultConfiguration);
	}

	/**
	 * Generates additional RealURL configuration for non realurl autoconf and merges it with provided configuration
	 * For detailed configuration documentation look into the manual ({@link https://wiki.typo3.org/Realurl/manual})
	 *
	 * @param array $parameters
	 * @param \DmitryDulepov\Realurl\Configuration\ConfigurationReader $parentObject
	 * @return void
	 */
	public function postProcessConfiguration($parameters, &$parentObject) {
		$parameters['configuration'] = array_merge_recursive($parameters['configuration'], $this->defaultConfiguration);
	}

}