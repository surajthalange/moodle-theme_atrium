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
use stdClass;

/**
 * What the enrolment page shows about a course before a learner joins it.
 *
 * Facts, outline and instructors are computed from data the visitor is allowed to see:
 * only visible sections and activities appear, and nothing links into the course, since
 * the visitor cannot open it yet.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class enrolpage {
    /** @var int Sections shown in the outline before "and N more". */
    public const MAX_SECTIONS = 12;

    /** @var int Activities named per section before "and N more". */
    public const MAX_ACTIVITIES = 8;

    /** @var int Related courses shown. */
    public const RELATED = 3;

    /**
     * Constructor.
     *
     * @param stdClass $course The course record.
     */
    public function __construct(
        /** @var stdClass The course. */
        public readonly stdClass $course,
    ) {
    }

    /**
     * The visible sections and their visible activities.
     *
     * @return array{sections: array<int, array{name: string, activities: array<int, array{name: string, modname: string,
     *     purpose: string}>, more: int}>, sectioncount: int, activitycount: int, more: int}
     */
    public function outline(): array {
        $modinfo = get_fast_modinfo($this->course);
        $sections = [];
        $sectioncount = 0;
        $activitycount = 0;
        foreach ($modinfo->get_section_info_all() as $section) {
            if (!$section->visible || $section->is_delegated() || $section->is_orphan()) {
                continue;
            }
            $activities = [];
            foreach ($section->get_sequence_cm_infos() as $cm) {
                // Raw visibility, not the viewer's: a visitor has no access yet, but may see the outline.
                if (!$cm->visible || !$cm->visibleoncoursepage || $cm->deletioninprogress) {
                    continue;
                }
                // Labels are text, question banks are not activities, subsections list their own.
                if (in_array($cm->modname, ['label', 'qbank', 'subsection'], true)) {
                    continue;
                }
                $activities[] = [
                    'name' => $cm->get_formatted_name(),
                    'modname' => $cm->modname,
                    'purpose' => plugin_supports('mod', $cm->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER),
                ];
            }
            $activitycount += count($activities);
            // The general section is only worth listing when it holds something.
            if ($section->section == 0 && !$activities) {
                continue;
            }
            $sectioncount++;
            $sections[] = [
                'name' => get_section_name($this->course, $section),
                'activities' => array_slice($activities, 0, self::MAX_ACTIVITIES),
                'more' => max(0, count($activities) - self::MAX_ACTIVITIES),
            ];
        }
        return [
            'sections' => array_slice($sections, 0, self::MAX_SECTIONS),
            'sectioncount' => $sectioncount,
            'activitycount' => $activitycount,
            'more' => max(0, count($sections) - self::MAX_SECTIONS),
        ];
    }

    /**
     * The facts strip: label and value pairs, only for facts that apply.
     *
     * @param array $outline From outline().
     * @return array<int, array{label: string, value: string, icon: string}>
     */
    public function facts(array $outline): array {
        global $CFG;
        $facts = [];
        $facts[] = [
            'label' => get_string('enrol_fact_sections', 'theme_atrium'),
            'value' => (string) $outline['sectioncount'],
            'icon' => 'i/section',
        ];
        $facts[] = [
            'label' => get_string('enrol_fact_activities', 'theme_atrium'),
            'value' => (string) $outline['activitycount'],
            'icon' => 'i/checkedcircle',
        ];
        $facts[] = [
            'label' => get_string('enrol_fact_learners', 'theme_atrium'),
            'value' => (string) courses::enrolled_count((int) $this->course->id),
            'icon' => 'i/users',
        ];
        if (!empty($this->course->startdate)) {
            $facts[] = [
                'label' => get_string('startdate'),
                'value' => userdate($this->course->startdate, get_string('strftimedate', 'langconfig')),
                'icon' => 'i/calendar',
            ];
        }
        if (!empty($this->course->enddate) && !empty($this->course->startdate)) {
            $weeks = (int) round(($this->course->enddate - $this->course->startdate) / WEEKSECS);
            if ($weeks > 0) {
                $facts[] = [
                    'label' => get_string('enrol_fact_duration', 'theme_atrium'),
                    'value' => get_string('enrol_fact_weeks', 'theme_atrium', $weeks),
                    'icon' => 'i/duration',
                ];
            }
        }
        if (!empty($CFG->enablecompletion) && !empty($this->course->enablecompletion)) {
            $facts[] = [
                'label' => get_string('enrol_fact_completion', 'theme_atrium'),
                'value' => get_string('enrol_fact_tracked', 'theme_atrium'),
                'icon' => 'i/completion_self',
            ];
        }
        return $facts;
    }

    /**
     * Course contacts with a short bio from their profile.
     *
     * @return array<int, array{name: string, role: string, pictureurl: string, bio: string}>
     */
    public function instructors(): array {
        global $DB, $PAGE;
        $element = new core_course_list_element($this->course);
        if (!$element->has_course_contacts()) {
            return [];
        }
        $out = [];
        foreach ($element->get_course_contacts() as $userid => $contact) {
            $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0]);
            if (!$user) {
                continue;
            }
            $picture = new \user_picture($user);
            $picture->size = 100;
            $context = \context_course::instance($this->course->id);
            $bio = '';
            if (!empty($user->description)) {
                $bio = shorten_text(strip_tags(format_text($user->description, $user->descriptionformat, [
                    'context' => $context, 'filter' => false,
                ])), 220);
            }
            $out[] = [
                'name' => $contact['username'],
                'role' => $contact['rolename'] ?? '',
                'pictureurl' => $picture->get_url($PAGE)->out(false),
                'bio' => $bio,
            ];
        }
        return $out;
    }

    /**
     * Other visible courses in the same category.
     *
     * @return core_course_list_element[]
     */
    public function related(): array {
        $out = [];
        foreach (courses::in_category((int) $this->course->category, self::RELATED + 1) as $course) {
            if ((int) $course->id === (int) $this->course->id) {
                continue;
            }
            $out[] = $course;
            if (count($out) === self::RELATED) {
                break;
            }
        }
        return $out;
    }
}
