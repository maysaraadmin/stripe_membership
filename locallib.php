<?php
function enrol_stripe_membership_handle_webhook($payload) {
    global $DB;

    $event = json_decode($payload);

    if ($event->type === 'checkout.session.completed') {
        $userid = $event->data->object->client_reference_id;
        $instanceid = $event->data->object->metadata->instanceid;

        // Enroll the user in the course
        $plugin = enrol_get_plugin('stripe_membership');
        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
        $plugin->enrol_user($instance, $userid, 5); // Default roleid = 5 (student).
    }
}