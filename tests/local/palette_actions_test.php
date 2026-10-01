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
 * Tests for the commands the palette can run.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\palette_actions::class)]
final class palette_actions_test extends \advanced_testcase {
    /**
     * A command changes the thing it names.
     */
    public function test_execute_changes_the_preference(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertSame(scheme::LIGHT, scheme::resolve());
        $this->assertTrue(palette_actions::execute('scheme_dark'));
        $this->assertSame(scheme::DARK, scheme::resolve());

        $this->assertTrue(palette_actions::execute('a11y_text_large'));
        $this->assertSame('large', accessibility::state()['textsize']);

        $this->assertTrue(palette_actions::execute('a11y_contrast'));
        $this->assertTrue(accessibility::state()['contrast']);
        // The same command again is a toggle, not a repeat.
        $this->assertTrue(palette_actions::execute('a11y_contrast'));
        $this->assertFalse(accessibility::state()['contrast']);

        $this->assertTrue(palette_actions::execute('a11y_reset'));
        $this->assertSame('normal', accessibility::state()['textsize']);
    }

    /**
     * An unknown command does nothing rather than erroring.
     */
    public function test_unknown_command_is_refused(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertFalse(palette_actions::execute('definitely_not_a_command'));
        $this->assertFalse(palette_actions::execute(''));
    }

    /**
     * Purging caches is for site administrators, and the check is at the point it runs,
     * not only where the list is built.
     */
    public function test_purge_caches_needs_an_administrator(): void {
        $this->resetAfterTest();

        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertFalse(palette_actions::execute('purgecaches'));

        $this->setAdminUser();
        $this->assertTrue(palette_actions::execute('purgecaches'));
    }

    /**
     * Guests and logged-out visitors get no commands at all.
     */
    public function test_nothing_runs_without_a_real_user(): void {
        $this->resetAfterTest();

        $this->setGuestUser();
        $this->assertFalse(palette_actions::execute('scheme_dark'));
        $this->assertSame([], palette_actions::available());

        $this->setUser(null);
        $this->assertFalse(palette_actions::execute('scheme_dark'));
    }

    /**
     * The offered list follows the current state: once it is dark, only light is offered.
     */
    public function test_available_follows_state(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $ids = array_column(palette_actions::available(), 'id');
        $this->assertContains('scheme_dark', $ids);
        $this->assertNotContains('scheme_light', $ids);

        scheme::set(scheme::DARK);
        $ids = array_column(palette_actions::available(), 'id');
        $this->assertContains('scheme_light', $ids);
        $this->assertNotContains('scheme_dark', $ids);
    }

    /**
     * Searching matches on the visible label, and an empty query stays short so that
     * commands never crowd out the places a user is more likely to want.
     */
    public function test_search_matches_labels(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertLessThanOrEqual(3, count(palette_actions::search('')));

        $labels = array_column(palette_actions::search('contrast'), 'label');
        $this->assertNotEmpty($labels);
        foreach ($labels as $label) {
            $this->assertStringContainsStringIgnoringCase('contrast', $label);
        }

        $this->assertSame([], palette_actions::search('zzzzzzzz'));
    }

    /**
     * A command carries an action id and no URL; a link carries a URL and no action id.
     * The front end relies on that to decide between posting and navigating.
     */
    public function test_commands_post_and_links_navigate(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        foreach (palette_actions::search('purge') as $item) {
            $this->assertSame('purgecaches', $item['action']);
            $this->assertSame('', $item['url']);
        }
        foreach (palette_actions::search('log out') as $item) {
            $this->assertSame('', $item['action']);
            $this->assertNotSame('', $item['url']);
        }
    }
}
