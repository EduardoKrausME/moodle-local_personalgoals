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

namespace local_personalgoals\service;

use DateTimeImmutable;
use DateTimeZone;

defined('MOODLE_INTERNAL') || die();

/**
 * Class period.
 */
final class period {
    /**
     * Resolve the goal window. Period goals begin when accepted and end at the user's local period boundary.
     *
     * @return array{0:int,1:int}
     */
    public static function resolve(
        string $periodtype,
        int $userid,
        ?int $reference = null,
        ?int $customstart = null,
        ?int $customend = null
    ): array {
        global $DB;

        $reference = $reference ?? time();
        $user = $DB->get_record('user', ['id' => $userid], 'id,timezone', MUST_EXIST);
        $tzname = \core_date::get_user_timezone($user);
        $timezone = new DateTimeZone($tzname);
        $now = (new DateTimeImmutable('@' . $reference))->setTimezone($timezone);
        $start = $reference;
        $end = 0;

        switch ($periodtype) {
            case 'daily':
                $end = $now->setTime(23, 59, 59)->getTimestamp();
                break;
            case 'weekly':
                $end = $now->modify('sunday this week')->setTime(23, 59, 59)->getTimestamp();
                break;
            case 'monthly':
                $end = $now->modify('last day of this month')->setTime(23, 59, 59)->getTimestamp();
                break;
            case 'custom':
                $start = $customstart ?: $reference;
                $end = $customend ?: 0;
                break;
            case 'none':
            default:
                $end = 0;
                break;
        }

        return [(int)$start, (int)$end];
    }

    /**
     * Method day_key.
     *
     * @param int $timestamp Parameter timestamp.
     * @param int $userid Parameter userid.
     * @return string Return value.
     */
    public static function day_key(int $timestamp, int $userid): string {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid], 'id,timezone', MUST_EXIST);
        $timezone = new DateTimeZone(\core_date::get_user_timezone($user));
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone($timezone)->format('Ymd');
    }
}
