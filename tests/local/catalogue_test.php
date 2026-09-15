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
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the course catalogue queries.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\catalogue::class)]
#[CoversClass(\theme_atrium\local\courses::class)]
#[CoversClass(\theme_atrium\output\core\course_renderer::class)]
final class catalogue_test extends \advanced_testcase {
    /**
     * Browsing sorts by name, by newest and by popularity, pages, and hides hidden courses
     * from those who may not see them.
     */
    public function test_browse(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $category = $generator->create_category(['name' => 'Science']);
        $child = $generator->create_category(['name' => 'Biology', 'parent' => $category->id]);
        $a = $generator->create_course(['fullname' => 'Algae', 'category' => $child->id]);
        $b = $generator->create_course(['fullname' => 'Botany', 'category' => $category->id]);
        $c = $generator->create_course(['fullname' => 'Chemistry', 'category' => $category->id]);
        $hidden = $generator->create_course(['fullname' => 'Hidden', 'category' => $category->id, 'visible' => 0]);
        $DB->set_field('course', 'timecreated', time() + 100, ['id' => $b->id]);
        for ($i = 0; $i < 3; $i++) {
            $generator->enrol_user($generator->create_user()->id, $c->id, 'student');
        }
        $generator->enrol_user($generator->create_user()->id, $a->id, 'student');
        $student = $generator->create_user();
        $this->setUser($student);

        $catalogue = new catalogue(core_course_category::get($category->id), '', 'name', 0, 12);
        $result = $catalogue->courses();
        $this->assertSame(3, $result['total']);
        $this->assertSame(['Algae', 'Botany', 'Chemistry'], $this->names($result['courses']));

        $catalogue = new catalogue(core_course_category::get($category->id), '', 'newest', 0, 12);
        $this->assertSame('Botany', $this->names($catalogue->courses()['courses'])[0]);

        $catalogue = new catalogue(core_course_category::get($category->id), '', 'popular', 0, 12);
        $this->assertSame(['Chemistry', 'Algae', 'Botany'], $this->names($catalogue->courses()['courses']));

        $catalogue = new catalogue(core_course_category::get($category->id), '', 'name', 1, 2);
        $result = $catalogue->courses();
        $this->assertSame(3, $result['total']);
        $this->assertSame(['Chemistry'], $this->names($result['courses']));

        $catalogue = new catalogue(core_course_category::get($child->id), '', 'name', 0, 12);
        $this->assertSame(['Algae'], $this->names($catalogue->courses()['courses']));
        $this->assertSame([], $catalogue->subcategories());

        $subcategories = (new catalogue(core_course_category::get($category->id), '', 'name', 0, 12))->subcategories();
        $this->assertSame('Biology', $subcategories[0]['name']);
        $this->assertSame(1, $subcategories[0]['count']);

        $this->setAdminUser();
        $catalogue = new catalogue(core_course_category::get($category->id), '', 'name', 0, 12);
        $this->assertSame(4, $catalogue->courses()['total'], 'An admin sees the hidden course');

        $this->setUser($student);
        $catalogue = new catalogue(core_course_category::user_top(), '', 'name', 0, 12);
        $this->assertSame(3, $catalogue->courses()['total'], 'All courses');
    }

    /**
     * Searching goes through core and pages.
     */
    public function test_search(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $generator->create_course(['fullname' => 'Marine biology', 'summary' => 'Sea life']);
        $generator->create_course(['fullname' => 'Cell biology']);
        $generator->create_course(['fullname' => 'Poetry']);
        $this->setUser($generator->create_user());

        $catalogue = new catalogue(core_course_category::user_top(), 'biology', 'name', 0, 12);
        $result = $catalogue->courses();
        $this->assertSame(2, $result['total']);
        $this->assertSame(['Cell biology', 'Marine biology'], $this->names($result['courses']));

        $catalogue = new catalogue(core_course_category::user_top(), 'biology', 'name', 1, 1);
        $this->assertSame(['Marine biology'], $this->names($catalogue->courses()['courses']));

        $catalogue = new catalogue(core_course_category::user_top(), 'nothingmatches', 'name', 0, 12);
        $this->assertSame(0, $catalogue->courses()['total']);
    }

