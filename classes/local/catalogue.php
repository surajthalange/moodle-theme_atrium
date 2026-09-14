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

/**
 * The course catalogue: a page of courses in a category (or matching a search), sorted.
 *
 * Browsing a category queries the course table directly so it can sort by creation date
 * and by popularity, which core's category listing cannot; visibility follows core's rule
 * (hidden courses only for users who may view hidden courses in the category). Searching
 * delegates to core's search so its permission handling is kept as is.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class catalogue {
    /** @var string[] Sort keys in the order the toolbar offers them. */
    public const SORTS = ['name', 'newest', 'popular'];

    /** @var string[] View keys. */
    public const VIEWS = ['grid', 'list'];

    /** @var string The user preference remembering grid or list. */
    public const VIEW_PREFERENCE = 'theme_atrium_catalogueview';

    /** @var string[] Enrolment plugins whose instances carry a price. */
    public const PAID_ENROL = ['fee', 'paypal'];

    /**
     * Constructor.
     *
     * @param core_course_category $category The category being browsed (user_top for all courses).
     * @param string $search A search term, or empty.
     * @param string $sort One of SORTS.
     * @param int $page Zero-based page.
     * @param int $perpage Courses per page.
     */
    public function __construct(
        /** @var core_course_category The category. */
        public readonly core_course_category $category,
        /** @var string The search term. */
        public readonly string $search,
        /** @var string The sort key. */
        public readonly string $sort,
        /** @var int The page. */
        public readonly int $page,
        /** @var int Courses per page. */
        public readonly int $perpage,
    ) {
    }

    /**
     * Courses per page from the setting, 6 to 48.
     *
     * @return int
     */
    public static function perpage_setting(): int {
        return max(6, min(48, (int) (get_config('theme_atrium', 'catalogue_perpage') ?: 12)));
    }

    /**
     * Validate a sort key against SORTS, falling back to the site default.
     *
     * @param string|null $sort
     * @return string
     */
    public static function sort_or_default(?string $sort): string {
        if ($sort !== null && in_array($sort, self::SORTS, true)) {
            return $sort;
        }
        $default = (string) get_config('theme_atrium', 'catalogue_defaultsort');
        return in_array($default, self::SORTS, true) ? $default : 'name';
    }

    /**
     * The view to use: an explicit request (which is remembered), else the preference, else grid.
     *
     * @param string|null $requested
     * @return string
     */
    public static function view(?string $requested): string {
        if ($requested !== null && in_array($requested, self::VIEWS, true)) {
            if (isloggedin() && !isguestuser()) {
                set_user_preference(self::VIEW_PREFERENCE, $requested);
            }
            return $requested;
        }
        $saved = isloggedin() && !isguestuser() ? get_user_preferences(self::VIEW_PREFERENCE) : null;
        return in_array($saved, self::VIEWS, true) ? $saved : 'grid';
    }

    /**
     * The page of courses and the total, as list elements.
     *
     * @return array{courses: core_course_list_element[], total: int}
     */
    public function courses(): array {
        if ($this->search !== '') {
            return $this->search_page();
        }
        return $this->browse_page();
    }

    /**
     * Search through core, so its capability handling applies.
     *
     * @return array{courses: core_course_list_element[], total: int}
     */
    private function search_page(): array {
        $criteria = ['search' => $this->search];
        if ($this->category->id) {
            $criteria['categoryid'] = $this->category->id;
        }
        $options = [
            'sort' => ['fullname' => 1],
            'offset' => $this->page * $this->perpage,
            'limit' => $this->perpage,
        ];
        $courses = core_course_category::search_courses($criteria, $options);
        $total = core_course_category::search_courses_count($criteria);
        return ['courses' => array_values($courses), 'total' => $total];
    }

    /**
     * Browse the category and its subcategories directly.
     *
     * @return array{courses: core_course_list_element[], total: int}
     */
    private function browse_page(): array {
        global $DB;

        // Only categories the user may list courses in, which is what core's own listing shows.
        $visible = array_keys(core_course_category::make_categories_list());
        if ($this->category->id) {
            $wanted = array_merge([$this->category->id], $this->category->get_all_children_ids());
            $visible = array_values(array_intersect($wanted, $visible));
        }
        if (!$visible) {
            return ['courses' => [], 'total' => 0];
        }
        [$insql, $params] = $DB->get_in_or_equal($visible, SQL_PARAMS_NAMED, 'cat');
        $params['site'] = SITEID;
        $where = "c.id <> :site AND c.category $insql";
        if (!$this->can_see_hidden()) {
            $where .= ' AND c.visible = 1';
        }

        $total = $DB->count_records_sql("SELECT COUNT(1) FROM {course} c WHERE $where", $params);

        switch ($this->sort) {
            case 'newest':
                $order = 'c.timecreated DESC, c.id DESC';
                $select = 'c.*';
                $join = '';
                break;
            case 'popular':
                $order = 'enrolments DESC, c.fullname ASC';
                $select = 'c.*, (SELECT COUNT(1) FROM {user_enrolments} ue JOIN {enrol} e ON e.id = ue.enrolid'
                    . ' WHERE e.courseid = c.id AND ue.status = 0 AND e.status = 0) AS enrolments';
                $join = '';
                break;
            default:
                $order = 'c.fullname ASC, c.id ASC';
                $select = 'c.*';
                $join = '';
        }
        $records = $DB->get_records_sql(
            "SELECT $select FROM {course} c $join WHERE $where ORDER BY $order",
            $params,
            $this->page * $this->perpage,
            $this->perpage
        );
        $courses = [];
        foreach ($records as $record) {
            unset($record->enrolments);
            $courses[] = new core_course_list_element($record);
        }
        return ['courses' => $courses, 'total' => $total];
    }

    /**
     * Whether the user may see hidden courses in this category.
     *
     * @return bool
     */
    private function can_see_hidden(): bool {
        $context = $this->category->id
            ? \context_coursecat::instance($this->category->id)
            : \context_system::instance();
        return has_capability('moodle/course:viewhiddencourses', $context);
    }

    /**
     * Visible subcategories with their course counts.
     *
     * @return array<int, array{id: int, name: string, count: int}>
     */
    public function subcategories(): array {
        $out = [];
        foreach ($this->category->get_children() as $child) {
            $out[] = [
                'id' => $child->id,
                'name' => $child->get_formatted_name(),
                'count' => $child->get_courses_count(['recursive' => true]),
            ];
        }
        return $out;
    }

    /**
     * Prices for a set of courses from their enabled fee-style enrolment instances.
     *
     * @param int[] $courseids
     * @return array<int, string> Course id => formatted price, for courses that have one.
     */
    public static function prices(array $courseids): array {
        global $DB;
        if (!$courseids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');
        [$enrolsql, $enrolparams] = $DB->get_in_or_equal(self::PAID_ENROL, SQL_PARAMS_NAMED, 'e');
        $records = $DB->get_records_select(
            'enrol',
            "courseid $insql AND enrol $enrolsql AND status = :enabled AND cost IS NOT NULL",
            $params + $enrolparams + ['enabled' => ENROL_INSTANCE_ENABLED],
            'sortorder ASC',
            'id, courseid, cost, currency'
        );
        $prices = [];
        foreach ($records as $record) {
            $cost = (float) $record->cost;
            if ($cost <= 0 || isset($prices[$record->courseid])) {
                continue;
            }
            $prices[$record->courseid] = self::format_price($cost, (string) $record->currency);
        }
        return $prices;
    }

    /**
     * Format a price in its currency for the current language.
     *
     * @param float $cost
     * @param string $currency ISO code
     * @return string
     */
    public static function format_price(float $cost, string $currency): string {
        return \core_payment\helper::get_cost_as_string($cost, $currency ?: 'USD');
    }
}
