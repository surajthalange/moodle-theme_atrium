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

namespace theme_atrium\output;

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;
use core_course_category;
use core_course_list_element;
use moodle_url;
use stdClass;
use theme_atrium\local\courses;
use theme_atrium\local\focusmode;

/**
 * The course banner: image, name, teachers, the learner's progress, and a resume link.
 *
 * Rendered on the course page (and, compactly, on activity pages in focus mode).
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_banner implements renderable, templatable {
    /**
     * Constructor.
     *
     * @param stdClass $course
     */
    public function __construct(
        /** @var stdClass The course. */
        protected stdClass $course,
    ) {
    }

    /**
     * Whether the banner should render on the current page.
     *
     * @return bool
     */
    public static function wanted(): bool {
        global $PAGE;
        // The page has no __isset, so the course is read into a variable before testing it.
        $course = $PAGE->course;
        if ($PAGE->pagelayout !== 'course' || !$course || (int) $course->id === SITEID) {
            return false;
        }
        if ($PAGE->pagetype !== 'course-view-' . $course->format) {
            return false;
        }
        $value = get_config('theme_atrium', 'course_showbanner');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * Progress and resume data for the current user, shared with the focus bar.
     *
     * @param stdClass $course
     * @return array{hasprogress: bool, progress: int|null, resumeurl: string, resumename: string}
     */
    public static function learner_state(stdClass $course): array {
        global $CFG, $USER, $DB;
        $context = \context_course::instance($course->id);
        $state = ['hasprogress' => false, 'progress' => null, 'resumeurl' => '', 'resumename' => ''];
        if (!isloggedin() || isguestuser() || !is_enrolled($context, $USER, '', true)) {
            return $state;
        }
        if (!empty($CFG->enablecompletion) && !empty($course->enablecompletion)) {
            require_once($CFG->libdir . '/completionlib.php');
            $progress = \core_completion\progress::get_course_progress_percentage($course);
            if ($progress !== null) {
                $state['hasprogress'] = true;
                $state['progress'] = (int) round($progress);
            }
        }
        if ($DB->get_manager()->table_exists('block_recentlyaccesseditems')) {
            $recent = $DB->get_records(
                'block_recentlyaccesseditems',
                ['userid' => $USER->id, 'courseid' => $course->id],
                'timeaccess DESC',
                'id, cmid',
                0,
                5
            );
            $modinfo = get_fast_modinfo($course);
            foreach ($recent as $item) {
                $cm = $modinfo->cms[$item->cmid] ?? null;
                if ($cm && $cm->uservisible && $cm->url) {
                    $state['resumeurl'] = $cm->url->out(false);
                    $state['resumename'] = $cm->get_formatted_name();
                    break;
                }
            }
        }
        return $state;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $course = $this->course;
        $element = new core_course_list_element($course);
        $image = courses::image($element);
        $category = core_course_category::get($course->category, IGNORE_MISSING);
        $state = self::learner_state($course);

        return [
            'fullname' => $element->get_formatted_name(),
            'imageurl' => $image['imageurl'],
            'gradient' => $image['gradient'],
            'category' => $category ? $category->get_formatted_name() : '',
            'categoryurl' => $category ? (new moodle_url('/course/index.php', ['categoryid' => $category->id]))->out(false) : '',
            'contacts' => courses::contacts($element, 4),
            'hasprogress' => $state['hasprogress'],
            'progress' => $state['progress'],
            'complete' => $state['hasprogress'] && $state['progress'] >= 100,
            'resumeurl' => $state['resumeurl'],
            'resumename' => $state['resumename'],
            'hasresume' => $state['resumeurl'] !== '',
            'focusurl' => focusmode::applies() ? focusmode::toggle_url()->out(false) : '',
            'focuson' => focusmode::on(),
        ];
    }
}
