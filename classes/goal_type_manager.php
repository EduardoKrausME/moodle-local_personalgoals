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

use moodle_exception;
use stdClass;
use Throwable;

/**
 * Class goal_type_manager.
 */
final class goal_type_manager {
    /**
     * Method get_types.
     *
     * @return array Return value.
     */
    public static function get_types(): array {
        $types = [
            'xp' => '\\local_personalgoals\\goal_type\\xp',
            'active_days' => '\\local_personalgoals\\goal_type\\active_days',
            'activity_completion' => '\\local_personalgoals\\goal_type\\activity_completion',
            'course_completion' => '\\local_personalgoals\\goal_type\\course_completion',
            'quiz_attempts' => '\\local_personalgoals\\goal_type\\quiz_attempts',
            'specific_activities' => '\\local_personalgoals\\goal_type\\specific_activities',
            'study_time' => '\\local_personalgoals\\goal_type\\study_time',
        ];

        if (function_exists('get_plugins_with_function')) {
            foreach (get_plugins_with_function('personalgoals_goal_types', 'lib.php') as $plugins) {
                foreach ($plugins as $callback) {
                    try {
                        $provided = $callback();
                        if (!is_array($provided)) {
                            continue;
                        }
                        foreach ($provided as $key => $classname) {
                            if (is_string($key) && is_string($classname) && class_exists($classname)
                                && is_subclass_of($classname, goal_type_interface::class)) {
                                $types[$key] = $classname;
                            }
                        }
                    } catch (Throwable $e) {
                        debugging('Unable to load personal goal type provider: ' . $e->getMessage(), DEBUG_DEVELOPER);
                    }
                }
            }
        }

        return $types;
    }

    /**
     * Method get_available_types.
     *
     * @param int $courseid Parameter courseid.
     * @return array Return value.
     */
    public static function get_available_types(int $courseid): array {
        $result = [];
        foreach (self::get_types() as $key => $classname) {
            if ($key === 'xp') {
                $xpclass = '\\local_personalxp\\service\\xp_manager';
                if (!class_exists($xpclass) || (method_exists($xpclass, 'is_enabled') && !$xpclass::is_enabled())) {
                    continue;
                }
            }
            $limit = service\limits::get($courseid, $key);
            if (!(int)$limit->enabled) {
                continue;
            }
            $dummy = (object)[
                'id' => 0,
                'userid' => 0,
                'courseid' => $courseid,
                'targetvalue' => 1,
                'configjson' => null,
            ];
            $instance = new $classname($dummy);
            $result[$key] = [
                'key' => $key,
                'name' => $instance->get_name(),
                'description' => $instance->get_description(),
            ];
        }
        return $result;
    }

    /**
     * Method get_type_name.
     *
     * @param string $key Parameter key.
     * @param int $courseid Parameter courseid.
     * @return string Return value.
     */
    public static function get_type_name(string $key, int $courseid = 0): string {
        $types = self::get_types();
        if (!isset($types[$key])) {
            return $key;
        }
        $dummy = (object)[
            'id' => 0,
            'userid' => 0,
            'courseid' => $courseid,
            'targetvalue' => 1,
            'configjson' => null,
        ];
        $classname = $types[$key];
        return (new $classname($dummy))->get_name();
    }

    /**
     * Method for_goal.
     *
     * @param stdClass $goal Parameter goal.
     * @return goal_type_interface Return value.
     */
    public static function for_goal(stdClass $goal): goal_type_interface {
        $types = self::get_types();
        if (!isset($types[$goal->goaltype])) {
            throw new moodle_exception('unknowngoaltype', 'local_personalgoals', '', $goal->goaltype);
        }
        $classname = $types[$goal->goaltype];
        return new $classname($goal);
    }
}
