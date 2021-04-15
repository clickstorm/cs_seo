<?php

$EM_CONF[$_EXTKEY] = [
    'title' => '[clickstorm] SEO',
    'description' => 'SEO Extension: Enables important onpage features for search engine optimization (SEO). Expands
						the page settings and any desired records for example with a preview for Google search results (SERP)
						and a Focus Keyword. Restrective hreflang and canonical tags. Modules for metadata of records and
						alternative texts of images. Further features are shown in the extension manual.',
    'category' => 'services',
    'author' => 'Pascale Beier, Angela Dudtkowski, Marc Hirdes, Andreas Kirilow, Alexander König - clickstorm GmbH',
    'author_email' => 'hirdes@clickstorm.de',
    'author_company' => 'clickstorm GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '6.2.1-dev',
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
