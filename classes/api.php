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

use completion_info;
use context_course;
use context_system;
use local_personalgoals\service\integrations;
use local_personalgoals\service\json;
use local_personalgoals\service\limits;
use local_personalgoals\service\period;
use local_personalgoals\service\progress;
use local_personalgoals\service\refresh;
use moodle_exception;
use required_capability_exception;
use stdClass;

/**
 * Public API for Personal Goals.
 */
final class api {

    /** @var string */
    public const STATUS_ACTIVE = 'active';

    /** @var string */
    public const STATUS_COMPLETED = 'completed';

    /** @var string */
    public const STATUS_EXPIRED = 'expired';

    /** @var string */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Method get_user_goals.
     *
     * @param int $userid Parameter userid.
     * @param ?int $courseid Parameter courseid.
     * @param ?string $status Parameter status.
     * @return array Return value.
     */
    public static function get_user_goals(int $userid, ?int $courseid = null, ?string $status = null): array {
        global $DB, $USER;
        if ((int)$USER->id !== $userid && !is_siteadmin()) {
            throw new required_capability_exception(context_system::instance(), 'moodle/site:config', 'nopermissions', '');
        }
        $conditions = ['userid' => $userid];
        if ($courseid !== null && $courseid > 0) {
            $conditions['courseid'] = $courseid;
        }
        if ($status !== null && $status !== '') {
            $conditions['status'] = $status;
        }
        return array_values($DB->get_records('local_personalgoals_goals', $conditions, 'timecreated DESC'));
    }

    /**
     * Method get_goal_progress.
     *
     * @param int $goalid Parameter goalid.
     * @return array Return value.
     */
    public static function get_goal_progress(int $goalid): array {
        global $DB, $USER;
        $goal = $DB->get_record('local_personalgoals_goals', ['id' => $goalid], '*', MUST_EXIST);
        if ((int)$USER->id !== (int)$goal->userid && !is_siteadmin()) {
            throw new moodle_exception('cannotviewgoal', 'local_personalgoals');
        }
        return refresh::goal($goal);
    }

    /**
     * Method create_goal.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param string $goaltype Parameter goaltype.
     * @param string $name Parameter name.
     * @param float $targetvalue Parameter targetvalue.
     * @param array $config Parameter config.
     * @param string $periodtype Parameter periodtype.
     * @param ?int $customstart Parameter customstart.
     * @param ?int $customend Parameter customend.
     * @return stdClass Return value.
     */
    public static function create_goal(
        int $userid,
        int $courseid,
        string $goaltype,
        string $name,
        float $targetvalue,
        array $config = [],
        string $periodtype = 'none',
        ?int $customstart = null,
        ?int $customend = null
    ): stdClass {
        return self::create_goal_record(
            $userid,
            $courseid,
            $goaltype,
            $name,
            $targetvalue,
            $config,
            $periodtype,
            $customstart,
            $customend,
            false
        );
    }

