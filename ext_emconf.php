<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'AbuseIp',
    'description' => 'Middleware to block IP addresses from known sources',
    'category' => 'plugin',
    'version' => '1.0.1',
    'author' => 'Lukas Niestroj',
    'author_email' => 'lukas.niestroj@werkraum.net',
    'author_company' => 'werkraum.net',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.2-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];