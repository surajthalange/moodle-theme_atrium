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
 * Tests for the header and login page settings models.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\header::class)]
#[CoversClass(\theme_atrium\local\loginpage::class)]
#[CoversClass(\theme_atrium\local\recentcourses::class)]
final class header_test extends \advanced_testcase {
    /**
     * Height, stickiness and the brand follow their settings, with safe defaults.
     */
    public function test_header(): void {
        global $PAGE;
        $this->resetAfterTest();
        $output = $PAGE->get_renderer('core');

        $this->assertSame(56, header::height());
        $this->assertTrue(header::sticky());
        $this->assertSame('standard', header::pagewidth());
        $this->assertSame(['atrium-width-standard'], header::body_classes());
        $this->assertSame('both', header::brandstyle());

        set_config('navbarheight', 'compact', 'theme_atrium');
        set_config('navbarsticky', 0, 'theme_atrium');
        set_config('brandstyle', 'huge', 'theme_atrium');
        set_config('pagewidth', 'wide', 'theme_atrium');
        $this->assertSame(48, header::height());
        $this->assertFalse(header::sticky());
        $this->assertSame(['atrium-width-wide', 'atrium-navbar-static'], header::body_classes());
        set_config('pagewidth', 'huge', 'theme_atrium');
        $this->assertSame('standard', header::pagewidth(), 'Unknown widths fall back');
        $this->assertSame('both', header::brandstyle(), 'Unknown styles fall back');

        // No logo on the site: the name always shows.
        set_config('brandstyle', 'logo', 'theme_atrium');
        $brand = header::brand($output);
        $this->assertFalse($brand['haslogo']);
        $this->assertTrue($brand['showname']);
        $this->assertSame('', $brand['logourl']);
        $this->assertNotSame('', $brand['sitename']);

        // With a logo: "logo" hides the name, "name" hides the logo, "both" shows both.
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => \context_system::instance()->id,
            'component' => 'core_admin',
            'filearea' => 'logocompact',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'logo.png',
        ], file_get_contents(__DIR__ . '/../../pix/screenshot.png'));
        set_config('logocompact', '/logo.png', 'core_admin');

        $brand = header::brand($output);
        $this->assertTrue($brand['haslogo']);
        $this->assertFalse($brand['showname']);
        $this->assertStringContainsString('logo.png', $brand['logourl']);

        set_config('brandstyle', 'name', 'theme_atrium');
        $brand = header::brand($output);
        $this->assertFalse($brand['haslogo']);
        $this->assertTrue($brand['showname']);

        set_config('brandstyle', 'both', 'theme_atrium');
        $brand = header::brand($output);
        $this->assertTrue($brand['haslogo']);
        $this->assertTrue($brand['showname']);
    }

    /**
     * The recent courses menu lists what the user opened, newest first, and can be turned off.
     */
    public function test_recentcourses(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/course/lib.php');
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $first = $generator->create_course(['fullname' => 'First']);
        $second = $generator->create_course(['fullname' => 'Second']);
        $generator->enrol_user($user->id, $first->id, 'student');
        $generator->enrol_user($user->id, $second->id, 'student');

        $this->assertFalse(recentcourses::export(), 'Visitors have no menu');
        $this->setUser($user);
        $this->assertFalse(recentcourses::export(), 'Nothing opened yet');

        $DB->insert_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $first->id, 'timeaccess' => time() - 100]);
        $DB->insert_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $second->id, 'timeaccess' => time()]);
        $menu = recentcourses::export();
        $this->assertSame(['Second', 'First'], array_column($menu['courses'], 'name'));
        $this->assertStringContainsString('/my/courses.php', $menu['allurl']);
        $this->assertStringContainsString('atrium-gradient-', $menu['courses'][0]['gradient']);

        set_config('navbar_recentcourses', 0, 'theme_atrium');
        $this->assertFalse(recentcourses::enabled());
        $this->assertFalse(recentcourses::export());
    }

    /**
     * The login layout, its switches and its copy.
     */
    public function test_loginpage(): void {
        $this->resetAfterTest();

        $this->assertSame('centred', loginpage::layout());
        $this->assertFalse(loginpage::panel());
        $this->assertSame(['atrium-login-centred'], loginpage::body_classes());
        $export = loginpage::export();
        $this->assertFalse($export['panel']);
        $this->assertSame('', $export['textabove']);
        $this->assertSame('', $export['paneltext']);

        set_config('loginlayout', 'panelright', 'theme_atrium');
        set_config('loginshowlangmenu', 0, 'theme_atrium');
        set_config('loginsignupbutton', 1, 'theme_atrium');
        set_config('loginpanelheading', 'Hello <b>there</b>', 'theme_atrium');
        set_config('loginpaneltext', '<p>Panel</p>', 'theme_atrium');
        set_config('logintextabove', '<p></p>', 'theme_atrium');
        set_config('logintextbelow', '<p>Call us</p>', 'theme_atrium');

        $this->assertSame('panelright', loginpage::layout());
        $this->assertTrue(loginpage::panel());
        $this->assertSame(
            ['atrium-login-panelright', 'atrium-login-nolangmenu', 'atrium-login-signupbutton'],
            loginpage::body_classes()
        );
        $export = loginpage::export();
        $this->assertTrue($export['panel']);
        $this->assertStringNotContainsString('<b>', $export['panelheading']);
        $this->assertStringContainsString('Panel', $export['paneltext']);
        $this->assertSame('', $export['textabove'], 'Empty markup counts as no text');
        $this->assertStringContainsString('Call us', $export['textbelow']);

        set_config('loginlayout', 'sideways', 'theme_atrium');
        $this->assertSame('centred', loginpage::layout());
    }
}
