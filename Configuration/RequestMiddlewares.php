<?php

return [
    'frontend' => [
        'werkraum/abuseip' => [
            'target' => \Werkraum\Abuseip\Middleware\Abuseip::class,
            'before' => [
                'typo3/cms-core/verify-host-header',
            ],
        ],
    ],
];