    /**
     * Method create_goal_record.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param string $goaltype Parameter goaltype.
     * @param string $name Parameter name.
     * @param float $targetvalue Parameter targetvalue.
     * @param array $config Parameter config.
     * @param string $periodtype Parameter periodtype.
     * @param ?int $customstart Parameter customstart.
     * @param ?int $customend Parameter customend.
     * @param bool $trustedrewards Parameter trustedrewards.
     * @return stdClass Return value.
     */
    private static function create_goal_record(
        int $userid,
        int $courseid,
        string $goaltype,
        string $name,
        float $targetvalue,
        array $config,
        string $periodtype,
        ?int $customstart,
        ?int $customend,
        bool $trustedrewards
    ): stdClass {
        global $DB, $USER;

        $context = context_course::instance($courseid);
        if ((int)$USER->id !== $userid || !has_capability('local/personalgoals:manageown', $context)) {
            throw new moodle_exception('cannotcreategoal', 'local_personalgoals');
        }
        $types = goal_type_manager::get_types();
        if (!isset($types[$goaltype])) {
            throw new moodle_exception('unknowngoaltype', 'local_personalgoals', '', $goaltype);
        }
        $name = trim(clean_param($name, PARAM_TEXT));
        if ($name === '') {
            throw new moodle_exception('goalnamerequired', 'local_personalgoals');
        }
        if ($targetvalue <= 0) {
            throw new moodle_exception('targetmustbepositive', 'local_personalgoals');
        }
        if (!in_array($periodtype, ['daily', 'weekly', 'monthly', 'custom', 'none'], true)) {
            throw new moodle_exception('invalidperiod', 'local_personalgoals');
        }

        if ($goaltype === 'specific_activities') {
            $modinfo = get_fast_modinfo($courseid, $userid);
            $cmids = array_values(array_unique(array_filter(array_map('intval', $config['cmids'] ?? []))));
            $cmids = array_values(array_filter($cmids, static fn(int $cmid): bool => isset($modinfo->cms[$cmid])));
            $config['cmids'] = $cmids;
            if (!$cmids) {
                throw new moodle_exception('selectatleastoneactivity', 'local_personalgoals');
            }
            $targetvalue = count($cmids);
        }

        if ($goaltype === 'course_completion' && $targetvalue > 100) {
            throw new moodle_exception('percentagecannotexceed100', 'local_personalgoals');
        }

        [$timestart, $timeend] = period::resolve($periodtype, $userid, time(), $customstart, $customend);
        limits::validate($userid, $courseid, $goaltype, $targetvalue, $periodtype, $timestart, $timeend);

        if (!$trustedrewards) {
            unset($config['rewardxp'], $config['rewardcredits'], $config['celebrate'], $config['repeatrewardallowed']);
        }
        $config['periodtype'] = $periodtype;

        if ($goaltype === 'xp') {
            $currentxp = integrations::current_xp($userid, $courseid);
            if ($currentxp === null) {
                throw new moodle_exception('xpunavailable', 'local_personalgoals');
            }
            $config['xpbaseline'] = $currentxp;
        }

        $candidate = (object)[
            'id' => 0,
            'userid' => $userid,
            'courseid' => $courseid,
            'goaltype' => $goaltype,
            'name' => $name,
            'targetvalue' => $targetvalue,
            'configjson' => json::encode($config),
            'timestart' => $timestart,
            'timeend' => $timeend,
            'status' => self::STATUS_ACTIVE,
            'completedat' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $type = new $types[$goaltype]($candidate);
        $errors = $type->validate_configuration($config);
        if ($errors) {
            throw new moodle_exception('invalidgoalconfiguration', 'local_personalgoals', '', implode('; ', array_values($errors)));
        }

        $transaction = $DB->start_delegated_transaction();
        $candidate->id = $DB->insert_record('local_personalgoals_goals', $candidate);
        $initial = 0.0;
        if ($goaltype === 'course_completion') {
            $initial = $type->get_current_value();
        }
        progress::set_value((int)$candidate->id, $initial, []);
        if ($goaltype === 'specific_activities') {
            $completion = new completion_info(get_course($courseid));
            $modinfo = get_fast_modinfo($courseid, $userid);
            foreach ($config['cmids'] as $cmid) {
                if (!isset($modinfo->cms[$cmid])) {
                    continue;
                }
                $cm = $modinfo->cms[$cmid];
                if ($completion->is_enabled($cm) !== COMPLETION_TRACKING_NONE) {
                    $completiondata = $completion->get_data($cm, false, $userid);
                    if ((int)$completiondata->completionstate !== COMPLETION_INCOMPLETE) {
                        progress::set_item_state((int)$candidate->id, (int)$cmid, true);
                    }
                }
            }
        }
        $transaction->allow_commit();

        event\goal_created::create([
            'context' => $context,
            'objectid' => $candidate->id,
            'relateduserid' => $userid,
            'other' => ['goaltype' => $goaltype],
        ])->trigger();

        refresh::goal($candidate);
        return $DB->get_record('local_personalgoals_goals', ['id' => $candidate->id], '*', MUST_EXIST);
    }

    /**
     * Method complete_goal.
     *
     * @param int $goalid Parameter goalid.
     * @return bool Return value.
     */
    public static function complete_goal(int $goalid): bool {
        global $DB;
        $goal = $DB->get_record('local_personalgoals_goals', ['id' => $goalid], '*', IGNORE_MISSING);
        if (!$goal || $goal->status !== self::STATUS_ACTIVE) {
            return false;
        }
        if ((int)$goal->timeend > 0 && (int)$goal->timeend < time()) {
            self::expire_goal($goalid);
            return false;
        }
        $type = goal_type_manager::for_goal($goal);
        if (!$type->is_completed()) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        $locked = $DB->get_record_sql(
            'SELECT * FROM {local_personalgoals_goals} WHERE id = :id FOR UPDATE',
            ['id' => $goalid],
            MUST_EXIST
        );
        if ($locked->status !== self::STATUS_ACTIVE) {
            $transaction->allow_commit();
            return false;
        }
        $locked->status = self::STATUS_COMPLETED;
        $locked->completedat = time();
        $locked->timemodified = time();
        $DB->update_record('local_personalgoals_goals', $locked);
        $transaction->allow_commit();

        $config = json::decode($locked->configjson);
        event\goal_completed::create([
            'context' => context_course::instance($locked->courseid),
            'objectid' => $locked->id,
            'relateduserid' => $locked->userid,
            'other' => [
                'goaltype' => $locked->goaltype,
                'rewardxp' => max(0, (int)($config['rewardxp'] ?? 0)),
                'rewardcredits' => max(0, (int)($config['rewardcredits'] ?? 0)),
            ],
        ])->trigger();

        integrations::award_xp($locked, max(0, (int)($config['rewardxp'] ?? 0)));
        integrations::award_credits($locked, max(0, (int)($config['rewardcredits'] ?? 0)));
        if (!array_key_exists('celebrate', $config) || !empty($config['celebrate'])) {
            integrations::celebrate($locked);
        }
        return true;
    }

    /**
     * Method expire_goal.
     *
     * @param int $goalid Parameter goalid.
     * @return bool Return value.
     */
    public static function expire_goal(int $goalid): bool {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $goal = $DB->get_record_sql(
            'SELECT * FROM {local_personalgoals_goals} WHERE id = :id FOR UPDATE',
            ['id' => $goalid],
            IGNORE_MISSING
        );
        if (!$goal || $goal->status !== self::STATUS_ACTIVE) {
            $transaction->allow_commit();
            return false;
        }
        if ((int)$goal->timeend === 0 || (int)$goal->timeend >= time()) {
            $transaction->allow_commit();
            return false;
        }
        $goal->status = self::STATUS_EXPIRED;
        $goal->timemodified = time();
        $DB->update_record('local_personalgoals_goals', $goal);
        $transaction->allow_commit();

        event\goal_expired::create([
            'context' => context_course::instance($goal->courseid),
            'objectid' => $goal->id,
            'relateduserid' => $goal->userid,
            'other' => ['goaltype' => $goal->goaltype],
        ])->trigger();
        return true;
    }

    /**
     * Method cancel_goal.
     *
     * @param int $goalid Parameter goalid.
     * @return bool Return value.
     */
    public static function cancel_goal(int $goalid): bool {
        global $DB, $USER;
        $goal = $DB->get_record('local_personalgoals_goals', ['id' => $goalid], '*', MUST_EXIST);
        $context = context_course::instance($goal->courseid);
        if ((int)$USER->id !== (int)$goal->userid || !has_capability('local/personalgoals:manageown', $context)) {
            throw new moodle_exception('cannotcancelgoal', 'local_personalgoals');
        }
        if ($goal->status !== self::STATUS_ACTIVE) {
            return false;
        }
        $goal->status = self::STATUS_CANCELLED;
        $goal->timemodified = time();
        $DB->update_record('local_personalgoals_goals', $goal);
        event\goal_cancelled::create([
            'context' => $context,
            'objectid' => $goal->id,
            'relateduserid' => $goal->userid,
            'other' => ['goaltype' => $goal->goaltype],
        ])->trigger();
        return true;
    }

    /**
     * Method accept_template.
     *
     * @param int $templateid Parameter templateid.
     * @param int $userid Parameter userid.
     * @return stdClass Return value.
     */
    public static function accept_template(int $templateid, int $userid): stdClass {
        global $DB, $USER;
        $template = $DB->get_record('local_personalgoals_template', ['id' => $templateid, 'active' => 1], '*', MUST_EXIST);
        if ((int)$USER->id !== $userid) {
            throw new moodle_exception('cannotaccepttemplate', 'local_personalgoals');
        }
        $config = json::decode($template->configjson);
        $config['templateid'] = (int)$template->id;
        return self::create_goal_record(
            $userid,
            (int)$template->courseid,
            (string)$template->goaltype,
            (string)$template->name,
            (float)$template->targetvalue,
            $config,
            (string)$template->periodtype,
            null,
            null,
            true
        );
    }

    /**
     * Method recreate_goal.
     *
     * @param int $goalid Parameter goalid.
     * @return stdClass Return value.
     */
    public static function recreate_goal(int $goalid): stdClass {
        global $DB, $USER;
        $goal = $DB->get_record('local_personalgoals_goals', ['id' => $goalid], '*', MUST_EXIST);
        if ((int)$USER->id !== (int)$goal->userid) {
            throw new moodle_exception('cannotrecreategoal', 'local_personalgoals');
        }
        $config = json::decode($goal->configjson);
        $periodtype = $config['periodtype'] ?? 'none';
        unset($config['xpbaseline'], $config['templateid']);
        $trustedrewards = !empty($config['repeatrewardallowed']);
        if (!$trustedrewards) {
            unset($config['rewardxp'], $config['rewardcredits'], $config['celebrate']);
        }
        unset($config['repeatrewardallowed']);
        return self::create_goal_record(
            (int)$goal->userid,
            (int)$goal->courseid,
            (string)$goal->goaltype,
            (string)$goal->name,
            (float)$goal->targetvalue,
            $config,
            (string)$periodtype,
            null,
            null,
            $trustedrewards
        );
    }

    /**
     * Method get_available_goal_types.
     *
     * @param int $courseid Parameter courseid.
     * @return array Return value.
     */
    public static function get_available_goal_types(int $courseid): array {
        return array_values(goal_type_manager::get_available_types($courseid));
    }
}
