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

namespace theme_atrium\output\core;

use core_course_category;
use moodle_url;
use theme_atrium\local\catalogue;
use theme_atrium\local\courses;
use theme_atrium\local\enrolpage;
use theme_atrium\output\course_card;

/**
 * Course renderer: the catalogue replaces core's category listing and search results.
 *
 * course_category() and search_courses() draw the catalogue; enrolment_options() draws
 * the enrolment page. Everything else the course renderer draws (the front page combo
 * list, the course search form, activity chooser fragments) is core's.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \core_course_renderer {
    /**
     * The catalogue for a category, or for all courses.
     *
     * @param core_course_category|\stdClass|int|null $category
     * @return string
     */
    public function course_category($category) {
        if (empty($category)) {
            $coursecat = core_course_category::user_top();
        } else if ($category instanceof core_course_category) {
            $coursecat = $category;
        } else {
            $coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
        }
        $this->page->set_title($coursecat->id ? $coursecat->get_formatted_name() : get_string('fulllistofcourses'));
        return $this->catalogue($coursecat, '');
    }

    /**
     * Search results, in the catalogue.
     *
     * @param array $searchcriteria
     * @return string
     */
    public function search_courses($searchcriteria) {
        $search = trim((string) ($searchcriteria['search'] ?? ''));
        $coursecat = core_course_category::user_top();
        if (!empty($searchcriteria['categoryid'])) {
            $coursecat = core_course_category::get($searchcriteria['categoryid'], IGNORE_MISSING) ?: $coursecat;
        }
        if ($search === '') {
            return $this->render_from_template('theme_atrium/catalogue', $this->export($coursecat, '', true));
        }
        return $this->catalogue($coursecat, $search);
    }

    /**
     * Render the catalogue.
     *
     * @param core_course_category $coursecat
     * @param string $search
     * @return string
     */
    private function catalogue(core_course_category $coursecat, string $search): string {
        return $this->render_from_template('theme_atrium/catalogue', $this->export($coursecat, $search, false));
    }

    /**
     * The template context for the catalogue page.
     *
     * @param core_course_category $coursecat
     * @param string $search
     * @param bool $searchonly Whether this is the search page with no term yet.
     * @return array
     */
    private function export(core_course_category $coursecat, string $search, bool $searchonly): array {
        $sort = catalogue::sort_or_default(optional_param('sort', null, PARAM_ALPHA));
        $view = catalogue::view(optional_param('view', null, PARAM_ALPHA));
        $page = max(0, optional_param('page', 0, PARAM_INT));
        $perpage = catalogue::perpage_setting();
        $catalogue = new catalogue($coursecat, $search, $sort, $page, $perpage);

        $baseurl = $search !== ''
            ? new moodle_url('/course/search.php', ['search' => $search])
            : new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($sort !== catalogue::sort_or_default(null)) {
            $baseurl->param('sort', $sort);
        }

        $result = $searchonly ? ['courses' => [], 'total' => 0] : $catalogue->courses();
        $showenrolled = $this->setting('catalogue_showenrolled', true);
        $showprogress = $this->setting('catalogue_showprogress', true);
        $prices = $this->setting('catalogue_showprice', true)
            ? catalogue::prices(array_map(fn($c) => $c->id, $result['courses']))
            : [];

        $cards = [];
        foreach ($result['courses'] as $course) {
            $cards[] = (new course_card($course, $showenrolled, $showprogress, $prices[$course->id] ?? null))
                ->export_for_template($this);
        }

        $sorts = [];
        foreach (catalogue::SORTS as $key) {
            $sorts[] = [
                'key' => $key,
                'label' => get_string('catalogue_sort_' . $key, 'theme_atrium'),
                'url' => (new moodle_url($baseurl, ['sort' => $key, 'page' => 0]))->out(false),
                'active' => $key === $sort,
            ];
        }

        $parents = [];
        foreach ($coursecat->get_parents() as $parentid) {
            $parent = core_course_category::get($parentid, IGNORE_MISSING);
            if ($parent) {
                $parents[] = [
                    'name' => $parent->get_formatted_name(),
                    'url' => (new moodle_url('/course/index.php', ['categoryid' => $parent->id]))->out(false),
                ];
            }
        }

        $subcategories = [];
        foreach ($catalogue->subcategories() as $child) {
            $subcategories[] = [
                'name' => $child['name'],
                'count' => $child['count'],
                'url' => (new moodle_url('/course/index.php', ['categoryid' => $child['id']]))->out(false),
            ];
        }

        $context = $coursecat->id ? \context_coursecat::instance($coursecat->id) : \context_system::instance();
        $canmanage = has_capability('moodle/category:manage', $context) || has_capability('moodle/course:create', $context);
        // Managers get core's "More" menu (manage courses and categories, add a course);
        // the catalogue's own crumbs and search replace the rest of core's action bar.
        $manageactions = '';
        if ($canmanage && !$searchonly) {
            $bar = new \core_course\output\category_action_bar($this->page, $coursecat);
            $manageactions = (string) ($bar->export_for_template($this)['additionaloptions'] ?? '');
        }

        $description = '';
        if ($coursecat->id) {
            $chelper = new \coursecat_helper();
            $description = (string) $chelper->get_category_formatted_description($coursecat);
        }

        $pagingbar = '';
        if ($result['total'] > $perpage) {
            $pagingbar = $this->paging_bar($result['total'], $page, $perpage, $baseurl);
        }

        return [
            'heading' => $search !== ''
                ? get_string('catalogue_searchheading', 'theme_atrium', s($search))
                : ($coursecat->id ? $coursecat->get_formatted_name() : get_string('fulllistofcourses')),
            'description' => $description,
            'parents' => $parents,
            'hasparents' => !empty($parents),
            'allcoursesurl' => (new moodle_url('/course/index.php'))->out(false),
            'iscategory' => (bool) $coursecat->id,
            'subcategories' => $subcategories,
            'hassubcategories' => !empty($subcategories),
            'search' => $search,
            'searchurl' => (new moodle_url('/course/search.php'))->out(false),
            'categoryid' => $coursecat->id,
            'sorts' => $sorts,
            'sortlabel' => get_string('catalogue_sort_' . $sort, 'theme_atrium'),
            'isgrid' => $view === 'grid',
            'gridurl' => (new moodle_url($baseurl, ['view' => 'grid']))->out(false),
            'listurl' => (new moodle_url($baseurl, ['view' => 'list']))->out(false),
            'cards' => $cards,
            'hascourses' => !empty($cards),
            'total' => $result['total'],
            'totaltext' => $result['total'] === 1
                ? get_string('catalogue_total_one', 'theme_atrium')
                : get_string('catalogue_total', 'theme_atrium', $result['total']),
            'pagingbar' => $pagingbar,
            'manageactions' => $manageactions,
            'searchonly' => $searchonly,
        ];
    }

    /**
     * The enrolment page: a course landing page with core's enrolment forms in a side card.
     *
     * @param \stdClass $course
     * @param array $widgets Enrolment plugin forms, keyed by instance id.
     * @param \core\url|null $returnurl
     * @return string
     */
    public function enrolment_options(\stdClass $course, array $widgets, ?\core\url $returnurl = null): string {
        $message = '';
        $continuebutton = '';
        if (!$widgets) {
            if (isguestuser()) {
                $message = get_string('noguestaccess', 'enrol');
                $continuebutton = $this->output->continue_button(get_login_url());
            } else {
                $url = $returnurl ?: (get_local_referer(false) ?: new moodle_url('/index.php'));
                $message = get_string('notenrollable', 'enrol');
                $continuebutton = $this->output->continue_button($url);
            }
        }

        $element = new \core_course_list_element($course);
        $page = new enrolpage($course);
        $outline = $page->outline();
        $image = courses::image($element);
        $category = core_course_category::get($course->category, IGNORE_MISSING);
        $context = \context_course::instance($course->id);
        $prices = catalogue::prices([$course->id]);

        $summary = '';
        if ($element->has_summary()) {
            $summary = format_text($course->summary, $course->summaryformat, ['context' => $context]);
        }

        $sections = [];
        foreach ($outline['sections'] as $section) {
            $sections[] = $section + ['hasmore' => $section['more'] > 0, 'count' => count($section['activities'])];
        }

        $related = [];
        if ($this->setting('enrol_showrelated', true)) {
            foreach ($page->related() as $other) {
                $related[] = (new course_card($other, true, false))->export_for_template($this);
            }
        }

        $data = [
            'fullname' => $element->get_formatted_name(),
            'summary' => $summary,
            'imageurl' => $image['imageurl'],
            'gradient' => $image['gradient'],
            'category' => $category ? $category->get_formatted_name() : '',
            'categoryurl' => $category ? (new moodle_url('/course/index.php', ['categoryid' => $category->id]))->out(false) : '',
            'catalogueurl' => (new moodle_url('/course/index.php'))->out(false),
            'facts' => $page->facts($outline),
            'showoutline' => $this->setting('enrol_showoutline', true) && !empty($sections),
            'sections' => $sections,
            'outlinemore' => $outline['more'],
            'showinstructors' => $this->setting('enrol_showinstructors', true),
            'instructors' => $page->instructors(),
            'widgets' => array_values($widgets),
            'haswidgets' => !empty($widgets),
            'message' => $message,
            'continuebutton' => $continuebutton,
            'price' => $prices[$course->id] ?? '',
            'hasprice' => isset($prices[$course->id]),
            'related' => $related,
            'hasrelated' => !empty($related),
        ];
        $data['showinstructors'] = $data['showinstructors'] && !empty($data['instructors']);
        return $this->render_from_template('theme_atrium/enrolpage', $data);
    }

    /**
     * A boolean theme setting with a default.
     *
     * @param string $name
     * @param bool $default
     * @return bool
     */
    private function setting(string $name, bool $default): bool {
        $value = get_config('theme_atrium', $name);
        return $value === false || $value === '' ? $default : (bool) $value;
    }
}
