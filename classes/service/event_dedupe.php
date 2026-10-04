<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalgoals\service;

use core\event\base;
use dml_write_exception;

/**
 * Class event_dedupe.
 */
final class event_dedupe {
    /**
     * Method claim.
     *
     * @param base $event Parameter event.
     * @return bool Return value.
     */
    public static function claim(base $event): bool {
        global $DB;
        $data = $event->get_data();
        if (!empty($data['id'])) {
            $identity = get_class($event) . '|id|' . $data['id'];
        } else {
            $identity = get_class($event) . '|' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        $record = (object)[
            'eventkey' => hash('sha256', $identity),
            'timecreated' => time(),
        ];
        try {
            $DB->insert_record('local_personalgoals_eventlog', $record);
            return true;
        } catch (dml_write_exception $e) {
            return false;
        }
    }
}
