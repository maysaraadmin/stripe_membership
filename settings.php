<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'enrol_stripe_membership/publishablekey',
        get_string('publishablekey', 'enrol_stripe_membership'),
        get_string('publishablekey_desc', 'enrol_stripe_membership'),
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_stripe_membership/secretkey',
        get_string('secretkey', 'enrol_stripe_membership'),
        get_string('secretkey_desc', 'enrol_stripe_membership'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'enrol_stripe_membership/useaffiliates',
        get_string('useaffiliates', 'enrol_stripe_membership'),
        get_string('useaffiliates_desc', 'enrol_stripe_membership'),
        0
    ));
}
