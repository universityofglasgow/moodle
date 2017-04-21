<?php

/**
 * In this file are specified the timings that Moodle uses with cron in order to
 * periodically launch tasks.
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$tasks = array(
    /**
     * Export data
     */
    array(
        'classname' => 'block_gismo\task\export_data',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '2',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
