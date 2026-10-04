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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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

namespace local_personalgoals;

use completion_info;
use core\event\base;
use core\event\course_completed;
use core\event\course_module_completion_updated;
use core\event\course_module_deleted;
use local_personalgoals\service\activity_tracking;
use local_personalgoals\service\event_dedupe;
use local_personalgoals\service\json;
use local_personalgoals\service\progress;
use local_personalgoals\service\refresh;
use mod_quiz\event\attempt_submitted;

/**
 * Class observer.
 */
final class observer {
    /**
     * Method learner_id.
     *
     * @param base $event Parameter event.
     * @return int Return value.
     */
    private static function learner_id(base $event): int {
        return (int)($event->relateduserid ?: $event->userid);
    }

    /**
     * Method common.
     *
     * @param base $event Parameter event.
     * @return ?array Return value.
     */
    private static function common(base $event): ?array {
        if (!event_dedupe::claim($event)) {
            return null;
        }
        $userid = self::learner_id($event);
        $courseid = (int)$event->courseid;
        if ($userid <= 0 || $courseid <= 1) {
            return null;
        }
        activity_tracking::record_interaction($userid, $courseid, (int)$event->timecreated);
        return [$userid, $courseid];
    }

    /**
     * Method learning_interaction.
     *
     * @param base $event Parameter event.
     * @return void Return value.
     */
    public static function learning_interaction(base $event): void {
        $ids = self::common($event);
        if (!$ids) {
            return;
        }
        [$userid, $courseid] = $ids;
        refresh::user_course($userid, $courseid, ['xp', 'active_days', 'study_time']);
    }

    /**
     * Method course_module_completion_updated.
     *
     * @param course_module_completion_updated $event Parameter event.
     * @return void Return value.
     */
    public static function course_module_completion_updated(course_module_completion_updated $event): void {
        global $DB;
        $ids = self::common($event);
        if (!$ids) {
            return;
        }
        [$userid, $courseid] = $ids;
        $cmid = (int)$event->contextinstanceid;
        $state = $event->other['completionstate'] ?? null;
        if ($state === null) {
            $course = get_course($courseid);
            $completion = new completion_info($course);
            $cm = get_fast_modinfo($courseid, $userid)->get_cm($cmid);
            $data = $completion->get_data($cm, false, $userid);
            $state = $data->completionstate;
        }
        $completed = (int)$state !== COMPLETION_INCOMPLETE;

        $goals = $DB->get_records_select('local_personalgoals_goals',
            'userid = :userid AND courseid = :courseid AND status = :status AND timestart <= :now1 '
                . 'AND (timeend = 0 OR timeend >= :now2) AND goaltype IN (:g1, :g2)',
            [
                'userid' => $userid,
                'courseid' => $courseid,
                'status' => api::STATUS_ACTIVE,
                'now1' => (int)$event->timecreated,
                'now2' => (int)$event->timecreated,
                'g1' => 'activity_completion',
                'g2' => 'specific_activities',
            ]
        );
        foreach ($goals as $goal) {
            if ($goal->goaltype === 'specific_activities') {
                $config = json::decode($goal->configjson);
                if (!in_array($cmid, array_map('intval', $config['cmids'] ?? []), true)) {
                    continue;
                }
            }
            progress::set_item_state((int)$goal->id, $cmid, $completed);
            refresh::goal($goal);
        }
        refresh::user_course($userid, $courseid, ['xp', 'active_days', 'course_completion', 'study_time']);
    }

    /**
     * Method quiz_attempt_submitted.
     *
     * @param attempt_submitted $event Parameter event.
     * @return void Return value.
     */
    public static function quiz_attempt_submitted(attempt_submitted $event): void {
        global $DB;
        $ids = self::common($event);
        if (!$ids) {
            return;
        }
        [$userid, $courseid] = $ids;
        $goals = $DB->get_records_select('local_personalgoals_goals',
            'userid = :userid AND courseid = :courseid AND goaltype = :goaltype AND status = :status '
                . 'AND timestart <= :now1 AND (timeend = 0 OR timeend >= :now2)',
            [
                'userid' => $userid,
                'courseid' => $courseid,
                'goaltype' => 'quiz_attempts',
                'status' => api::STATUS_ACTIVE,
                'now1' => (int)$event->timecreated,
                'now2' => (int)$event->timecreated,
            ]
        );
        foreach ($goals as $goal) {
            progress::increment((int)$goal->id, 1);
            refresh::goal($goal);
        }
        refresh::user_course($userid, $courseid, ['xp', 'active_days', 'course_completion', 'study_time']);
    }

    /**
     * Method course_completed.
     *
     * @param course_completed $event Parameter event.
     * @return void Return value.
     */
    public static function course_completed(course_completed $event): void {
        $ids = self::common($event);
        if (!$ids) {
            return;
        }
        [$userid, $courseid] = $ids;
        refresh::user_course($userid, $courseid, ['xp', 'active_days', 'course_completion', 'study_time']);
    }

    /**
     * Method course_module_deleted.
     *
     * @param course_module_deleted $event Parameter event.
     * @return void Return value.
     */
    public static function course_module_deleted(course_module_deleted $event): void {
        global $DB;
        if (!event_dedupe::claim($event)) {
            return;
        }
        $courseid = (int)$event->courseid;
        $cmid = (int)$event->objectid;
        if ($courseid <= 1 || $cmid <= 0) {
            return;
        }
        $goals = $DB->get_records('local_personalgoals_goals', [
            'courseid' => $courseid,
            'goaltype' => 'specific_activities',
            'status' => api::STATUS_ACTIVE,
        ]);
        foreach ($goals as $goal) {
            $config = json::decode($goal->configjson);
            $cmids = array_values(array_unique(array_map('intval', $config['cmids'] ?? [])));
            if (!in_array($cmid, $cmids, true)) {
                continue;
            }
            $cmids = array_values(array_filter($cmids, static fn(int $id): bool => $id !== $cmid));
            $missing = array_values(array_unique(array_map('intval', $config['missingactivities'] ?? [])));
            $missing[] = $cmid;
            $config['cmids'] = $cmids;
            $config['missingactivities'] = array_values(array_unique($missing));
            $goal->configjson = json::encode($config);
            if ($cmids) {
                $goal->targetvalue = count($cmids);
            }
            $goal->timemodified = time();
            $DB->update_record('local_personalgoals_goals', $goal);
            progress::set_item_state((int)$goal->id, $cmid, false);
            if ($cmids) {
                refresh::goal($goal);
            }
        }
    }
}
