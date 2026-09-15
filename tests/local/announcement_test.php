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
 * Tests for the announcement bar, quick links and the profile renderer.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\announcement::class)]
#[CoversClass(\theme_atrium\local\quicklinks::class)]
#[CoversClass(\theme_atrium\output\core_user\myprofile\renderer::class)]
final class announcement_test extends \advanced_testcase {
    /**
     * The bar shows when enabled with text, is dismissed per text, and reappears when the text changes.
     */
    public function test_announcement(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertFalse(announcement::enabled(), 'Off by default');
        set_config('announcement_enable', 1, 'theme_atrium');
        $this->assertFalse(announcement::enabled(), 'No text yet');
        set_config('announcement_text', '<p>Exams start Monday.</p>', 'theme_atrium');
        $this->assertTrue(announcement::enabled());
        $this->assertTrue(announcement::wanted());

        $export = announcement::export();
        $this->assertStringContainsString('Exams start Monday.', $export['text']);
        $this->assertSame('info', $export['type'], 'Unknown or unset tone falls back to info');
        $this->assertTrue($export['dismissible']);
        $this->assertSame(announcement::hash(), $export['hash']);

        set_config('announcement_type', 'warning', 'theme_atrium');
        $this->assertSame('warning', announcement::export()['type']);
        set_config('announcement_type', 'purple', 'theme_atrium');
        $this->assertSame('info', announcement::export()['type']);

        announcement::dismiss();
        $this->assertTrue(announcement::dismissed());
        $this->assertFalse(announcement::wanted());

        set_config('announcement_text', '<p>Exams start Tuesday.</p>', 'theme_atrium');
        $this->assertFalse(announcement::dismissed(), 'New text, new announcement');
        $this->assertTrue(announcement::wanted());

        set_config('announcement_dismissible', 0, 'theme_atrium');
        $this->assertFalse(announcement::export()['dismissible']);

        $this->setGuestUser();
        $this->assertTrue(announcement::wanted(), 'Guests see it');
        $this->assertFalse(announcement::export()['dismissible'], 'But cannot dismiss it');
    }

    /**
     * Quick links parse "icon|Label|URL|newtab" lines, skipping malformed ones.
     */
    public function test_quicklinks(): void {
        $this->resetAfterTest();
        $links = quicklinks::parse(
            "fa-book|Library|/library\n" .
            "fa-headset | Help desk | https://help.example.com | newtab\n" .
            "not-an-icon|Calendar|/calendar/view.php\n" .
            "fa-x|No url|\n" .
            "fa-x|Bad scheme|javascript:alert(1)\n" .
            "just text\n" .
            "|Empty icon|/somewhere"
        );
        $this->assertSame(['Library', 'Help desk', 'Calendar', 'Empty icon'], array_column($links, 'label'));
        $this->assertSame('fa-book', $links[0]['icon']);
        $this->assertFalse($links[0]['newtab']);
        $this->assertTrue($links[1]['newtab']);
        $this->assertSame('fa-link', $links[2]['icon'], 'Unknown icon falls back');
        $this->assertSame('fa-link', $links[3]['icon']);

        $this->assertFalse(quicklinks::export(), 'Nothing configured');
        set_config('quicklinks', "fa-book|Library|/library", 'theme_atrium');
        $export = quicklinks::export();
        $this->assertCount(1, $export['links']);
        $this->assertStringEndsWith('/library', $export['links'][0]['url']);

        $many = implode("\n", array_map(fn($i) => "fa-book|Link $i|/p$i", range(1, 20)));
        $this->assertCount(quicklinks::MAX, quicklinks::parse($many));
    }

    /**
     * The profile renderer wraps core's tree in cards, one per category, with core's nodes inside.
     */
    public function test_profile_renderer(): void {
        global $CFG, $PAGE, $OUTPUT;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Ada', 'lastname' => 'Lovelace']);
        $this->setUser($user);
        $PAGE->set_url('/user/profile.php', ['id' => $user->id]);
        $PAGE->set_context(\context_user::instance($user->id));
        $PAGE->force_theme('atrium');
        $OUTPUT = $PAGE->get_renderer('core');
        $renderer = $PAGE->get_renderer('core_user', 'myprofile');
        $this->assertInstanceOf(\theme_atrium\output\core_user\myprofile\renderer::class, $renderer);

        $tree = \core_user\output\myprofile\manager::build_tree($user, true);
        $html = $renderer->render($tree);
        $this->assertStringContainsString('atrium-profile-grid', $html);
        $this->assertStringContainsString('atrium-profile-card-contact', $html);
        $this->assertStringContainsString('fa-address-card', $html);
        $this->assertStringContainsString(get_string('userdetails'), $html);
        $this->assertStringContainsString('contentnode', $html, "Core's node markup is kept");
    }
}
