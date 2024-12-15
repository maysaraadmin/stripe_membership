<?php

namespace enrol_stripe_membership\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event triggered when a payment is successfully processed.
 */
class payment_successful extends \core\event\base {
    /**
     * Initialize event data.
     */
    protected function init() {
        $this->data['crud'] = 'c'; // Event represents a "create" action.
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING; // Participant-level event.
        $this->data['objecttable'] = 'user_enrolments'; // Target table for this event.
    }

    /**
     * Return localizable event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('payment_successful', 'enrol_stripe_membership');
    }

    /**
     * Get a description of the event.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '{$this->relateduserid}' successfully completed a payment and was enrolled in the course with id '{$this->objectid}'.";
    }

    /**
     * Return the URL related to this event.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/course/view.php', ['id' => $this->objectid]);
    }

    /**
     * Return the context of the event.
     *
     * @return \context_course
     */
    protected function get_context() {
        return \context_course::instance($this->objectid);
    }

    /**
     * Return additional event data for logging.
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return ['db' => 'user_enrolments', 'restore' => 'user_enrolment'];
    }
}