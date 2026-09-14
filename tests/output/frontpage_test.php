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

use PHPUnit\Framework\Attributes\CoversClass;
use theme_atrium\local\courses;

/**
 * Tests for the front page renderable and the course queries behind it.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\output\frontpage::class)]
#[CoversClass(\theme_atrium\output\course_card::class)]
#[CoversClass(\theme_atrium\local\courses::class)]
final class frontpage_test extends \advanced_testcase {
    /**
     * The default export has the default sections, the showcase lists visible courses newest
     * first, the stats resolve to real counts, and hidden courses never reach a visitor.
     */
    public function test_export_defaults(): void {
        global $PAGE;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $old = $generator->create_course(['fullname' => 'Old course']);
        $new = $generator->create_course(['fullname' => 'New course']);
        $hidden = $generator->create_course(['fullname' => 'Hidden course', 'visible' => 0]);
        // Give the new course a later creation time than the generator's same-second stamps.
        $this->set_created($new->id, time() + 10);
        $this->set_created($hidden->id, time() + 20);
        $output = $PAGE->get_renderer('core');

        $data = (new frontpage())->export_for_template($output);
        $this->assertSame(['hero', 'features', 'showcase', 'stats', 'cta'], $data['sections']);
        $this->assertTrue($data['hassections']);
        $this->assertFalse($data['transparentnavbar']);
        $this->assertStringContainsString('Welcome to', $data['hero']['heading']);
        $this->assertSame('Browse courses', $data['hero']['button1']['text']);
        $this->assertFalse($data['hero']['button2']);
        $this->assertCount(3, $data['features']['items']);
        $this->assertSame(3, $data['features']['columns']);

        $names = array_column($data['showcase']['cards'], 'fullname');
        $this->assertSame(['New course', 'Old course'], $names, 'Newest first, hidden course absent for a visitor');
        $this->assertStringContainsString('atrium-gradient-', $data['showcase']['cards'][0]['gradient']);
        $this->assertFalse($data['showcase']['cards'][0]['enrolled']);

        $stats = array_column($data['stats']['items'], 'value', 'label');
        $this->assertSame('2', $stats['Courses']);
        $this->assertSame('1', $stats['Categories']);
        $this->assertSame('0', $stats['Completions']);
        $this->assertSame('accent', $data['cta']['background']);
        $this->assertSame('Log in', $data['cta']['button']['text']);
    }

    /**
     * Sections switch off cleanly and the showcase sources behave.
     */
    public function test_sources_and_switches(): void {
        global $PAGE;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $incat = $generator->create_course(['category' => $category->id, 'fullname' => 'In category']);
        $other = $generator->create_course(['fullname' => 'Elsewhere']);
        $output = $PAGE->get_renderer('core');
        // A signed-in learner: PHPUnit's not-logged-in user carries no category capabilities.
        $this->setUser($generator->create_user());

        set_config('fp_showcase_source', 'category', 'theme_atrium');
        set_config('fp_showcase_category', $category->id, 'theme_atrium');
        $data = (new frontpage())->export_for_template($output);
        $this->assertSame(['In category'], array_column($data['showcase']['cards'], 'fullname'));
        $this->assertStringContainsString('categoryid=' . $category->id, $data['showcase']['browseurl']);

        set_config('fp_showcase_source', 'ids', 'theme_atrium');
        set_config('fp_showcase_ids', $other->id . ',' . $incat->id . ',999999', 'theme_atrium');
        $data = (new frontpage())->export_for_template($output);
        $this->assertSame(['Elsewhere', 'In category'], array_column($data['showcase']['cards'], 'fullname'));

        set_config('fp_showcase_ids', '999999', 'theme_atrium');
        $data = (new frontpage())->export_for_template($output);
        $this->assertNotContains('showcase', $data['sections'], 'An empty showcase is dropped');

        set_config('fp_enable', 0, 'theme_atrium');
        $data = (new frontpage())->export_for_template($output);
        $this->assertSame([], $data['sections']);
        $this->assertFalse($data['hassections']);
    }

    /**
     * The card knows the viewer's enrolment and progress, and teachers appear as contacts.
     */
    public function test_course_card(): void {
        global $PAGE, $CFG;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $teacher = $generator->create_user(['firstname' => 'Grace', 'lastname' => 'Hopper']);
        $student = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($student->id, $course->id, 'student');
        $output = $PAGE->get_renderer('core');
        $element = new \core_course_list_element(get_course($course->id));

        $data = (new course_card($element))->export_for_template($output);
        $this->assertSame('Grace Hopper', $data['firstcontactname']);
        $this->assertSame(2, $data['enrolledcount']);
        $this->assertFalse($data['enrolled']);
        $this->assertFalse($data['hasprogress']);

        $this->setUser($student);
        $data = (new course_card($element))->export_for_template($output);
        $this->assertTrue($data['enrolled']);
        $this->assertFalse($data['hasprogress'], 'Nothing to complete yet');

        $generator->create_module('assign', ['course' => $course->id], ['completion' => COMPLETION_TRACKING_MANUAL]);
        $data = (new course_card($element))->export_for_template($output);
        $this->assertTrue($data['hasprogress']);
        $this->assertSame(0, $data['progress']);

        $data = (new course_card($element, false, false))->export_for_template($output);
        $this->assertFalse($data['showenrolled']);
        $this->assertFalse($data['hasprogress']);
    }

    /**
     * Site counts exclude the site course and the guest user, and are cached.
     */
    public function test_site_counts(): void {
        $this->resetAfterTest();
        $before = courses::site_counts();
        $this->assertSame(0, $before['courses']);
        $this->getDataGenerator()->create_course();
        $this->assertSame(0, courses::site_counts()['courses'], 'Cached for ten minutes');
        \cache::make('theme_atrium', 'sitecounts')->purge();
        $this->assertSame(1, courses::site_counts()['courses']);
        $this->assertSame(1, courses::site_counts()['users'], 'The guest account is excluded; the admin counts');
    }

    /**
     * Set a course's creation time directly.
     *
     * @param int $courseid
     * @param int $time
     */
    private function set_created(int $courseid, int $time): void {
        global $DB;
        $DB->set_field('course', 'timecreated', $time, ['id' => $courseid]);
    }
}
