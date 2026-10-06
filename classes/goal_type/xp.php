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

namespace local_personalgoals\goal_type;

use local_personalgoals\service\integrations;
use local_personalgoals\service\json;

/**
 * Class xp.
 */
final class xp extends base_goal_type {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('goaltype_xp', 'local_personalgoals');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('goaltype_xp_desc', 'local_personalgoals');
    }

    /**
     * Method get_current_value.
     *
     * @return float Return value.
     */
    public function get_current_value(): float {
        $total = integrations::current_xp((int)$this->goal->userid, (int)$this->goal->courseid);
        if ($total === null) {
            return parent::get_current_value();
        }
        $config = json::decode($this->goal->configjson);
        $baseline = (int)($config['xpbaseline'] ?? $total);
        return max(0, $total - $baseline);
    }

    /**
     * Method validate_configuration.
     *
     * @param array $config Parameter config.
     * @return array Return value.
     */
    public function validate_configuration(array $config): array {
        if (integrations::current_xp((int)$this->goal->userid, (int)$this->goal->courseid) === null) {
            return ['goaltype' => get_string('xpunavailable', 'local_personalgoals')];
        }
        return [];
    }
}
