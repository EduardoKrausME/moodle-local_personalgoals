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

namespace local_personalgoals\service;

use local_personalgoals\api;
use local_personalgoals\goal_type_manager;
use stdClass;

/**
 * Class refresh.
 */
final class refresh {
    /**
     * Method goal.
     *
     * @param stdClass $goal Parameter goal.
     * @param bool $allowcomplete Parameter allowcomplete.
     * @return array Return value.
     */
    public static function goal(stdClass $goal, bool $allowcomplete = true): array {
        if ($goal->status === 'active' && (int)$goal->timeend > 0 && (int)$goal->timeend < time()) {
            api::expire_goal((int)$goal->id);
            $goal->status = 'expired';
        }

        $type = goal_type_manager::for_goal($goal);
        $current = max(0.0, $type->get_current_value());
        if (in_array($goal->goaltype, ['xp', 'active_days', 'course_completion'], true)) {
            progress::set_value((int)$goal->id, $current);
        }
        $target = max(0.0, $type->get_target_value());
        $percentage = $target > 0 ? min(100.0, max(0.0, ($current / $target) * 100.0)) : 0.0;

        if ($allowcomplete && $goal->status === 'active' && $target > 0 && $current >= $target) {
            api::complete_goal((int)$goal->id);
            $goal->status = 'completed';
            $percentage = 100.0;
        }

        return [
            'current' => $current,
            'target' => $target,
            'percentage' => $percentage,
            'status' => $goal->status,
        ];
    }

    /**
     * Method user_course.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param ?array $types Parameter types.
     * @return void Return value.
     */
    public static function user_course(int $userid, int $courseid, ?array $types = null): void {
        global $DB;
        $params = [
            'userid' => $userid,
            'courseid' => $courseid,
            'status' => 'active',
        ];
        $typesql = '';
        if ($types) {
            [$insql, $inparams] = $DB->get_in_or_equal($types, SQL_PARAMS_NAMED, 'gt');
            $typesql = " AND goaltype $insql";
            $params += $inparams;
        }
        $goals = $DB->get_records_sql(
            "SELECT * FROM {local_personalgoals_goals}
              WHERE userid = :userid AND courseid = :courseid AND status = :status$typesql",
            $params
        );
        foreach ($goals as $goal) {
            self::goal($goal);
        }
    }
}
