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
use moodle_url;
use stdClass;
use theme_atrium\local\stats;

/**
 * The greeting band and progress tiles at the top of the dashboard.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_hero implements renderable, templatable {
    /** @var array<string, array{icon: string, url: string}> Tile definitions in display order. */
    private const TILES = [
        'inprogress' => ['icon' => 'inprogress', 'url' => '/my/courses.php'],
        'completed' => ['icon' => 'completed', 'url' => '/my/courses.php'],
        'due' => ['icon' => 'due', 'url' => '/calendar/view.php?view=upcoming'],
        'unread' => ['icon' => 'unread', 'url' => '/message/index.php'],
    ];

    /**
     * Constructor.
     *
     * @param stdClass $user The user whose dashboard this is.
     */
    public function __construct(
        /** @var stdClass The user. */
        protected stdClass $user,
    ) {
    }

    /**
     * Whether the hero should render for the current page and user.
     *
     * @return bool
     */
    public static function wanted(): bool {
        global $PAGE;
        // The dashboard itself, not every page that borrows its layout (messaging does).
        if ($PAGE->pagetype !== 'my-index' || $PAGE->pagelayout !== 'mydashboard' || !isloggedin() || isguestuser()) {
            return false;
        }
        $show = get_config('theme_atrium', 'showhero');
        return $show === false || $show === '' ? true : (bool) $show;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $greeting = (string) get_config('theme_atrium', 'herogreeting');
        if (trim($greeting) === '') {
            $greeting = get_string('herogreeting_default', 'theme_atrium');
        }
        $greeting = str_replace(
            ['{firstname}', '{fullname}'],
            [$this->user->firstname, fullname($this->user)],
            $greeting
        );

        // Four zeros tell a new user nothing and a new teacher less. When there is nothing
        // to count, the tiles give way to one panel that says what to do next.
        $empty = self::empty_state();

        $tiles = [];
        $counts = null;
        foreach ($empty === null ? self::TILES : [] as $key => $tile) {
            $show = get_config('theme_atrium', 'showstat_' . $key);
            if (!($show === false || $show === '' ? true : (bool) $show)) {
                continue;
            }
            $counts = $counts ?? stats::for_user((int) $this->user->id);
            $tiles[] = [
                'key' => $key,
                'icon' => $tile['icon'],
                'url' => (new moodle_url($tile['url']))->out(false),
                'count' => $counts[$key],
                'label' => get_string('stat:' . $key, 'theme_atrium'),
                'help' => $key === 'unread' ? get_string('stat:unread_help', 'theme_atrium') : '',
            ];
        }

        return [
            'greeting' => format_string($greeting, true, ['context' => \context_system::instance()]),
            'date' => userdate(time(), get_string('strftimedaydate', 'langconfig')),
            'tiles' => $tiles,
            'hastiles' => !empty($tiles),
            'empty' => $empty,
            'hasempty' => $empty !== null,
            'hasimage' => !empty(get_config('theme_atrium', 'heroimage')),
        ];
    }

    /**
     * What to say when the user is enrolled in nothing, or null when they are enrolled in
     * something and the tiles have real numbers to show.
     *
     * The wording follows what the person can actually do next: somebody who may create a
     * course is told to create one, somebody who may not is pointed at the catalogue, and
     * somebody who can do neither is told plainly that enrolment is handled for them,
     * rather than being sent to a page that will turn them away.
     *
     * @return array{title: string, text: string, label: string, url: string}|null
     */
    private static function empty_state(): ?array {
        // One row is enough to answer "is there anything here"; the full list is not needed.
        if (enrol_get_my_courses('id', 'id ASC', 1)) {
            return null;
        }

        $cancreate = \core_course_category::make_categories_list('moodle/course:create');
        if (!empty($cancreate)) {
            return [
                'title' => get_string('empty_nocourses_staff', 'theme_atrium'),
                'text' => get_string('empty_nocourses_staff_text', 'theme_atrium'),
                'label' => get_string('empty_nocourses_staff_action', 'theme_atrium'),
                'url' => (new moodle_url('/course/edit.php', ['category' => array_key_first($cancreate)]))->out(false),
            ];
        }

        // Counted with the user's own visibility, recursively, and cached by core.
        $top = \core_course_category::user_top();
        if ($top && $top->get_courses_count(['recursive' => true])) {
            return [
                'title' => get_string('empty_nocourses', 'theme_atrium'),
                'text' => get_string('empty_nocourses_text', 'theme_atrium'),
                'label' => get_string('empty_nocourses_action', 'theme_atrium'),
                'url' => (new moodle_url('/course/index.php'))->out(false),
            ];
        }

        // Nothing to browse and nothing to create: say so rather than offer a dead link.
        return [
            'title' => get_string('empty_nocourses', 'theme_atrium'),
            'text' => get_string('empty_nocourses_closed', 'theme_atrium'),
            'label' => '',
            'url' => '',
        ];
    }
}
