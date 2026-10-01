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

use core_course_category;
use core_course_list_element;
use moodle_url;

/**
 * The command palette: one box, from any page, that finds courses, the current course's
 * activities, the pages a user goes to, and (for administrators) admin pages.
 *
 * Everything comes from Moodle's own APIs with the caller's permissions: course search
 * honours visibility and capabilities, activities come from modinfo with uservisible, and
 * the admin tree search runs only for people who can reach the admin tree.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class palette {
    /** @var int Results per group at most. */
    public const LIMIT = 6;

    /**
     * Whether the site shows the palette.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $value = get_config('theme_atrium', 'navbar_palette');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * Whether the palette is offered to the current user.
     *
     * @return bool
     */
    public static function wanted(): bool {
        return self::enabled() && isloggedin() && !isguestuser();
    }

    /**
     * Search.
     *
     * @param string $query The typed text; empty shows the starting set.
     * @param int $courseid The course the user is looking at, or 0.
     * @param string $pageurl The page the user is on, already validated as local, or ''.
     * @return array<int, array{title: string, items: array<int, array{label: string, url: string, icon: string, meta: string}>}>
     */
    public static function search(string $query, int $courseid = 0, string $pageurl = ''): array {
        $query = trim($query);
        $groups = [];

        $pages = self::pages($query);
        if ($pages) {
            $groups[] = ['title' => get_string('palette_pages', 'theme_atrium'), 'items' => $pages];
        }
        // Commands sit below the pages deliberately. With an empty box the first result is
        // highlighted and Enter runs it, and that first result should be somewhere to go,
        // never something that changes the site.
        $actions = palette_actions::search($query, $pageurl, self::LIMIT);
        if ($actions) {
            $groups[] = ['title' => get_string('palette_actions', 'theme_atrium'), 'items' => $actions];
        }
        $activities = $courseid > 1 ? self::activities($query, $courseid) : [];
        if ($activities) {
            $groups[] = ['title' => get_string('palette_activities', 'theme_atrium'), 'items' => $activities];
        }
        $courses = self::courses($query);
        if ($courses) {
            $groups[] = ['title' => get_string('palette_courses', 'theme_atrium'), 'items' => $courses];
        }
        $admin = $query !== '' ? self::admin($query) : [];
        if ($admin) {
            $groups[] = ['title' => get_string('palette_admin', 'theme_atrium'), 'items' => $admin];
        }
        return $groups;
    }

    /**
     * The pages every user goes to, filtered by the query.
     *
     * @param string $query
     * @return array
     */
    private static function pages(string $query): array {
        global $CFG;
        $all = [
            ['label' => get_string('myhome'), 'url' => '/my/', 'icon' => 'fa-gauge'],
            ['label' => get_string('mycourses'), 'url' => '/my/courses.php', 'icon' => 'fa-graduation-cap'],
            ['label' => get_string('courses'), 'url' => '/course/index.php', 'icon' => 'fa-book'],
            ['label' => get_string('calendar', 'calendar'), 'url' => '/calendar/view.php?view=month', 'icon' => 'fa-calendar'],
            ['label' => get_string('messages', 'message'), 'url' => '/message/index.php', 'icon' => 'fa-comments'],
            ['label' => get_string('notifications'), 'url' => '/message/output/popup/notifications.php', 'icon' => 'fa-bell'],
            ['label' => get_string('grades'), 'url' => '/grade/report/overview/index.php', 'icon' => 'fa-chart-line'],
            ['label' => get_string('profile'), 'url' => '/user/profile.php', 'icon' => 'fa-user'],
            ['label' => get_string('preferences'), 'url' => '/user/preferences.php', 'icon' => 'fa-sliders'],
            ['label' => get_string('privatefiles'), 'url' => '/user/files.php', 'icon' => 'fa-folder'],
            ['label' => get_string('sitehome'), 'url' => '/', 'icon' => 'fa-house'],
        ];
        if (is_siteadmin()) {
            $all[] = ['label' => get_string('administrationsite'), 'url' => '/admin/search.php', 'icon' => 'fa-gear'];
            $all[] = ['label' => get_string('purgecaches', 'admin'), 'url' => '/admin/purgecaches.php', 'icon' => 'fa-broom'];
        }
        $items = [];
        foreach ($all as $page) {
            if ($query !== '' && !self::matches($page['label'], $query)) {
                continue;
            }
            $items[] = [
                'label' => $page['label'],
                'url' => (new moodle_url($page['url']))->out(false),
                'icon' => $page['icon'],
                'meta' => '',
            ];
        }
        return array_slice($items, 0, $query === '' ? 5 : self::LIMIT);
    }

    /**
     * Activities the user can see in the current course, filtered by the query.
     *
     * @param string $query
     * @param int $courseid
     * @return array
     */
    private static function activities(string $query, int $courseid): array {
        $course = get_course($courseid);
        $items = [];
        foreach (get_fast_modinfo($course)->get_cms() as $cm) {
            if (!$cm->uservisible || !$cm->url || $cm->modname === 'label') {
                continue;
            }
            $name = $cm->get_formatted_name();
            if ($query !== '' && !self::matches($name, $query)) {
                continue;
            }
            $items[] = [
                'label' => $name,
                'url' => $cm->url->out(false),
                'icon' => '',
                'meta' => (string) $cm->get_module_type_name(),
            ];
            if (count($items) === self::LIMIT) {
                break;
            }
        }
        return $items;
    }

    /**
     * Courses: the user's own when the query is empty, otherwise a site search.
     *
     * @param string $query
     * @return array
     */
    private static function courses(string $query): array {
        $items = [];
        if ($query === '') {
            $courses = array_slice(enrol_get_my_courses('*', 'visible DESC, sortorder ASC', self::LIMIT), 0, self::LIMIT);
        } else {
            $courses = core_course_category::search_courses(['search' => $query], ['limit' => self::LIMIT]);
        }
        foreach ($courses as $course) {
            $element = $course instanceof core_course_list_element ? $course : new core_course_list_element($course);
            $category = core_course_category::get($element->category, IGNORE_MISSING);
            $items[] = [
                'label' => $element->get_formatted_name(),
                'url' => (new moodle_url('/course/view.php', ['id' => $element->id]))->out(false),
                'icon' => 'fa-book-open',
                'meta' => $category ? $category->get_formatted_name() : '',
            ];
        }
        return $items;
    }

    /**
     * Admin pages whose title matches, for site administrators.
     *
     * Building the admin tree costs about a second, so the flat list of pages is cached
     * for an hour (and cleared with every cache purge). Only site administrators get admin
     * results: they can open every page, so the cached list needs no per-user access check.
     *
     * @param string $query
     * @return array
     */
    private static function admin(string $query): array {
        if (!is_siteadmin()) {
            return [];
        }
        $items = [];
        foreach (self::admin_pages() as $page) {
            if (!self::matches($page['label'], $query)) {
                continue;
            }
            $items[] = ['label' => $page['label'], 'url' => $page['url'], 'icon' => 'fa-gear', 'meta' => $page['meta']];
            if (count($items) === self::LIMIT) {
                break;
            }
        }
        return $items;
    }

    /**
     * Every admin settings page and external page, flattened and cached.
     *
     * @return array<int, array{label: string, url: string, meta: string}>
     */
    public static function admin_pages(): array {
        global $CFG;
        $cache = \cache::make('theme_atrium', 'adminpages');
        $pages = $cache->get('pages');
        if (is_array($pages)) {
            return $pages;
        }
        require_once($CFG->libdir . '/adminlib.php');
        $pages = [];
        $walk = function (\part_of_admin_tree $node, string $parent) use (&$walk, &$pages): void {
            if ($node instanceof \admin_category) {
                // Pages are labelled with their top-level category: Users, Courses, Plugins...
                $title = $parent === '' && !($node instanceof \admin_root) ? (string) $node->visiblename : $parent;
                foreach ($node->get_children() as $child) {
                    $walk($child, $title);
                }
                return;
            }
            if ($node->is_hidden()) {
                return;
            }
            if ($node instanceof \admin_settingpage) {
                $url = (new moodle_url('/admin/settings.php', ['section' => $node->name]))->out(false);
            } else if ($node instanceof \admin_externalpage) {
                $url = (string) $node->url;
            } else {
                return;
            }
            $pages[] = ['label' => (string) $node->visiblename, 'url' => $url, 'meta' => $parent];
        };
        $walk(admin_get_root(false, false), '');
        $cache->set('pages', $pages);
        return $pages;
    }

    /**
     * Case-insensitive "contains".
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private static function matches(string $haystack, string $needle): bool {
        return \core_text::strpos(\core_text::strtolower($haystack), \core_text::strtolower($needle)) !== false;
    }
}
