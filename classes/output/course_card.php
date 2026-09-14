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
use theme_atrium\local\courses;

/**
 * One course as a card: the same card on the front page, the catalogue and the enrolment page.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_card implements renderable, templatable {
    /**
     * Constructor.
     *
     * @param core_course_list_element $course
     * @param bool $showenrolled Whether to show the enrolled-user count.
     * @param bool $showprogress Whether to show the viewer's completion progress when enrolled.
     * @param string|null $price A formatted price, when the course has a paid enrolment.
     */
    public function __construct(
        /** @var core_course_list_element The course. */
        protected core_course_list_element $course,
        /** @var bool Show the enrolled count. */
        protected bool $showenrolled = true,
        /** @var bool Show completion progress. */
        protected bool $showprogress = true,
        /** @var string|null A formatted price to badge the card with. */
        protected ?string $price = null,
    ) {
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $USER, $CFG;
        $course = $this->course;
        $context = \context_course::instance($course->id);
        $category = core_course_category::get($course->category, IGNORE_MISSING);
        $image = courses::image($course);

        $enrolled = isloggedin() && !isguestuser() && is_enrolled($context, $USER, '', true);
        $progress = null;
        if ($enrolled && $this->showprogress && !empty($CFG->enablecompletion) && $course->enablecompletion) {
            require_once($CFG->libdir . '/completionlib.php');
            $progress = \core_completion\progress::get_course_progress_percentage(get_course($course->id));
        }

        $contacts = courses::contacts($course);
        $summary = '';
        if ($course->has_summary()) {
            $formatted = format_text($course->summary, $course->summaryformat, ['context' => $context]);
            $summary = shorten_text(strip_tags($formatted), 140);
        }

        return [
            'id' => $course->id,
            'url' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            'fullname' => $course->get_formatted_name(),
            'summary' => $summary,
            'imageurl' => $image['imageurl'],
            'gradient' => $image['gradient'],
            'category' => $category ? $category->get_formatted_name() : '',
            'categoryurl' => $category ? (new moodle_url('/course/index.php', ['categoryid' => $category->id]))->out(false) : '',
            'contacts' => $contacts,
            'firstcontactname' => $contacts[0]['name'] ?? '',
            'enrolledcount' => $this->showenrolled ? courses::enrolled_count($course->id) : null,
            'showenrolled' => $this->showenrolled,
            'enrolled' => $enrolled,
            'progress' => $progress === null ? null : (int) round($progress),
            'hasprogress' => $progress !== null,
            'hidden' => !$course->visible,
            'price' => $this->price,
            'hasprice' => $this->price !== null && $this->price !== '',
        ];
    }
}
