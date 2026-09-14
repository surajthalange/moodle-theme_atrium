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

namespace theme_atrium\local;

use cache;
use core\event\base;

/**
 * The four dashboard counts for one user, cached so the hero never slows the dashboard.
 *
 * Cached in MUC for five minutes per user; the two completion events drop that user's
 * entry so "Completed" moves the moment a course completes rather than up to five
 * minutes later. Messages and calendar entries tolerate the delay.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class stats {
    /** @var string The MUC cache area, see db/caches.php. */
    public const CACHE_AREA = 'stats';

    /** @var int Seconds ahead that "due" looks. */
    public const DUE_WINDOW = 7 * DAYSECS;

    /**
     * The counts for a user: inprogress, completed, due, unread.
     *
     * @param int $userid
     * @return array{inprogress: int, completed: int, due: int, unread: int}
     */
    public static function for_user(int $userid): array {
        $cache = cache::make('theme_atrium', self::CACHE_AREA);
        $counts = $cache->get($userid);
        if (is_array($counts)) {
            return $counts;
        }
        $counts = [
            'inprogress' => self::count_in_progress($userid),
            'completed' => self::count_completed($userid),
            'due' => self::count_due($userid),
            'unread' => self::count_unread($userid),
        ];
        $cache->set($userid, $counts);
        return $counts;
    }

    /**
     * Forget a user's counts.
     *
     * @param int $userid
     */
    public static function invalidate(int $userid): void {
        cache::make('theme_atrium', self::CACHE_AREA)->delete($userid);
    }

    /**
     * Event observer for the completion events listed in db/events.php.
     *
     * @param base $event
     */
    public static function completion_changed(base $event): void {
        if (!empty($event->relateduserid)) {
            self::invalidate((int) $event->relateduserid);
        }
    }

    /**
     * Active enrolments in visible courses that track completion and are not yet complete.
     *
     * @param int $userid
     * @return int
     */
    private static function count_in_progress(int $userid): int {
        global $DB;
        $courses = enrol_get_all_users_courses($userid, true, ['enablecompletion']);
        $courses = array_filter($courses, fn($course) => !empty($course->enablecompletion) && !empty($course->visible));
        if (!$courses) {
            return 0;
        }
        [$insql, $params] = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED);
        $params['userid'] = $userid;
        $completed = $DB->count_records_select(
            'course_completions',
            "userid = :userid AND timecompleted > 0 AND course $insql",
            $params
        );
        return count($courses) - $completed;
    }

    /**
     * Courses completed, whether or not the user is still enrolled.
     *
     * @param int $userid
     * @return int
     */
    private static function count_completed(int $userid): int {
        global $DB;
        return $DB->count_records_select('course_completions', 'userid = :userid AND timecompleted > 0', ['userid' => $userid]);
    }

    /**
     * Action events (assignment due, quiz closes, ...) in the next seven days.
     *
     * @param int $userid
     * @return int
     */
    private static function count_due(int $userid): int {
        $user = \core_user::get_user($userid);
        if (!$user) {
            return 0;
        }
        $now = time();
        $events = \core_calendar\local\api::get_action_events_by_timesort($now, $now + self::DUE_WINDOW, null, 50, true, $user);
        return count($events);
    }

    /**
     * Unread conversations plus unread popup notifications.
     *
     * @param int $userid
     * @return int
     */
    private static function count_unread(int $userid): int {
        $user = \core_user::get_user($userid);
        if (!$user) {
            return 0;
        }
        $count = (int) \core_message\api::count_unread_conversations($user);
        if (class_exists('\message_popup\api')) {
            $count += (int) \message_popup\api::count_unread_popup_notifications($userid);
        }
        return $count;
    }
}
