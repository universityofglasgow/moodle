<?php

namespace theme_gu28\task;

class instagram_reload extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('instagramreload', 'theme_gu28');
    }

    public function execute() {

        // Purging the cache will cause it to be reloaded on next view of
        // login page. 
        $cache = \cache::make('theme_gu28', 'instagram');
        $cache->purge();
    }
}
