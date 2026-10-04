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

namespace local_personalgoals\service;

use moodle_exception;
use stdClass;

/**
 * Class limits.
 */
final class limits {
    /**
     * Method get.
     *
     * @param int $courseid Parameter courseid.
     * @param string $goaltype Parameter goaltype.
     * @return stdClass Return value.
     */
    public static function get(int $courseid, string $goaltype): stdClass {
        global $DB;
        $record = $DB->get_record('local_personalgoals_limits', [
            'courseid' => $courseid,
            'goaltype' => $goaltype,
        ]);
        if ($record) {
            return $record;
        }
        return (object)[
            'id' => 0,
            'courseid' => $courseid,
            'goaltype' => $goaltype,
            'enabled' => 1,
            'mintarget' => 1,
            'maxtarget' => 1000000,
            'maxactive' => 10,
            'allowcustomdates' => 1,
            'maxdurationdays' => 365,
        ];
    }

    /**
     * Method validate.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @param string $goaltype Parameter goaltype.
     * @param float $target Parameter target.
     * @param string $periodtype Parameter periodtype.
     * @param int $timestart Parameter timestart.
     * @param int $timeend Parameter timeend.
     * @return void Return value.
     */
    public static function validate(
        int $userid,
        int $courseid,
        string $goaltype,
        float $target,
        string $periodtype,
        int $timestart,
        int $timeend
    ): void {
        global $DB;
        $limit = self::get($courseid, $goaltype);
        if (!(int)$limit->enabled) {
            throw new moodle_exception('goaltypenotallowed', 'local_personalgoals');
        }
        if ($target < (float)$limit->mintarget || $target > (float)$limit->maxtarget) {
            throw new moodle_exception('targetoutsideallowedrange', 'local_personalgoals', '', (object)[
                'min' => $limit->mintarget,
                'max' => $limit->maxtarget,
            ]);
        }
        if ($periodtype === 'custom' && !(int)$limit->allowcustomdates) {
            throw new moodle_exception('customdatesnotallowed', 'local_personalgoals');
        }
        if ($timeend > 0 && $timeend <= $timestart) {
            throw new moodle_exception('invaliddeadline', 'local_personalgoals');
        }
        if ($timeend > 0 && (int)$limit->maxdurationdays > 0) {
            $days = ($timeend - $timestart) / DAYSECS;
            if ($days > (int)$limit->maxdurationdays) {
                throw new moodle_exception('durationtoolong', 'local_personalgoals', '', (int)$limit->maxdurationdays);
            }
        }
        $active = $DB->count_records('local_personalgoals_goals', [
            'userid' => $userid,
            'courseid' => $courseid,
            'goaltype' => $goaltype,
            'status' => 'active',
        ]);
        if ((int)$limit->maxactive > 0 && $active >= (int)$limit->maxactive) {
            throw new moodle_exception('maxactivegoalsreached', 'local_personalgoals', '', (int)$limit->maxactive);
        }
    }
}
