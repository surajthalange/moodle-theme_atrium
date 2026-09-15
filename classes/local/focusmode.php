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

use moodle_url;

/**
 * Focus mode: the course and its activities with nothing else on screen.
 *
 * A per-user preference, applied server-side on course and activity pages, so there is
 * no flash and the preference survives navigation. The switch is a plain link to
 * focus.php, which works without JavaScript.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class focusmode {
    /** @var string The user preference. */
    public const PREFERENCE = 'theme_atrium_focusmode';

    /** @var string[] Page layouts focus mode applies to. */
    public const LAYOUTS = ['course', 'incourse'];

    /**
     * Whether the site offers focus mode.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $value = get_config('theme_atrium', 'course_enablefocus');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * Whether the current user has focus mode switched on.
     *
     * @return bool
     */
    public static function on(): bool {
        return self::enabled() && isloggedin() && !isguestuser() && (bool) get_user_preferences(self::PREFERENCE, 0);
    }

    /**
     * Whether the current page is one focus mode applies to.
     *
     * @return bool
     */
    public static function applies(): bool {
        global $PAGE;
        $course = $PAGE->course;
        return self::enabled()
            && isloggedin() && !isguestuser()
            && in_array($PAGE->pagelayout, self::LAYOUTS, true)
            && $course && (int) $course->id !== SITEID
            && $PAGE->pagetype !== 'enrol-index';
    }

    /**
     * Whether to render this page in focus mode.
     *
     * @return bool
     */
    public static function active(): bool {
        return self::applies() && self::on();
    }

    /**
     * Save the preference.
     *
     * @param bool $on
     */
    public static function set(bool $on): void {
        set_user_preference(self::PREFERENCE, $on ? 1 : 0);
    }

    /**
     * The link that flips focus mode and returns to the current page.
     *
     * @return moodle_url
     */
    public static function toggle_url(): moodle_url {
        global $PAGE;
        return new moodle_url('/theme/atrium/focus.php', [
            'state' => self::on() ? 0 : 1,
            'sesskey' => sesskey(),
            'returnurl' => $PAGE->url->out_as_local_url(false),
        ]);
    }
}
