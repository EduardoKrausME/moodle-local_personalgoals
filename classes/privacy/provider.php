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

namespace local_personalgoals\privacy;

use context;
use context_course;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
final class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_personalgoals_goals', [
            'userid' => 'privacy:metadata:goals:userid',
            'courseid' => 'privacy:metadata:goals:courseid',
            'goaltype' => 'privacy:metadata:goals:goaltype',
            'name' => 'privacy:metadata:goals:name',
            'targetvalue' => 'privacy:metadata:goals:targetvalue',
            'configjson' => 'privacy:metadata:goals:configjson',
            'timestart' => 'privacy:metadata:goals:timestart',
            'timeend' => 'privacy:metadata:goals:timeend',
            'status' => 'privacy:metadata:goals:status',
            'completedat' => 'privacy:metadata:goals:completedat',
            'timecreated' => 'privacy:metadata:goals:timecreated',
            'timemodified' => 'privacy:metadata:goals:timemodified',
        ], 'privacy:metadata:goals');

        $collection->add_database_table('local_personalgoals_progress', [
            'goalid' => 'privacy:metadata:progress',
            'currentvalue' => 'privacy:metadata:progress',
            'datajson' => 'privacy:metadata:progress',
            'timemodified' => 'privacy:metadata:goals:timemodified',
        ], 'privacy:metadata:progress');

        $collection->add_database_table('local_personalgoals_days', [
            'userid' => 'privacy:metadata:goals:userid',
            'courseid' => 'privacy:metadata:goals:courseid',
            'daykey' => 'privacy:metadata:days:daykey',
            'timecreated' => 'privacy:metadata:goals:timecreated',
        ], 'privacy:metadata:days');

        $collection->add_database_table('local_personalgoals_sessions', [
            'userid' => 'privacy:metadata:goals:userid',
            'courseid' => 'privacy:metadata:goals:courseid',
            'lastactivity' => 'privacy:metadata:sessions:lastactivity',
            'timemodified' => 'privacy:metadata:goals:timemodified',
        ], 'privacy:metadata:sessions');
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {local_personalgoals_goals} g ON g.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel AND g.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);

        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {local_personalgoals_days} d ON d.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel AND d.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);

        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {local_personalgoals_sessions} s ON s.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel AND s.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {
            if (!$context instanceof context_course) {
                continue;
            }
            $courseid = $context->instanceid;
            $goals = array_values($DB->get_records('local_personalgoals_goals', [
                'userid' => $userid,
                'courseid' => $courseid,
            ], 'timecreated ASC'));
            $days = array_values($DB->get_records('local_personalgoals_days', [
                'userid' => $userid,
                'courseid' => $courseid,
            ], 'timecreated ASC'));
            $sessions = array_values($DB->get_records('local_personalgoals_sessions', [
                'userid' => $userid,
                'courseid' => $courseid,
            ]));
            $progress = [];
            if ($goals) {
                $goalids = array_map(static fn($goal): int => (int)$goal->id, $goals);
                [$insql, $params] = $DB->get_in_or_equal($goalids, SQL_PARAMS_NAMED, 'goal');
                $progress = array_values($DB->get_records_select('local_personalgoals_progress', "goalid $insql", $params));
            }
            foreach ($goals as $goal) {
                $goal->timestart = transform::datetime($goal->timestart);
                $goal->timeend = $goal->timeend ? transform::datetime($goal->timeend) : null;
                $goal->completedat = $goal->completedat ? transform::datetime($goal->completedat) : null;
                $goal->timecreated = transform::datetime($goal->timecreated);
                $goal->timemodified = transform::datetime($goal->timemodified);
            }
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_personalgoals')],
                (object)['goals' => $goals, 'progress' => $progress, 'days' => $days, 'sessions' => $sessions]
            );
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if (!$context instanceof context_course) {
            return;
        }
        self::delete_for_course($context->instanceid, null);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist as $context) {
            if ($context instanceof context_course) {
                self::delete_for_course($context->instanceid, $userid);
            }
        }
    }

    /**
     * Method delete_for_course.
     *
     * @param int $courseid Parameter courseid.
     * @param ?int $userid Parameter userid.
     * @return void Return value.
     */
    private static function delete_for_course(int $courseid, ?int $userid): void {
        global $DB;
        $conditions = ['courseid' => $courseid];
        if ($userid !== null) {
            $conditions['userid'] = $userid;
        }
        $goalids = $DB->get_fieldset_select(
            'local_personalgoals_goals',
            'id',
            $userid === null ? 'courseid = :courseid' : 'courseid = :courseid AND userid = :userid',
            $conditions
        );
        if ($goalids) {
            [$insql, $params] = $DB->get_in_or_equal($goalids, SQL_PARAMS_NAMED, 'goal');
            $DB->delete_records_select('local_personalgoals_progress', "goalid $insql", $params);
        }
        $DB->delete_records('local_personalgoals_goals', $conditions);
        $DB->delete_records('local_personalgoals_days', $conditions);
        $DB->delete_records('local_personalgoals_sessions', $conditions);
    }
}
