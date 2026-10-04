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

namespace local_personalgoals\goal_type;

use local_personalgoals\goal_type_interface;
use local_personalgoals\service\progress;
use stdClass;

/**
 * Class base_goal_type.
 */
abstract class base_goal_type implements goal_type_interface {
    /**
     * Property goal.
     *
     * @var stdClass
     */
    protected stdClass $goal;

    /**
     * Method __construct.
     *
     * @param stdClass $goal Parameter goal.
     */
    public function __construct(stdClass $goal) {
        $this->goal = $goal;
    }

    /**
     * Method get_target_value.
     *
     * @return float Return value.
     */
    public function get_target_value(): float {
        return max(0.0, (float)$this->goal->targetvalue);
    }

    /**
     * Method get_current_value.
     *
     * @return float Return value.
     */
    public function get_current_value(): float {
        return progress::get_value((int)$this->goal->id);
    }

    /**
     * Method get_progress.
     *
     * @return float Return value.
     */
    public function get_progress(): float {
        $target = $this->get_target_value();
        if ($target <= 0) {
            return 0.0;
        }
        return max(0.0, min(100.0, ($this->get_current_value() / $target) * 100.0));
    }

    /**
     * Method is_completed.
     *
     * @return bool Return value.
     */
    public function is_completed(): bool {
        return $this->get_target_value() > 0 && $this->get_current_value() >= $this->get_target_value();
    }

    /**
     * Method validate_configuration.
     *
     * @param array $config Parameter config.
     * @return array Return value.
     */
    public function validate_configuration(array $config): array {
        return [];
    }
}
