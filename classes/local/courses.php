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
use core_course_category;
use core_course_list_element;
use moodle_url;

/**
 * Course queries shared by the front page showcase and the catalogue.
 *
 * Every method respects course visibility for the current user through
 * core_course_category, so a hidden course never reaches a visitor.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class courses {
    /** @var int Gradients available for courses without an image; see _cards.scss. */
    public const GRADIENTS = 8;

    /**
     * The latest visible courses, newest first.
     *
     * @param int $limit
     * @return core_course_list_element[]
     */
    public static function latest(int $limit): array {
        global $DB;
        // Fetch a few more than needed so hidden courses can be dropped without a second query.
        $ids = $DB->get_fieldset_sql(
            'SELECT id FROM {course} WHERE id <> :site AND visible = 1 ORDER BY timecreated DESC, id DESC',
            ['site' => SITEID],
            0,
            $limit * 2
        );
        return array_slice(self::by_ids($ids), 0, $limit);
    }

    /**
     * Visible courses in a category, in the category's own order.
     *
     * @param int $categoryid
     * @param int $limit
     * @return core_course_list_element[]
     */
    public static function in_category(int $categoryid, int $limit): array {
        $category = core_course_category::get($categoryid, IGNORE_MISSING);
        if (!$category || !$category->is_uservisible()) {
            return [];
        }
        return self::visible($category->get_courses(['recursive' => true, 'limit' => $limit]));
    }

    /**
     * Hand-picked courses, in the order given, skipping any the user may not see.
     *
     * @param int[] $ids
     * @return core_course_list_element[]
     */
    public static function by_ids(array $ids): array {
        global $DB;
        if (!$ids) {
            return [];
        }
        $found = [];
        foreach ($DB->get_records_list('course', 'id', $ids) as $record) {
            $found[$record->id] = new core_course_list_element($record);
        }
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($found[$id])) {
                $ordered[] = $found[$id];
            }
        }
        return self::visible($ordered);
    }

    /**
     * The course image URL, or the gradient class to use instead.
     *
     * @param core_course_list_element $course
     * @return array{imageurl: string, gradient: string}
     */
    public static function image(core_course_list_element $course): array {
        foreach ($course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $url = moodle_url::make_file_url(
                    '/pluginfile.php',
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea()
                    . $file->get_filepath() . $file->get_filename(),
                    !$file->is_valid_image()
                );
                return ['imageurl' => $url->out(false), 'gradient' => ''];
            }
        }
        return ['imageurl' => '', 'gradient' => 'atrium-gradient-' . ($course->id % self::GRADIENTS)];
    }

    /**
     * Course contacts (teachers) with their names and picture URLs.
     *
     * @param core_course_list_element $course
     * @param int $limit
     * @return array<int, array{name: string, pictureurl: string, url: string}>
     */
    public static function contacts(core_course_list_element $course, int $limit = 3): array {
        global $PAGE;
        if (!$course->has_course_contacts()) {
            return [];
        }
        $contacts = [];
        foreach (array_slice($course->get_course_contacts(), 0, $limit, true) as $userid => $contact) {
            $user = self::picture_user((int) $userid);
            if (!$user) {
                continue;
            }
            $picture = new \user_picture($user);
            $picture->size = 35;
            $contacts[] = [
                'name' => $contact['username'],
                'pictureurl' => $picture->get_url($PAGE)->out(false),
                'url' => (new moodle_url('/user/view.php', ['id' => $userid, 'course' => $course->id]))->out(false),
            ];
        }
        return $contacts;
    }

    /**
     * A user record with the fields user_picture needs, loaded once per request.
     *
     * Course contacts come back from core with name fields only; the picture needs a few
     * more, and the same teacher appears on many cards.
     *
     * @param int $userid
     * @return \stdClass|null
     */
    private static function picture_user(int $userid): ?\stdClass {
        global $DB;
        static $users = [];
        if (!array_key_exists($userid, $users)) {
            $fields = \core_user\fields::for_userpic()->get_sql('', false, '', '', false)->selects;
            $users[$userid] = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], $fields) ?: null;
        }
        return $users[$userid];
    }

    /**
     * Number of users enrolled with an active enrolment in a course.     *
     * @param int $courseid
     * @return int
     */
    public static function enrolled_count(int $courseid): int {
        $cache = cache::make('theme_atrium', 'coursecounts');
        $count = $cache->get($courseid);
        if ($count === false) {
            $count = count_enrolled_users(\context_course::instance($courseid), '', 0, true);
            $cache->set($courseid, $count);
        }
        return (int) $count;
    }

    /**
     * Site-wide counters for the front page stats strip, cached for ten minutes.
     *
     * @return array{courses: int, users: int, categories: int, completions: int}
     */
    public static function site_counts(): array {
        global $DB;
        $cache = cache::make('theme_atrium', 'sitecounts');
        $counts = $cache->get('counts');
        if (is_array($counts)) {
            return $counts;
        }
        $counts = [
            'courses' => $DB->count_records_select('course', 'id <> ? AND visible = 1', [SITEID]),
            'users' => $DB->count_records_select('user', 'deleted = 0 AND confirmed = 1 AND id > 1'),
            'categories' => $DB->count_records('course_categories', ['visible' => 1]),
            'completions' => $DB->count_records_select('course_completions', 'timecompleted > 0'),
        ];
        $cache->set('counts', $counts);
        return $counts;
    }

    /**
     * Keep only the courses the current user may see, and never the site course.
     *
     * @param iterable $courses
     * @return core_course_list_element[]
     */
    private static function visible(iterable $courses): array {
        $out = [];
        foreach ($courses as $course) {
            if ((int) $course->id === SITEID) {
                continue;
            }
            if ($course->visible || has_capability('moodle/course:viewhiddencourses', \context_course::instance($course->id))) {
                $out[] = $course;
            }
        }
        return $out;
    }
}
