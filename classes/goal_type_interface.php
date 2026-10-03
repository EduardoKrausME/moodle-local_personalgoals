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

namespace local_personalgoals;

defined('MOODLE_INTERNAL') || die();

/**
 * Contract for extensible personal goal types.
 */
interface goal_type_interface {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string;
    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string;
    /**
     * Method get_current_value.
     *
     * @return float Return value.
     */
    public function get_current_value(): float;
    /**
     * Method get_target_value.
     *
     * @return float Return value.
     */
    public function get_target_value(): float;
    /**
     * Method get_progress.
     *
     * @return float Return value.
     */
    public function get_progress(): float;
    /**
     * Method is_completed.
     *
     * @return bool Return value.
     */
    public function is_completed(): bool;

    /**
     * Validate configuration and return field => error pairs.
     *
     * @param array $config
     * @return array
     */
    public function validate_configuration(array $config): array;
}
