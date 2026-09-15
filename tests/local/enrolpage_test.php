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

/**
 * Tests for the enrolment page data and rendering.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\enrolpage::class)]
final class enrolpage_test extends \advanced_testcase {
    /**
     * The outline lists visible sections and their visible, real activities; hidden ones
     * and labels are skipped; the general section only appears when it has content.
     */
    public function test_outline_and_facts(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $generator = $this->getDataGenerator();
        $start = strtotime('2026-01-05');
        $course = $generator->create_course([
            'numsections' => 3, 'enablecompletion' => 1, 'startdate' => $start, 'enddate' => $start + 4 * WEEKSECS,
        ]);
        $generator->create_module('page', ['course' => $course->id, 'name' => 'Welcome', 'section' => 1]);
        $generator->create_module('forum', ['course' => $course->id, 'name' => 'Discuss', 'section' => 1]);
        $generator->create_module('label', ['course' => $course->id, 'section' => 1]);
        $generator->create_module('quiz', ['course' => $course->id, 'name' => 'Secret quiz', 'section' => 2, 'visible' => 0]);
        $generator->create_module('assign', ['course' => $course->id, 'name' => 'Essay', 'section' => 3]);
        $DB->set_field('course_sections', 'visible', 0, ['course' => $course->id, 'section' => 3]);
        rebuild_course_cache($course->id, true);
        for ($i = 0; $i < 4; $i++) {
            $generator->enrol_user($generator->create_user()->id, $course->id, 'student');
        }
        $teacher = $generator->create_user([
            'firstname' => 'Grace', 'lastname' => 'Hopper', 'description' => '<p>Marine scientist.</p>',
        ]);
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($generator->create_user());

        $page = new enrolpage(get_course($course->id));
        $outline = $page->outline();
        $this->assertSame(2, $outline['sectioncount'], 'Section 1 and the empty but visible section 2; not the hidden 3');
        $this->assertSame(2, $outline['activitycount'], 'Welcome and Discuss; the label, hidden quiz and hidden section are out');
        $this->assertSame(['Welcome', 'Discuss'], array_column($outline['sections'][0]['activities'], 'name'));
        $this->assertSame(['page', 'forum'], array_column($outline['sections'][0]['activities'], 'modname'));
        $this->assertSame([], $outline['sections'][1]['activities']);
        $this->assertSame(0, $outline['more']);

        $facts = array_column($page->facts($outline), 'value', 'label');
        $this->assertSame('2', $facts['Sections']);
        $this->assertSame('2', $facts['Activities']);
        $this->assertSame('5', $facts['Learners'], 'Four students and the teacher have active enrolments');
        $this->assertSame('4 weeks', $facts['Duration']);
        $this->assertSame('Tracked', $facts['Completion']);
        $this->assertArrayHasKey(get_string('startdate'), $facts);

        $instructors = $page->instructors();
        $this->assertCount(1, $instructors);
        $this->assertSame('Grace Hopper', $instructors[0]['name']);
        $this->assertSame('Marine scientist.', $instructors[0]['bio']);
        $this->assertNotEmpty($instructors[0]['role']);
    }

    /**
     * Related courses are others in the same category, capped at three, never the course itself.
     */
    public function test_related(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $course = $generator->create_course(['category' => $category->id]);
        for ($i = 1; $i <= 4; $i++) {
            $generator->create_course(['category' => $category->id, 'fullname' => "Other $i"]);
        }
        $generator->create_course(['fullname' => 'Elsewhere']);
        $this->setUser($generator->create_user());

        $related = (new enrolpage(get_course($course->id)))->related();
        $this->assertCount(3, $related);
        foreach ($related as $other) {
            $this->assertNotEquals($course->id, $other->id);
            $this->assertStringStartsWith('Other', $other->fullname);
        }
    }

    /**
     * The renderer draws the landing page around core's enrolment widgets, and the
     * no-way-to-enrol message when there are none.
     */
    public function test_renderer(): void {
        global $PAGE, $OUTPUT;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['fullname' => 'Marine Biology', 'summary' => '<p>Life in the sea.</p>']);
        $generator->create_module('page', ['course' => $course->id, 'name' => 'Welcome', 'section' => 1]);
        $this->setUser($generator->create_user());
        $PAGE->set_course(get_course($course->id));
        $PAGE->set_url('/enrol/index.php', ['id' => $course->id]);
        $PAGE->force_theme('atrium');
        $OUTPUT = $PAGE->get_renderer('core');
        $renderer = $PAGE->get_renderer('core', 'course');

        $html = $renderer->enrolment_options(get_course($course->id), [5 => '<form class="fake-widget">Enrol</form>']);
        $this->assertStringContainsString('atrium-enrol', $html);
        $this->assertStringContainsString('Marine Biology', $html);
        $this->assertStringContainsString('Life in the sea.', $html);
        $this->assertStringContainsString('Welcome', $html);
        $this->assertStringContainsString('fake-widget', $html);
        $this->assertStringNotContainsString(get_string('notenrollable', 'enrol'), $html);

        $html = $renderer->enrolment_options(get_course($course->id), []);
        $this->assertStringContainsString(get_string('notenrollable', 'enrol'), $html);

        set_config('enrol_showoutline', 0, 'theme_atrium');
        $html = $renderer->enrolment_options(get_course($course->id), []);
        $this->assertStringNotContainsString('atrium-enrol-outline', $html);
    }
}
