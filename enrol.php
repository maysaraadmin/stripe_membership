<?php
/**
 * Stripe Membership enrolment plugin enrolment method.
 *
 * @package   enrol_stripe_membership
 * @copyright Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/enrollib.php');

/**
 * Stripe Membership enrolment plugin implementation.
 */
class enrol_stripe_membership extends enrol_plugin {

    /**
     * Add enrolment instance to a course.
     *
     * @param stdClass $course The course to add instance to.
     * @param array $fields Optional fields for enrolment instance.
     * @return int ID of new enrolment instance.
     */
    public function add_instance($course, array $fields = null) {
        global $DB;

        $fields = (array)$fields;
        $fields['status'] = isset($fields['status']) ? $fields['status'] : ENROL_INSTANCE_ENABLED;
        $fields['courseid'] = $course->id;

        return $DB->insert_record('enrol', $fields);
    }

    /**
     * Check if a user can enrol.
     *
     * @param stdClass $instance The enrolment instance.
     * @return bool True if user can enrol.
     */
    public function can_enrol($instance) {
        return true; // Modify based on conditions (e.g., user roles).
    }

    /**
     * Enrol a user into a course.
     *
     * @param stdClass $instance The enrolment instance.
     * @param int $userid User ID.
     * @param int|null $roleid Role ID (default: student).
     * @param int $timestart Start time for enrolment.
     * @param int $timeend End time for enrolment.
     * @param int $status Enrolment status (default: ENROL_USER_ACTIVE).
     */
    public function enrol_user($instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = ENROL_USER_ACTIVE) {
        global $DB;

        $context = context_course::instance($instance->courseid);

        if ($roleid) {
            role_assign($roleid, $userid, $context->id);
        }

        $ue = $DB->get_record('user_enrolments', [
            'enrolid' => $instance->id,
            'userid' => $userid
        ]);

        if (!$ue) {
            $record = new stdClass();
            $record->status = $status;
            $record->enrolid = $instance->id;
            $record->userid = $userid;
            $record->timestart = $timestart;
            $record->timeend = $timeend;
            $record->timecreated = time();
            $record->timemodified = time();

            $DB->insert_record('user_enrolments', $record);
        }
    }

    /**
     * Unenrol a user from a course.
     *
     * @param stdClass $instance The enrolment instance.
     * @param int $userid User ID.
     */
    public function unenrol_user($instance, $userid) {
        global $DB;

        $context = context_course::instance($instance->courseid);
        role_unassign_all(['userid' => $userid, 'contextid' => $context->id], true);

        $DB->delete_records('user_enrolments', [
            'enrolid' => $instance->id,
            'userid' => $userid
        ]);
    }

    /**
     * Return enrolment instance name for display.
     *
     * @param stdClass $instance The enrolment instance.
     * @return string Enrolment instance name.
     */
    public function get_instance_name($instance) {
        if (!empty($instance->name)) {
            return format_string($instance->name, true, [
                'context' => context_course::instance($instance->courseid)
            ]);
        }

        return get_string('pluginname', 'enrol_stripe_membership');
    }

    /**
     * Process Stripe webhook notifications.
     *
     * @param string $payload Raw webhook payload.
     */
    public function process_webhook($payload) {
        global $DB;

        $event = json_decode($payload);

        if ($event->type === 'checkout.session.completed') {
            $userid = $event->data->object->client_reference_id;
            $instanceid = $event->data->object->metadata->instanceid;

            $instance = $DB->get_record('enrol', [
                'id' => $instanceid
            ], '*', MUST_EXIST);

            $this->enrol_user($instance, $userid, 5); // Default role: student.
        }
    }
}