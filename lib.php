<?php
/**
 * Library file for the Stripe Membership enrolment plugin.
 *
 * @package   enrol_stripe_membership
 * @copyright Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Stripe Membership enrolment plugin implementation.
 */
class enrol_stripe_membership_plugin extends enrol_plugin {

    /**
     * Add new instance of enrolment plugin to a course.
     *
     * @param object $course The course object.
     * @param array $fields Array of instance configuration fields.
     * @return int ID of the newly created enrolment instance.
     */
    public function add_instance($course, array $fields = null) {
        global $DB;

        $fields = (array)$fields;
        $fields['status'] = isset($fields['status']) ? $fields['status'] : ENROL_INSTANCE_ENABLED;
        $fields['courseid'] = $course->id;

        return $DB->insert_record('enrol', $fields);
    }

    /**
     * Handles user enrolment based on a successful payment.
     *
     * @param object $instance The enrolment instance.
     * @param int $userid The ID of the user to enrol.
     * @param int $roleid Role ID for the user (default: student).
     * @param int $timestart Start time of enrolment (default: 0).
     * @param int $timeend End time of enrolment (default: 0).
     * @param int $status Enrolment status (default: ENROL_USER_ACTIVE).
     * @return void
     */
    public function enrol_user($instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = ENROL_USER_ACTIVE) {
        global $DB;

        $context = context_course::instance($instance->courseid);

        if ($roleid) {
            role_assign($roleid, $userid, $context->id);
        }

        $ue = $DB->get_record('user_enrolments', ['enrolid' => $instance->id, 'userid' => $userid]);
        if (!$ue) {
            $record = new stdClass();
            $record->status = $status;
            $record->enrolid = $instance->id;
            $record->userid = $userid;
            $record->timestart = $timestart;
            $record->timeend = $timeend;
            $record->modifierid = 0;
            $record->timecreated = time();
            $record->timemodified = time();
            $DB->insert_record('user_enrolments', $record);
        }
    }

    /**
     * Get the enrolment instance name for display.
     *
     * @param object $instance The enrolment instance.
     * @return string The instance name.
     */
    public function get_instance_name($instance) {
        if (isset($instance->name) && $instance->name !== '') {
            return format_string($instance->name, true, ['context' => context_course::instance($instance->courseid)]);
        }

        return get_string('pluginname', 'enrol_stripe_membership');
    }

    /**
     * Check if a user can be manually unenrolled.
     *
     * @param object $instance The enrolment instance.
     * @return bool True if manual unenrolment is allowed.
     */
    public function allow_unenrol_user($instance) {
        return true;
    }

    /**
     * Check if a user can manage this enrolment instance.
     *
     * @param object $instance The enrolment instance.
     * @return bool True if management is allowed.
     */
    public function can_manage_instance($instance) {
        return has_capability('enrol/stripe_membership:config', context_course::instance($instance->courseid));
    }

    /**
     * Process a Stripe webhook notification.
     *
     * @param string $payload The raw webhook payload.
     * @return void
     */
    public function process_webhook($payload) {
        global $DB;

        $event = json_decode($payload);

        if ($event->type === 'checkout.session.completed') {
            $userid = $event->data->object->client_reference_id;
            $instanceid = $event->data->object->metadata->instanceid;

            $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
            $this->enrol_user($instance, $userid, 5); // Default roleid = 5 (student).
        }
    }
}