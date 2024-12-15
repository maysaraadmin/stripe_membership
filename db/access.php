<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'enrol/stripe_membership:config' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
