<?php

namespace enrol_gudatabase\task;

class sync_enrolments extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('scheduledname', 'enrol_gudatabase');
    }

    public function execute() {
        $plugin = enrol_get_plugin('gudatabase');
        $plugin->scheduled();
        \core\task\manager::clear_static_caches();
    }

}
