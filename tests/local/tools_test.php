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
 * Tests for the accessibility toolbar, the command palette and quick start.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\accessibility::class)]
#[CoversClass(\theme_atrium\local\palette::class)]
#[CoversClass(\theme_atrium\local\quickstart::class)]
final class tools_test extends \advanced_testcase {
    /**
     * Choices are saved per user, become body classes, and reset cleanly.
     */
    public function test_accessibility(): void {
        global $PAGE;
        $this->resetAfterTest();
        $PAGE->set_url('/');

        $this->assertFalse(accessibility::wanted(), 'Visitors have no toolbar');
        $this->assertSame([], accessibility::body_classes());

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertTrue(accessibility::wanted());
        $this->assertSame(
            ['textsize' => 'normal', 'font' => false, 'contrast' => false, 'motion' => false],
            accessibility::state()
        );

        accessibility::save(['textsize' => 'huge', 'font' => 1, 'contrast' => '1', 'motion' => 0]);
        $this->assertSame('normal', accessibility::state()['textsize'], 'Unknown sizes are ignored');
        $this->assertSame(['atrium-font-reading', 'atrium-contrast'], accessibility::body_classes());

        accessibility::save(['textsize' => 'larger', 'motion' => 1]);
        $this->assertSame(
            ['atrium-text-larger', 'atrium-font-reading', 'atrium-contrast', 'atrium-reduce-motion'],
            accessibility::body_classes()
        );
        $export = accessibility::export();
        $this->assertTrue($export['motion']);
        $this->assertSame('larger', array_values(array_filter($export['sizes'], fn($s) => $s['checked']))[0]['value']);

        accessibility::reset();
        $this->assertSame([], accessibility::body_classes());

        set_config('navbar_a11y', 0, 'theme_atrium');
        $this->assertFalse(accessibility::wanted());
    }

    /**
     * The palette finds pages, the current course's visible activities and courses, with
     * the caller's permissions; admin pages only for site administrators.
     */
    public function test_palette(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['fullname' => 'Marine Biology']);
        $other = $generator->create_course(['fullname' => 'Organic Chemistry']);
        $hidden = $generator->create_course(['fullname' => 'Secret Biology', 'visible' => 0]);
        $generator->create_module('quiz', ['course' => $course->id, 'name' => 'Plankton quiz']);
        $generator->create_module('page', ['course' => $course->id, 'name' => 'Hidden reading', 'visible' => 0]);
        $generator->create_module('label', ['course' => $course->id, 'name' => 'A label']);
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);

        $this->assertTrue(palette::wanted());
        $groups = palette::search('', $course->id);
        $titles = array_column($groups, 'title');
        $this->assertSame([
            get_string('palette_pages', 'theme_atrium'),
            get_string('palette_activities', 'theme_atrium'),
            get_string('palette_courses', 'theme_atrium'),
        ], $titles);
        $activities = array_column($groups[1]['items'], 'label');
        $this->assertContains('Plankton quiz', $activities);
        $this->assertNotContains('Hidden reading', $activities, 'Hidden activities are not shown to students');
        $this->assertNotContains('A label', $activities);
        $this->assertSame(['Marine Biology'], array_column($groups[2]['items'], 'label'), 'Own courses when the query is empty');

        $groups = palette::search('biology');
        $courses = array_column(end($groups)['items'], 'label');
        $this->assertContains('Marine Biology', $courses);
        $this->assertNotContains('Secret Biology', $courses, 'Hidden courses are not shown');
        $this->assertNotContains('Organic Chemistry', $courses);
        foreach ($groups as $group) {
            $this->assertNotSame(get_string('palette_admin', 'theme_atrium'), $group['title'], 'No admin pages for a student');
        }

        $groups = palette::search('dash');
        $this->assertSame(get_string('myhome'), $groups[0]['items'][0]['label']);

        $this->setAdminUser();
        $groups = palette::search('theme');
        $admin = end($groups);
        $this->assertSame(get_string('palette_admin', 'theme_atrium'), $admin['title']);
        $this->assertNotEmpty($admin['items']);
        $this->assertLessThanOrEqual(palette::LIMIT, count($admin['items']));
        $this->assertNotEmpty(palette::admin_pages());

        set_config('navbar_palette', 0, 'theme_atrium');
        $this->assertFalse(palette::enabled());
    }

    /**
     * Quick start writes a coherent set of settings from the site's own details, and
     * reset clears exactly those.
     */
    public function test_quickstart(): void {
        global $SITE, $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $DB->set_field('course', 'summary', '<p>A place for marine science.</p>', ['id' => SITEID]);
        $SITE = $DB->get_record('course', ['id' => SITEID]);
        $CFG->supportemail = 'help@example.com';

        $written = quickstart::apply();
        $this->assertSame(quickstart::settings(), $written);
        $this->assertStringContainsString('Welcome to', get_config('theme_atrium', 'fp_hero_heading'));
        $this->assertSame('A place for marine science.', get_config('theme_atrium', 'fp_hero_subheading'));
        $this->assertSame('1', get_config('theme_atrium', 'fp_about_enable'));
        $this->assertSame('4', get_config('theme_atrium', 'footercolumns'));
        $this->assertSame('help@example.com', get_config('theme_atrium', 'footercontactemail'));
        $this->assertSame('panelleft', get_config('theme_atrium', 'loginlayout'));
        $this->assertStringContainsString('/course/index.php', get_config('theme_atrium', 'quicklinks'));
        $this->assertCount(2, frontpage_settings::hero_slides());

        quickstart::reset();
        $this->assertFalse(get_config('theme_atrium', 'fp_hero_heading'));
        $this->assertFalse(get_config('theme_atrium', 'footercolumns'));
        $this->assertSame([], frontpage_settings::hero_slides());
    }
}
