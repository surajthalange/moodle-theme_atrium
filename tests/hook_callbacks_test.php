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

namespace theme_atrium;

use core\hook\output\before_html_attributes;
use core\hook\output\before_standard_head_html_generation;
use core_user\hook\extend_user_menu;
use theme_atrium\local\scheme;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the hook callbacks.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\hook_callbacks::class)]
final class hook_callbacks_test extends \advanced_testcase {
    /**
     * Render with this theme active on the page.
     *
     * @return \renderer_base
     */
    private function use_atrium(): \renderer_base {
        global $PAGE;
        $PAGE->force_theme('atrium');
        $PAGE->set_url('/');
        return $PAGE->get_renderer('core');
    }

    /**
     * The html element carries the scheme, and nothing is added when another theme renders the page.
     */
    public function test_html_attributes(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $renderer = $this->use_atrium();

        $hook = new before_html_attributes($renderer);
        hook_callbacks::before_html_attributes($hook);
        $this->assertSame('light', $hook->get_attributes()['data-bs-theme']);
        $this->assertSame('light', $hook->get_attributes()['data-atrium-scheme']);

        scheme::set(scheme::DARK);
        $hook = new before_html_attributes($renderer);
        hook_callbacks::before_html_attributes($hook);
        $this->assertSame('dark', $hook->get_attributes()['data-bs-theme']);

        set_config('defaultscheme', scheme::SYSTEM, 'theme_atrium');
        unset_user_preference(scheme::PREFERENCE);
        $hook = new before_html_attributes($renderer);
        hook_callbacks::before_html_attributes($hook);
        $this->assertSame('light', $hook->get_attributes()['data-bs-theme']);
        $this->assertSame('system', $hook->get_attributes()['data-atrium-scheme']);

        // Another theme rendering a page: nothing is added.
        $atriumpage = $PAGE;
        $PAGE = new \moodle_page();
        $PAGE->force_theme('boost');
        $hook = new before_html_attributes($PAGE->get_renderer('core'));
        hook_callbacks::before_html_attributes($hook);
        $this->assertArrayNotHasKey('data-bs-theme', $hook->get_attributes());
        $PAGE = $atriumpage;
    }

    /**
     * The before-paint script is emitted only for "follow the device".
     */
    public function test_head_script(): void {
        $this->resetAfterTest();
        $renderer = $this->use_atrium();

        $hook = new before_standard_head_html_generation($renderer);
        hook_callbacks::before_standard_head_html_generation($hook);
        $this->assertSame('', $hook->get_output());

        set_config('defaultscheme', scheme::SYSTEM, 'theme_atrium');
        $hook = new before_standard_head_html_generation($renderer);
        hook_callbacks::before_standard_head_html_generation($hook);
        $this->assertStringContainsString('prefers-color-scheme: dark', $hook->get_output());
        $this->assertStringContainsString('data-bs-theme', $hook->get_output());
    }

    /**
     * The analytics tag loads only with a well-formed measurement id, and never for admins.
     */
    public function test_analytics(): void {
        $this->resetAfterTest();
        $renderer = $this->use_atrium();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertSame('', hook_callbacks::ga4_id());
        set_config('ga4id', 'UA-12345-1', 'theme_atrium');
        $this->assertSame('', hook_callbacks::ga4_id(), 'Universal Analytics ids are not GA4 ids');
        set_config('ga4id', ' g-abc123xy ', 'theme_atrium');
        $this->assertSame('G-ABC123XY', hook_callbacks::ga4_id());

        $hook = new before_standard_head_html_generation($renderer);
        hook_callbacks::before_standard_head_html_generation($hook);
        $this->assertStringContainsString('googletagmanager.com/gtag/js?id=G-ABC123XY', $hook->get_output());
        $this->assertStringContainsString('anonymize_ip', $hook->get_output());

        $this->setAdminUser();
        $this->assertSame('', hook_callbacks::ga4_id());
        $hook = new before_standard_head_html_generation($renderer);
        hook_callbacks::before_standard_head_html_generation($hook);
        $this->assertStringNotContainsString('googletagmanager', $hook->get_output());
    }

    /**
     * The user menu gets a switch for real users while dark mode is enabled.
     */
    public function test_user_menu(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->use_atrium();

        $hook = new extend_user_menu();
        hook_callbacks::extend_user_menu($hook);
        $this->assertSame([], $hook->get_navitems(), 'Nobody logged in');

        $this->setUser($user);
        $hook = new extend_user_menu();
        hook_callbacks::extend_user_menu($hook);
        $items = $hook->get_navitems();
        $this->assertCount(1, $items);
        $this->assertSame('link', $items[0]->itemtype);
        $this->assertSame(get_string('switchtodark', 'theme_atrium'), $items[0]->title);
        $this->assertSame('dark', $items[0]->url->get_param('scheme'));
        $this->assertSame('moon, theme_atrium', $items[0]->pix);

        scheme::set(scheme::DARK);
        $hook = new extend_user_menu();
        hook_callbacks::extend_user_menu($hook);
        $this->assertSame(get_string('switchtolight', 'theme_atrium'), $hook->get_navitems()[0]->title);
        $this->assertSame('sun, theme_atrium', $hook->get_navitems()[0]->pix);

        set_config('enabledarkmode', 0, 'theme_atrium');
        $hook = new extend_user_menu();
        hook_callbacks::extend_user_menu($hook);
        $this->assertSame([], $hook->get_navitems());
    }
}
