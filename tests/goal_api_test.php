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

use local_personalgoals\service\json;
use local_personalgoals\service\period;
use local_personalgoals\service\progress;
use local_personalgoals\service\refresh;

defined('MOODLE_INTERNAL') || die();

/**
 * Core behavior tests for personal goals.
 *
 * @coversDefaultClass \\local_personalgoals\\api
 */
final class goal_api_test extends \advanced_testcase {
    /**
     * Method create_student_course.
     *
     * @param array $userfields Parameter userfields.
     * @return array Return value.
     */
    private function create_student_course(array $userfields = []): array {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user($userfields);
        $course = $generator->create_course(['enablecompletion' => 1]);
        $generator->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);
        return [$user, $course];
    }

    /**
     * Method test_xp_goal_tracks_xp_gained_after_creation.
     *
     * @return void Return value.
     */
    public function test_xp_goal_tracks_xp_gained_after_creation(): void {
        $this->resetAfterTest(true);
        $xpclass = '\\local_personalxp\\service\\xp_manager';
        if (!class_exists($xpclass)) {
            $this->markTestSkipped('local_personalxp is optional and is not installed in this test environment.');
        }
        [$user, $course] = $this->create_student_course();
        set_config('enabled', 1, 'local_personalxp');

        $xpclass::award($user->id, $course->id, 'test_seed', 1001, 100, 'Seed', 'local_personalgoals', __METHOD__);
        $goal = api::create_goal($user->id, $course->id, 'xp', 'Gain 50 XP', 50, [], 'none');
        $xpclass::award($user->id, $course->id, 'test_gain', 1002, 60, 'Gain', 'local_personalgoals', __METHOD__);

        $state = api::get_goal_progress($goal->id);
        global $DB;
        $this->assertSame(60.0, $state['current']);
        $this->assertSame(100.0, $state['percentage']);
        $this->assertSame(api::STATUS_COMPLETED, $DB->get_field('local_personalgoals_goals', 'status', ['id' => $goal->id]));
    }

    /**
     * Method test_weekly_period_ends_on_user_local_sunday.
     *
     * @return void Return value.
     */
    public function test_weekly_period_ends_on_user_local_sunday(): void {
        $this->resetAfterTest(true);
        [$user] = $this->create_student_course(['timezone' => 'America/Sao_Paulo']);
        $reference = (new \DateTimeImmutable('2026-10-01 10:00:00', new \DateTimeZone('America/Sao_Paulo')))->getTimestamp();
        [$start, $end] = period::resolve('weekly', $user->id, $reference);

        $this->assertSame($reference, $start);
        $this->assertSame(
            (new \DateTimeImmutable('2026-10-04 23:59:59', new \DateTimeZone('America/Sao_Paulo')))->getTimestamp(),
            $end
        );
    }

    /**
     * Method test_monthly_period_ends_on_user_local_last_day.
     *
     * @return void Return value.
     */
    public function test_monthly_period_ends_on_user_local_last_day(): void {
        $this->resetAfterTest(true);
        [$user] = $this->create_student_course(['timezone' => 'America/Sao_Paulo']);
        $reference = (new \DateTimeImmutable('2026-10-03 09:00:00', new \DateTimeZone('America/Sao_Paulo')))->getTimestamp();
        [$start, $end] = period::resolve('monthly', $user->id, $reference);

        $this->assertSame($reference, $start);
        $this->assertSame(
            (new \DateTimeImmutable('2026-10-31 23:59:59', new \DateTimeZone('America/Sao_Paulo')))->getTimestamp(),
            $end
        );
    }

    /**
     * Method test_day_key_respects_each_user_timezone.
     *
     * @return void Return value.
     */
    public function test_day_key_respects_each_user_timezone(): void {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();
        $east = $generator->create_user(['timezone' => 'Pacific/Kiritimati']);
        $west = $generator->create_user(['timezone' => 'America/Los_Angeles']);
        $timestamp = (new \DateTimeImmutable('2026-10-03 10:30:00', new \DateTimeZone('UTC')))->getTimestamp();

        $this->assertSame('20261004', period::day_key($timestamp, $east->id));
        $this->assertSame('20261003', period::day_key($timestamp, $west->id));
    }

    /**
     * Method test_goal_expires_after_deadline_without_failure_message_state.
     *
     * @return void Return value.
     */
    public function test_goal_expires_after_deadline_without_failure_message_state(): void {
        global $DB;
        $this->resetAfterTest(true);
        [$user, $course] = $this->create_student_course();
        $goal = api::create_goal(
            $user->id,
            $course->id,
            'activity_completion',
            'Complete five activities',
            5,
            [],
            'custom',
            time() - HOURSECS,
            time() + HOURSECS
        );
        $DB->set_field('local_personalgoals_goals', 'timeend', time() - 1, ['id' => $goal->id]);

        $this->assertTrue(api::expire_goal($goal->id));
        $this->assertSame(api::STATUS_EXPIRED, $DB->get_field('local_personalgoals_goals', 'status', ['id' => $goal->id]));
        $this->assertSame('statusexpired', 'status' . $DB->get_field('local_personalgoals_goals', 'status', ['id' => $goal->id]));
    }

    /**
     * Method test_completion_is_idempotent_and_emits_event_once.
     *
     * @return void Return value.
     */
    public function test_completion_is_idempotent_and_emits_event_once(): void {
        global $DB;
        $this->resetAfterTest(true);
        [$user, $course] = $this->create_student_course();
        $goal = api::create_goal($user->id, $course->id, 'activity_completion', 'Complete one activity', 1);
        progress::set_value($goal->id, 1);
        $sink = $this->redirectEvents();

        $this->assertTrue(api::complete_goal($goal->id));
        $this->assertFalse(api::complete_goal($goal->id));

        $events = array_values(array_filter($sink->get_events(), static function($event): bool {
            return $event instanceof event\goal_completed;
        }));
        $this->assertCount(1, $events);
        $this->assertSame(api::STATUS_COMPLETED, $DB->get_field('local_personalgoals_goals', 'status', ['id' => $goal->id]));
    }

    /**
     * Method test_progress_never_exceeds_100_percent.
     *
     * @return void Return value.
     */
    public function test_progress_never_exceeds_100_percent(): void {
        $this->resetAfterTest(true);
        [$user, $course] = $this->create_student_course();
        $goal = api::create_goal($user->id, $course->id, 'activity_completion', 'Complete five activities', 5);
        progress::set_value($goal->id, 12);

        $state = refresh::goal($goal, false);
        $this->assertSame(12.0, $state['current']);
        $this->assertSame(100.0, $state['percentage']);
    }

    /**
     * Method test_deleted_specific_activity_is_removed_from_active_goal.
     *
     * @return void Return value.
     */
    public function test_deleted_specific_activity_is_removed_from_active_goal(): void {
        global $CFG, $DB;
        $this->resetAfterTest(true);
        [$user, $course] = $this->create_student_course();
        $generator = $this->getDataGenerator();
        $page1 = $generator->create_module('page', ['course' => $course->id, 'name' => 'Page A']);
        $page2 = $generator->create_module('page', ['course' => $course->id, 'name' => 'Page B']);
        $cm1 = get_coursemodule_from_instance('page', $page1->id, $course->id, false, MUST_EXIST);
        $cm2 = get_coursemodule_from_instance('page', $page2->id, $course->id, false, MUST_EXIST);

        $goal = api::create_goal(
            $user->id,
            $course->id,
            'specific_activities',
            'Complete the two pages',
            2,
            ['cmids' => [$cm1->id, $cm2->id]]
        );

        require_once($CFG->dirroot . '/course/lib.php');
        course_delete_module($cm2->id, false);

        $updated = $DB->get_record('local_personalgoals_goals', ['id' => $goal->id], '*', MUST_EXIST);
        $config = json::decode($updated->configjson);
        $this->assertSame([$cm1->id], array_values(array_map('intval', $config['cmids'])));
        $this->assertContains($cm2->id, array_map('intval', $config['missingactivities']));
        $this->assertEquals(1.0, (float)$updated->targetvalue);
        $this->assertSame(api::STATUS_ACTIVE, $updated->status);
    }

    /**
     * Method test_course_completed_event_completes_course_progress_goal.
     *
     * @return void Return value.
     */
    public function test_course_completed_event_completes_course_progress_goal(): void {
        global $CFG, $DB;
        $this->resetAfterTest(true);
        [$user, $course] = $this->create_student_course();
        $goal = api::create_goal($user->id, $course->id, 'course_completion', 'Finish the course', 100);

        require_once($CFG->dirroot . '/completion/completion_completion.php');
        $completion = new \completion_completion(['userid' => $user->id, 'course' => $course->id]);
        $completion->mark_complete(time());

        $updated = $DB->get_record('local_personalgoals_goals', ['id' => $goal->id], '*', MUST_EXIST);
        $this->assertSame(api::STATUS_COMPLETED, $updated->status);
        $this->assertSame(100.0, api::get_goal_progress($goal->id)['percentage']);
    }
}
