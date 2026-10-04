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

namespace local_personalgoals;

use advanced_testcase;
use context_course;
use local_personalgoals\service\event_dedupe;

defined('MOODLE_INTERNAL') || die();

/**
 * Class event_dedupe_test.
 */
final class event_dedupe_test extends advanced_testcase {
    /**
     * Method test_same_event_is_processed_only_once.
     *
     * @return void Return value.
     */
    public function test_same_event_is_processed_only_once(): void {
        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $event = event\goal_created::create([
            'context' => context_course::instance($course->id),
            'objectid' => 12345,
            'relateduserid' => $user->id,
            'other' => ['goaltype' => 'activity_completion'],
        ]);

        $this->assertTrue(event_dedupe::claim($event));
        $this->assertFalse(event_dedupe::claim($event));
    }
}
