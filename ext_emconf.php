<?php

$EM_CONF[$_EXTKEY] = [
    'title' => '[clickstorm] SEO',
    'description' => 'SEO Extension: Enables important onpage features for search engine optimization (SEO). Expands
						the page settings and any desired records for example with a preview for Google search results (SERP)
						Structured Data (JSON-LD) and a Focus Keyword. Restrictive hreflang and canonical tags. Modules for
						metadata of records and alternative texts of images. Further features are shown in the extension manual.',
    'category' => 'services',
    'author' => 'Pascale Beier, Angela Dudtkowski, Marc Hirdes, Andreas Kirilow, Alexander König - clickstorm GmbH',
    'author_email' => 'hirdes@clickstorm.de',
    'author_company' => 'clickstorm GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '9.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.3.0-13.4.99',
            'seo' => '12.3.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Clickstorm\\CsSeo\\' => 'Classes',
        ],
    ],
];
