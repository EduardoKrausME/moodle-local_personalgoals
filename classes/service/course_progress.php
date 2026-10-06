<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalgoals\service;

/**
 * Class course_progress.
 */
final class course_progress {
    /**
     * Method percentage.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @return float Return value.
     */
    public static function percentage(int $userid, int $courseid): float {
        global $DB;
        $course = $DB->get_record('course', ['id' => $courseid], '*', IGNORE_MISSING);
        if (!$course) {
            return 0.0;
        }
        $percentage = \core_completion\progress::get_course_progress_percentage($course, $userid);
        if ($percentage === null) {
            return 0.0;
        }
        return max(0.0, min(100.0, (float)$percentage));
    }
}
