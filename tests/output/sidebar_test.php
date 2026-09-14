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

/**
 * Tests for the sidebar renderable.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\output\sidebar::class)]
final class sidebar_test extends \advanced_testcase {
    /**
     * The initial state: the user's own choice, else the site setting, else expanded.
     */
    public function test_starts_collapsed(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $this->assertFalse(sidebar::starts_collapsed());
        set_config('sidebardefault', sidebar::COLLAPSED, 'theme_atrium');
        $this->assertTrue(sidebar::starts_collapsed());

        $this->setUser($user);
        $this->assertTrue(sidebar::starts_collapsed(), 'No preference yet: the site default');
        set_user_preference(sidebar::PREFERENCE, sidebar::EXPANDED);
        $this->assertFalse(sidebar::starts_collapsed());
        set_user_preference(sidebar::PREFERENCE, sidebar::COLLAPSED);
        $this->assertTrue(sidebar::starts_collapsed());
        set_user_preference(sidebar::PREFERENCE, 'garbage');
        $this->assertTrue(sidebar::starts_collapsed(), 'An unknown value falls back to the site default');

        $this->setGuestUser();
        set_config('sidebardefault', sidebar::EXPANDED, 'theme_atrium');
        $this->assertFalse(sidebar::starts_collapsed());
    }

    /**
     * Core nodes get their icons by key; custom menu entries get the link icon; children carry through.
     */
    public function test_export(): void {
        global $PAGE;
        $this->resetAfterTest();
        $output = $PAGE->get_renderer('core');
        $nodes = [
            ['key' => 'home', 'text' => 'Home', 'url' => new \moodle_url('/'), 'isactive' => false],
            ['key' => 'myhome', 'text' => 'Dashboard', 'url' => new \moodle_url('/my/'), 'isactive' => true],
            ['key' => 'siteadminnode', 'text' => 'Site administration', 'url' => '/admin/search.php', 'isactive' => false],
            ['text' => 'Help', 'url' => '#', 'title' => 'Help desk', 'haschildren' => 1, 'children' => [
                ['text' => 'Guides', 'url' => 'https://example.com/guides', 'isactive' => false],
                ['divider' => true],
                ['text' => 'Contact', 'url' => 'https://example.com/contact', 'isactive' => true],
            ]],
        ];

        $data = (new sidebar($nodes, true))->export_for_template($output);
        $this->assertTrue($data['collapsed']);
        $this->assertTrue($data['hasitems']);
        $this->assertCount(4, $data['items']);

        [$home, $dashboard, $admin, $help] = $data['items'];
        $this->assertSame(['i/home', 'core'], [$home['iconkey'], $home['iconcomponent']]);
        $this->assertSame(['i/dashboard', 'core'], [$dashboard['iconkey'], $dashboard['iconcomponent']]);
        $this->assertTrue($dashboard['isactive']);
        $this->assertSame(['i/settings', 'core'], [$admin['iconkey'], $admin['iconcomponent']]);
        $this->assertSame('atrium-nav-siteadminnode', $admin['id']);

        $this->assertSame(['link', 'theme_atrium'], [$help['iconkey'], $help['iconcomponent']]);
        $this->assertSame('Help desk', $help['title']);
        $this->assertTrue($help['haschildren']);
        $this->assertCount(2, $help['children'], 'Dividers are dropped');
        $this->assertSame('Contact', $help['children'][1]['text']);
        $this->assertTrue($help['children'][1]['isactive']);
        $this->assertStringStartsWith('atrium-nav-', $help['id']);
        $this->assertSame('https://www.example.com/moodle/my/', $dashboard['url']);

        $empty = (new sidebar([], false))->export_for_template($output);
        $this->assertFalse($empty['hasitems']);
        $this->assertFalse($empty['collapsed']);
    }
}
