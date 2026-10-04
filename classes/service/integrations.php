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

use stdClass;

/**
 * Class integrations.
 */
final class integrations {
    /**
     * Method current_xp.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @return ?int Return value.
     */
    public static function current_xp(int $userid, int $courseid): ?int {
        $class = '\\local_personalxp\\service\\xp_manager';
        if (!class_exists($class) || !method_exists($class, 'get_total')) {
            return null;
        }
        return (int)$class::get_total($userid, $courseid);
    }

    /**
     * Method award_xp.
     *
     * @param stdClass $goal Parameter goal.
     * @param int $amount Parameter amount.
     * @return bool Return value.
     */
    public static function award_xp(stdClass $goal, int $amount): bool {
        if ($amount <= 0) {
            return false;
        }
        $class = '\\local_personalxp\\service\\xp_manager';
        if (!class_exists($class) || !method_exists($class, 'award')) {
            return false;
        }
        return (bool)$class::award(
            (int)$goal->userid,
            (int)$goal->courseid,
            'personalgoal_completed',
            (int)$goal->id,
            $amount,
            (string)$goal->name,
            'local_personalgoals',
            '\\local_personalgoals\\event\\goal_completed'
        );
    }

    /**
     * Method award_credits.
     *
     * @param stdClass $goal Parameter goal.
     * @param int $amount Parameter amount.
     * @return bool Return value.
     */
    public static function award_credits(stdClass $goal, int $amount): bool {
        if ($amount <= 0) {
            return false;
        }
        $class = '\\local_rewardshop\\api';
        if (!class_exists($class)) {
            return false;
        }
        if (method_exists($class, 'grant_credits')) {
            return (bool)$class::grant_credits(
                (int)$goal->userid,
                (int)$goal->courseid,
                $amount,
                'personalgoal_completed',
                (int)$goal->id
            );
        }
        if (method_exists($class, 'credit')) {
            return (bool)$class::credit(
                (int)$goal->userid,
                (int)$goal->courseid,
                $amount,
                'personalgoal_completed',
                (int)$goal->id
            );
        }
        return false;
    }

    /**
     * Method celebrate.
     *
     * @param stdClass $goal Parameter goal.
     * @return void Return value.
     */
    public static function celebrate(stdClass $goal): void {
        $class = '\\local_xpcelebration\\api';
        if (!class_exists($class) || !method_exists($class, 'queue')) {
            return;
        }
        $class::queue(
            (int)$goal->userid,
            (int)$goal->courseid,
            'goal_completed',
            get_string('celebrationtitle', 'local_personalgoals'),
            get_string('celebrationmessage', 'local_personalgoals', format_string($goal->name)),
            ['goalid' => (int)$goal->id, 'progress' => 100]
        );
    }
}
