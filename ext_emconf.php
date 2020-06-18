<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "cs_seo"
 *
 * Auto generated by Extension Builder 2016-04-06
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => '[clickstorm] SEO',
    'description' => 'SEO Extension: Enables important on-page features for search engine optimization (SEO). Expands
						the page settings and any existing records, e.g. with a preview for Google search results (SERP),
						and a Focus Keyword. Robots.txt handling. Support for Session Tracking (Google Analytics or Matomo)
						and href="lang" tags. Further features are shown in the extension manual.',
    'category' => 'services',
    'author' => 'Pascale Beier, Angela Dudtkowski, Marc Hirdes, Andreas Kirilow, Alexander König - clickstorm GmbH',
    'author_email' => 'hirdes@clickstorm.de',
    'author_company' => 'clickstorm GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '5.0.1-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '10.0.0-10.4.99',
            'seo' => '10.0.0-10.4.99'
        ]
    ],
    'autoload' => [
        'psr-4' => [
            'Clickstorm\\CsSeo\\' => 'Classes'
        ]
    ],
];
