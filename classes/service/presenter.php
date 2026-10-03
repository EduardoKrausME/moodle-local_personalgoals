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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_personalgoals\service;

use local_personalgoals\api;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class presenter.
 */
final class presenter {
    /**
     * Method goal.
     *
     * @param stdClass $goal Parameter goal.
     * @return array Return value.
     */
    public static function goal(stdClass $goal): array {
        global $DB;
        $state = refresh::goal($goal);
        $course = $DB->get_record('course', ['id' => $goal->courseid], 'id,fullname', MUST_EXIST);
        $current = self::format_value($goal->goaltype, $state['current']);
        $target = self::format_value($goal->goaltype, $state['target']);
        $status = (string)$state['status'];
        $period = json::decode($goal->configjson)['periodtype'] ?? 'none';

        $data = [
            'id' => (int)$goal->id,
            'courseid' => (int)$goal->courseid,
            'coursename' => format_string($course->fullname),
            'name' => format_string($goal->name),
            'goaltype' => (string)$goal->goaltype,
            'current' => $current,
            'target' => $target,
            'progress' => (int)round($state['percentage']),
            'status' => $status,
            'statuslabel' => get_string('status' . $status, 'local_personalgoals'),
            'isactive' => $status === api::STATUS_ACTIVE,
            'iscompleted' => $status === api::STATUS_COMPLETED,
            'isexpired' => $status === api::STATUS_EXPIRED,
            'iscancelled' => $status === api::STATUS_CANCELLED,
            'hasdeadline' => (int)$goal->timeend > 0,
            'deadline' => (int)$goal->timeend > 0 ? userdate((int)$goal->timeend, get_string('strftimedatetime', 'langconfig')) : '',
            'periodlabel' => get_string('period' . $period, 'local_personalgoals'),
            'expiredmessage' => get_string('expiredneutral', 'local_personalgoals', (object)[
                'current' => $current,
                'target' => $target,
            ]),
            'cancelurl' => (new moodle_url('/local/personalgoals/action.php', [
                'action' => 'cancel', 'id' => $goal->id, 'sesskey' => sesskey(),
            ]))->out(false),
            'repeaturl' => (new moodle_url('/local/personalgoals/action.php', [
                'action' => 'repeat', 'id' => $goal->id, 'sesskey' => sesskey(),
            ]))->out(false),
        ];
        return $data;
    }

    /**
     * Method format_value.
     *
     * @param string $goaltype Parameter goaltype.
     * @param float $value Parameter value.
     * @return string Return value.
     */
    public static function format_value(string $goaltype, float $value): string {
        switch ($goaltype) {
            case 'xp':
                return get_string('valuexp', 'local_personalgoals', format_float($value, 0));
            case 'study_time':
                return get_string('valueminutes', 'local_personalgoals', format_float($value, 0));
            case 'course_completion':
                return get_string('valuepercentage', 'local_personalgoals', format_float($value, 0));
            default:
                return format_float($value, 0);
        }
    }

    /**
     * Method monthly_history.
     *
     * @param array $goals Parameter goals.
     * @return array Return value.
     */
    public static function monthly_history(array $goals): array {
        $months = [];
        foreach ($goals as $goal) {
            $key = userdate((int)$goal->timecreated, '%Y-%m');
            if (!isset($months[$key])) {
                $months[$key] = [
                    'key' => $key,
                    'label' => userdate((int)$goal->timecreated, '%B %Y'),
                    'created' => 0,
                    'completed' => 0,
                    'expired' => 0,
                    'cancelled' => 0,
                ];
            }
            $months[$key]['created']++;
            if (isset($months[$key][$goal->status])) {
                $months[$key][$goal->status]++;
            }
        }
        krsort($months);
        return array_values($months);
    }
}
