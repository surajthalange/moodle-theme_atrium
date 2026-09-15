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

use core_course_list_element;
use moodle_url;

/**
 * The recent courses menu in the navigation bar.
 *
 * Core keeps a per-user log of recently accessed courses for the dashboard block; the menu
 * lists the same courses so a learner can jump between them from any page.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class recentcourses {
    /** @var int Courses the menu shows at most. */
    public const MAX = 6;

    /**
     * Whether the site shows the menu.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $value = get_config('theme_atrium', 'navbar_recentcourses');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * Template context for the menu, or false when it has nothing to show.
     *
     * @return array|false
     */
    public static function export() {
        global $CFG, $USER;
        if (!self::enabled() || !isloggedin() || isguestuser()) {
            return false;
        }
        require_once($CFG->dirroot . '/course/lib.php');
        $recent = course_get_recent_courses($USER->id, self::MAX);
        if (!$recent) {
            return false;
        }
        $items = [];
        foreach ($recent as $course) {
            $element = new core_course_list_element($course);
            $image = courses::image($element);
            $items[] = [
                'id' => $course->id,
                'name' => $element->get_formatted_name(),
                'url' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
                'imageurl' => $image['imageurl'],
                'hasimage' => $image['imageurl'] !== '',
                'gradient' => $image['gradient'],
            ];
        }
        return [
            'courses' => $items,
            'allurl' => (new moodle_url('/my/courses.php'))->out(false),
        ];
    }
}
