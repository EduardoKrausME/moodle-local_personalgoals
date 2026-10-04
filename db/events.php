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

defined('MOODLE_INTERNAL') || die;

$observers = [
    [
        'eventname' => '\\core\\event\\course_viewed',
        'callback' => '\\local_personalgoals\\observer::learning_interaction',
        'priority' => -100,
    ],
    [
        'eventname' => '\\core\\event\\course_module_viewed',
        'callback' => '\\local_personalgoals\\observer::learning_interaction',
        'priority' => -100,
    ],
    [
        'eventname' => '\\mod_forum\\event\\post_created',
        'callback' => '\\local_personalgoals\\observer::learning_interaction',
        'priority' => -100,
    ],
    [
        'eventname' => '\\core\\event\\course_module_completion_updated',
        'callback' => '\\local_personalgoals\\observer::course_module_completion_updated',
        'priority' => -100,
    ],
    [
        'eventname' => '\\mod_quiz\\event\\attempt_submitted',
        'callback' => '\\local_personalgoals\\observer::quiz_attempt_submitted',
        'priority' => -100,
    ],
    [
        'eventname' => '\\core\\event\\course_completed',
        'callback' => '\\local_personalgoals\\observer::course_completed',
        'priority' => -100,
    ],
    [
        'eventname' => '\\core\\event\\course_module_deleted',
        'callback' => '\\local_personalgoals\\observer::course_module_deleted',
        'priority' => -100,
    ],
];
