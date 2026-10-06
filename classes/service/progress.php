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

namespace local_personalgoals\service;

use dml_write_exception;
use stdClass;

/**
 * Class progress.
 */
final class progress {
    /**
     * Method get_record.
     *
     * @param int $goalid Parameter goalid.
     * @return stdClass Return value.
     */
    public static function get_record(int $goalid): stdClass {
        global $DB;
        $record = $DB->get_record('local_personalgoals_progress', ['goalid' => $goalid]);
        if ($record) {
            return $record;
        }

        $record = (object)[
            'goalid' => $goalid,
            'currentvalue' => 0,
            'datajson' => null,
            'timemodified' => time(),
        ];
        try {
            $record->id = $DB->insert_record('local_personalgoals_progress', $record);
            return $record;
        } catch (dml_write_exception $e) {
            return $DB->get_record('local_personalgoals_progress', ['goalid' => $goalid], '*', MUST_EXIST);
        }
    }

    /**
     * Method get_value.
     *
     * @param int $goalid Parameter goalid.
     * @return float Return value.
     */
    public static function get_value(int $goalid): float {
        return (float)self::get_record($goalid)->currentvalue;
    }

    /**
     * Method set_value.
     *
     * @param int $goalid Parameter goalid.
     * @param float $value Parameter value.
     * @param ?array $data Parameter data.
     * @return float Return value.
     */
    public static function set_value(int $goalid, float $value, ?array $data = null): float {
        global $DB;
        $record = self::get_record($goalid);
        $record->currentvalue = max(0, $value);
        if ($data !== null) {
            $record->datajson = json::encode($data);
        }
        $record->timemodified = time();
        $DB->update_record('local_personalgoals_progress', $record);
        return (float)$record->currentvalue;
    }

    /**
     * Method increment.
     *
     * @param int $goalid Parameter goalid.
     * @param float $amount Parameter amount.
     * @return float Return value.
     */
    public static function increment(int $goalid, float $amount): float {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $record = self::get_record($goalid);
        $record = $DB->get_record_sql(
            'SELECT * FROM {local_personalgoals_progress} WHERE id = :id FOR UPDATE',
            ['id' => $record->id],
            MUST_EXIST
        );
        $record->currentvalue = max(0, (float)$record->currentvalue + $amount);
        $record->timemodified = time();
        $DB->update_record('local_personalgoals_progress', $record);
        $transaction->allow_commit();
        return (float)$record->currentvalue;
    }

    /**
     * Method set_item_state.
     *
     * @param int $goalid Parameter goalid.
     * @param int $itemid Parameter itemid.
     * @param bool $completed Parameter completed.
     * @return float Return value.
     */
    public static function set_item_state(int $goalid, int $itemid, bool $completed): float {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $record = self::get_record($goalid);
        $record = $DB->get_record_sql(
            'SELECT * FROM {local_personalgoals_progress} WHERE id = :id FOR UPDATE',
            ['id' => $record->id],
            MUST_EXIST
        );
        $data = json::decode($record->datajson);
        $items = array_values(array_unique(array_map('intval', $data['items'] ?? [])));
        $index = array_search($itemid, $items, true);
        if ($completed && $index === false) {
            $items[] = $itemid;
        } else if (!$completed && $index !== false) {
            unset($items[$index]);
            $items = array_values($items);
        }
        sort($items);
        $data['items'] = $items;
        $record->currentvalue = count($items);
        $record->datajson = json::encode($data);
        $record->timemodified = time();
        $DB->update_record('local_personalgoals_progress', $record);
        $transaction->allow_commit();
        return (float)$record->currentvalue;
    }
}
