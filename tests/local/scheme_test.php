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
 * Tests for colour scheme resolution.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\scheme::class)]
final class scheme_test extends \advanced_testcase {
    /**
     * Order of precedence: the user's preference, then the site default, then light.
     */
    public function test_resolve_precedence(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $this->assertTrue(scheme::enabled());
        $this->assertSame(scheme::LIGHT, scheme::resolve());
        $this->assertSame(scheme::LIGHT, scheme::initial());

        set_config('defaultscheme', scheme::DARK, 'theme_atrium');
        $this->assertSame(scheme::DARK, scheme::resolve());
        $this->assertSame(scheme::DARK, scheme::initial());

        set_config('defaultscheme', scheme::SYSTEM, 'theme_atrium');
        $this->assertSame(scheme::SYSTEM, scheme::resolve());
        $this->assertSame(scheme::LIGHT, scheme::initial(), 'System never reaches the html attribute as such');

        $this->setUser($user);
        scheme::set(scheme::DARK);
        $this->assertSame(scheme::DARK, scheme::resolve());
        $this->assertSame(scheme::DARK, get_user_preferences(scheme::PREFERENCE));

        scheme::set(scheme::LIGHT);
        $this->assertSame(scheme::LIGHT, scheme::resolve());

        set_config('defaultscheme', 'nonsense', 'theme_atrium');
        unset_user_preference(scheme::PREFERENCE);
        $this->assertSame(scheme::LIGHT, scheme::resolve());
    }

    /**
     * Disabling dark mode forces light for everyone but keeps the saved preference.
     */
    public function test_disabled_forces_light_and_keeps_preference(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        scheme::set(scheme::DARK);

        set_config('enabledarkmode', 0, 'theme_atrium');
        $this->assertFalse(scheme::enabled());
        $this->assertSame(scheme::LIGHT, scheme::resolve());
        $this->assertFalse(scheme::can_toggle());
        $this->assertSame(scheme::DARK, get_user_preferences(scheme::PREFERENCE));

        set_config('enabledarkmode', 1, 'theme_atrium');
        $this->assertSame(scheme::DARK, scheme::resolve());
        $this->assertTrue(scheme::can_toggle());
    }

    /**
     * Guests and visitors get the default and no toggle.
     */
    public function test_guest_and_anonymous(): void {
        $this->resetAfterTest();
        set_config('defaultscheme', scheme::DARK, 'theme_atrium');

        $this->setGuestUser();
        $this->assertSame(scheme::DARK, scheme::resolve());
        $this->assertFalse(scheme::can_toggle());

        $this->setUser(null);
        $this->assertSame(scheme::DARK, scheme::resolve());
        $this->assertFalse(scheme::can_toggle());
    }

    /**
     * The no-JavaScript toggle URL flips to the other scheme and returns to the page.
     */
    public function test_toggle_url(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $PAGE->set_url(new \moodle_url('/course/view.php', ['id' => 3]));

        $url = scheme::toggle_url();
        $this->assertSame(scheme::DARK, $url->get_param('scheme'));
        $this->assertSame('/course/view.php?id=3', $url->get_param('returnurl'));
        $this->assertSame(sesskey(), $url->get_param('sesskey'));

        scheme::set(scheme::DARK);
        $this->assertSame(scheme::LIGHT, scheme::toggle_url()->get_param('scheme'));
    }

    /**
     * Only the three named schemes are accepted.
     */
    public function test_set_rejects_unknown(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertTrue(scheme::valid(scheme::SYSTEM));
        $this->assertFalse(scheme::valid('auto'));
        $this->assertFalse(scheme::valid(null));
        $this->expectException(\invalid_parameter_exception::class);
        scheme::set('auto');
    }
}
