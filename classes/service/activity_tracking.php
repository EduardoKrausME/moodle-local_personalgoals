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

use dml_write_exception;

/**
 * Class activity_tracking.
 */
final class activity_tracking {

    /** @var int */
    private const MAX_SESSION_GAP = 1800;

    /** @var int */
    private const MAX_CREDITED_INTERVAL = 900;

    /**
     * Method record_interaction.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param int $timestamp Parameter timestamp.
     * @return void Return value.
     */
    public static function record_interaction(int $userid, int $courseid, int $timestamp): void {
        global $DB;
        if ($userid <= 0 || $courseid <= 1) {
            return;
        }
        $types = $DB->get_fieldset_select(
            'local_personalgoals_goals',
            'goaltype',
            'userid = :userid AND courseid = :courseid AND status = :status '
                . 'AND timestart <= :now1 AND (timeend = 0 OR timeend >= :now2) '
                . 'AND goaltype IN (:active, :study)',
            [
                'userid' => $userid,
                'courseid' => $courseid,
                'status' => 'active',
                'now1' => $timestamp,
                'now2' => $timestamp,
                'active' => 'active_days',
                'study' => 'study_time',
            ]
        );
        $types = array_unique($types);
        if (in_array('active_days', $types, true)) {
            self::record_day($userid, $courseid, $timestamp);
        }
        if (in_array('study_time', $types, true)) {
            self::record_study_time($userid, $courseid, $timestamp);
        }
    }

    /**
     * Method record_day.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param int $timestamp Parameter timestamp.
     * @return void Return value.
     */
    public static function record_day(int $userid, int $courseid, int $timestamp): void {
        global $DB;
        $record = (object)[
            'userid' => $userid,
            'courseid' => $courseid,
            'daykey' => period::day_key($timestamp, $userid),
            'timecreated' => $timestamp,
        ];
        try {
            $DB->insert_record('local_personalgoals_days', $record);
        } catch (dml_write_exception $e) { // phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
            // Expected when this study day has already been registered.
        }
    }

    /**
     * Method record_study_time.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param int $timestamp Parameter timestamp.
     * @return void Return value.
     */
    public static function record_study_time(int $userid, int $courseid, int $timestamp): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $session = $DB->get_record('local_personalgoals_sessions', [
            'userid' => $userid,
            'courseid' => $courseid,
        ]);

        $elapsed = 0;
        $previousactivity = 0;
        if ($session) {
            $session = $DB->get_record_sql(
                'SELECT * FROM {local_personalgoals_sessions} WHERE id = :id FOR UPDATE',
                ['id' => $session->id],
                MUST_EXIST
            );
            $previousactivity = (int)$session->lastactivity;
            if ($timestamp > $previousactivity) {
                $elapsed = $timestamp - $previousactivity;
            }
            $session->lastactivity = $timestamp;
            $session->timemodified = time();
            $DB->update_record('local_personalgoals_sessions', $session);
        } else {
            try {
                $DB->insert_record('local_personalgoals_sessions', (object)[
                    'userid' => $userid,
                    'courseid' => $courseid,
                    'lastactivity' => $timestamp,
                    'timemodified' => time(),
                ]);
            } catch (dml_write_exception $e) { // phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
                // A concurrent event created the session. The next interaction will account for time.
            }
        }

        if ($elapsed > 0 && $elapsed <= self::MAX_SESSION_GAP) {
            $sql = "SELECT id, timestart
                      FROM {local_personalgoals_goals}
                     WHERE userid = :userid
                       AND courseid = :courseid
                       AND goaltype = :goaltype
                       AND status = :status
                       AND timestart <= :now1
                       AND (timeend = 0 OR timeend >= :now2)";
            $goals = $DB->get_records_sql($sql, [
                'userid' => $userid,
                'courseid' => $courseid,
                'goaltype' => 'study_time',
                'status' => 'active',
                'now1' => $timestamp,
                'now2' => $timestamp,
            ]);
            foreach ($goals as $goal) {
                $effectivefrom = max($previousactivity, (int)$goal->timestart);
                $goalseconds = max(0, $timestamp - $effectivefrom);
                $minutes = min($goalseconds, self::MAX_CREDITED_INTERVAL) / 60.0;
                if ($minutes > 0) {
                    progress::increment((int)$goal->id, $minutes);
                }
            }
        }
        $transaction->allow_commit();
    }
}