    /**
     * Sort, view and per-page settings are validated; the view is remembered per user.
     */
    public function test_settings_and_view(): void {
        $this->resetAfterTest();
        $this->assertSame('name', catalogue::sort_or_default(null));
        $this->assertSame('popular', catalogue::sort_or_default('popular'));
        $this->assertSame('name', catalogue::sort_or_default('random'));
        set_config('catalogue_defaultsort', 'newest', 'theme_atrium');
        $this->assertSame('newest', catalogue::sort_or_default(null));
        $this->assertSame('newest', catalogue::sort_or_default('bogus'));

        $this->assertSame(12, catalogue::perpage_setting());
        set_config('catalogue_perpage', 999, 'theme_atrium');
        $this->assertSame(48, catalogue::perpage_setting());
        set_config('catalogue_perpage', 1, 'theme_atrium');
        $this->assertSame(6, catalogue::perpage_setting());

        $this->assertSame('grid', catalogue::view(null));
        $this->assertSame('list', catalogue::view('list'), 'A visitor may request a view');
        $this->assertSame('grid', catalogue::view(null), 'But it is not remembered for a visitor');

        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame('list', catalogue::view('list'));
        $this->assertSame('list', catalogue::view(null));
        $this->assertSame('list', get_user_preferences(catalogue::VIEW_PREFERENCE));
        $this->assertSame('list', catalogue::view('cube'));
    }

    /**
     * Prices come from enabled fee-style enrolment instances only.
     */
    public function test_prices(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $paid = $generator->create_course();
        $free = $generator->create_course();
        $disabled = $generator->create_course();
        $DB->insert_record('enrol', (object) ['enrol' => 'fee', 'courseid' => $paid->id, 'status' => ENROL_INSTANCE_ENABLED,
            'cost' => '49.50', 'currency' => 'EUR', 'sortorder' => 1]);
        $DB->insert_record('enrol', (object) ['enrol' => 'fee', 'courseid' => $disabled->id, 'status' => ENROL_INSTANCE_DISABLED,
            'cost' => '10', 'currency' => 'EUR', 'sortorder' => 1]);

        $prices = catalogue::prices([$paid->id, $free->id, $disabled->id]);
        $this->assertSame([(int) $paid->id], array_map('intval', array_keys($prices)));
        $this->assertStringContainsString('49.50', $prices[$paid->id]);
        $this->assertSame([], catalogue::prices([]));
    }

    /**
     * Cards count the course's visible activities, labels excluded, and the count can be hidden.
     */
    public function test_activity_count(): void {
        global $PAGE;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $generator->create_module('page', ['course' => $course->id]);
        $generator->create_module('forum', ['course' => $course->id]);
        $generator->create_module('label', ['course' => $course->id]);
        $generator->create_module('page', ['course' => $course->id, 'visible' => 0]);
        $this->setAdminUser();

        $this->assertSame(2, courses::activity_count($course->id));
        $output = $PAGE->get_renderer('core');
        $card = (new \theme_atrium\output\course_card(new \core_course_list_element($course)))->export_for_template($output);
        $this->assertTrue($card['showactivities']);
        $this->assertSame(2, $card['activitycount']);

        set_config('catalogue_showactivities', 0, 'theme_atrium');
        $card = (new \theme_atrium\output\course_card(new \core_course_list_element($course)))->export_for_template($output);
        $this->assertFalse($card['showactivities']);
        $this->assertNull($card['activitycount']);
    }

    /**
     * The renderer draws the catalogue with cards and the toolbar for course/index.php.
     */
    public function test_renderer(): void {
        global $PAGE, $OUTPUT;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $category = $generator->create_category(['name' => 'Arts']);
        $generator->create_course(['fullname' => 'Sculpture', 'category' => $category->id]);
        $this->setUser($generator->create_user());
        $PAGE->force_theme('atrium');
        $PAGE->set_url('/course/index.php');
        $PAGE->set_context(\context_system::instance());
        $OUTPUT = $PAGE->get_renderer('core');
        $renderer = $PAGE->get_renderer('core', 'course');
        $this->assertInstanceOf(\theme_atrium\output\core\course_renderer::class, $renderer);

        $html = $renderer->course_category($category->id);
        $this->assertStringContainsString('atrium-catalogue', $html);
        $this->assertStringContainsString('Sculpture', $html);
        $this->assertStringContainsString('atrium-course-grid', $html);
        $this->assertStringContainsString('1 course', $html);

        $html = $renderer->search_courses(['search' => 'sculpt']);
        $this->assertStringContainsString('Sculpture', $html);
        $html = $renderer->search_courses(['search' => 'zzz']);
        $this->assertStringContainsString('atrium-empty', $html);
    }

    /**
     * Names of a list of courses.
     *
     * @param \core_course_list_element[] $courses
     * @return string[]
     */
    private function names(array $courses): array {
        return array_map(fn($c) => $c->fullname, $courses);
    }
}
