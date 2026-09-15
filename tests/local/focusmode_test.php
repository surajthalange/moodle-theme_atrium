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

use PHPUnit\Framework\Attributes\CoversClass;
use theme_atrium\output\course_banner;

/**
 * Tests for focus mode and the course banner.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\focusmode::class)]
#[CoversClass(\theme_atrium\output\course_banner::class)]
final class focusmode_test extends \advanced_testcase {
    /**
     * Focus mode applies on course and activity pages for real users, follows the
     * preference, and can be disabled site-wide.
     */
    public function test_focus_mode(): void {
        global $PAGE;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $PAGE->set_course($course);
        $PAGE->set_pagelayout('course');
        $PAGE->set_pagetype('course-view-topics');
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        $this->assertTrue(focusmode::enabled());
        $this->assertFalse(focusmode::applies(), 'Not logged in');

        $this->setUser($user);
        $this->assertTrue(focusmode::applies());
        $this->assertFalse(focusmode::on());
        $this->assertFalse(focusmode::active());
        $this->assertSame('1', focusmode::toggle_url()->get_param('state'));

        focusmode::set(true);
        $this->assertTrue(focusmode::on());
        $this->assertTrue(focusmode::active());
        $this->assertSame('0', focusmode::toggle_url()->get_param('state'));
        $this->assertSame('/course/view.php?id=' . $course->id, focusmode::toggle_url()->get_param('returnurl'));

        $PAGE->set_pagelayout('incourse');
        $this->assertTrue(focusmode::active(), 'Activity pages too');

        $PAGE->set_pagelayout('mydashboard');
        $this->assertFalse(focusmode::applies(), 'Not outside the course');

        set_config('course_enablefocus', 0, 'theme_atrium');
        $PAGE->set_pagelayout('course');
        $this->assertFalse(focusmode::enabled());
        $this->assertFalse(focusmode::active(), 'Disabled site-wide, whatever the preference');
    }

    /**
     * The banner renders on the course view page only, and reports the learner's progress.
     */
    public function test_course_banner(): void {
        global $PAGE, $CFG;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1, 'fullname' => 'Marine Biology']);
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $page = $generator->create_module(
            'page',
            ['course' => $course->id, 'name' => 'Reading'],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );

        $PAGE->set_course($course);
        $PAGE->set_pagelayout('course');
        $PAGE->set_pagetype('course-view-' . $course->format);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $this->assertTrue(course_banner::wanted());

        $PAGE->set_pagetype('course-edit');
        $this->assertFalse(course_banner::wanted(), 'Only the course view page');
        $PAGE->set_pagetype('course-view-' . $course->format);
        set_config('course_showbanner', 0, 'theme_atrium');
        $this->assertFalse(course_banner::wanted());
        set_config('course_showbanner', 1, 'theme_atrium');

        $output = $PAGE->get_renderer('core');
        $data = (new course_banner(get_course($course->id)))->export_for_template($output);
        $this->assertSame('Marine Biology', $data['fullname']);
        $this->assertFalse($data['hasprogress'], 'A visitor has no progress');
        $this->assertFalse($data['hasresume']);
        $this->assertSame('', $data['focusurl'], 'No focus switch for a visitor');

        $this->setUser($student);
        $data = (new course_banner(get_course($course->id)))->export_for_template($output);
        $this->assertTrue($data['hasprogress']);
        $this->assertSame(0, $data['progress']);
        $this->assertFalse($data['complete']);
        $this->assertNotSame('', $data['focusurl']);

        $completion = new \completion_info(get_course($course->id));
        $completion->update_state(get_coursemodule_from_instance('page', $page->id), COMPLETION_COMPLETE, $student->id);
        $data = (new course_banner(get_course($course->id)))->export_for_template($output);
        $this->assertSame(100, $data['progress']);
        $this->assertTrue($data['complete']);
    }
}
