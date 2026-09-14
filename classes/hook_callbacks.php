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

/**
 * Hook callbacks: the colour scheme on the html element, and the toggle in the user menu.
 *
 * Every callback returns immediately when Atrium is not the theme rendering the page, so
 * enabling the plugin has no effect on sites that pick a different theme.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class hook_callbacks {
    /**
     * Whether the page being rendered uses this theme.
     *
     * @return bool
     */
    private static function active(): bool {
        global $PAGE;
        return $PAGE->theme->name === 'atrium';
    }

    /**
     * Put data-bs-theme on the html element so the scheme applies before any script runs.
     *
     * @param before_html_attributes $hook
     */
    public static function before_html_attributes(before_html_attributes $hook): void {
        if (!self::active()) {
            return;
        }
        $hook->add_attribute('data-bs-theme', scheme::initial());
        $hook->add_attribute('data-atrium-scheme', scheme::resolve());
    }

    /**
     * When the scheme is "system", swap to dark before first paint if the browser prefers it.
     *
     * This is the one inline script in the theme. It has to run before the stylesheet
     * paints, which no AMD module can do, and it reads nothing but a media query.
     *
     * @param before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        if (!self::active() || scheme::resolve() !== scheme::SYSTEM) {
            return;
        }
        $hook->add_html(
            '<script>if (window.matchMedia("(prefers-color-scheme: dark)").matches) {'
            . 'document.documentElement.setAttribute("data-bs-theme", "dark");}</script>'
        );
    }

    /**
     * Add the light/dark switch to the user menu.
     *
     * A plain link: it works without JavaScript through scheme.php, and scheme.js
     * upgrades it to an instant switch when it is available.
     *
     * @param extend_user_menu $hook
     */
    public static function extend_user_menu(extend_user_menu $hook): void {
        if (!self::active() || !scheme::can_toggle()) {
            return;
        }
        $dark = scheme::initial() === scheme::DARK;
        $item = new \stdClass();
        $item->itemtype = 'link';
        $item->url = scheme::toggle_url();
        $item->title = get_string($dark ? 'switchtolight' : 'switchtodark', 'theme_atrium');
        $item->titleidentifier = ($dark ? 'switchtolight' : 'switchtodark') . ',theme_atrium';
        $item->pix = ($dark ? 'sun' : 'moon') . ', theme_atrium';
        $hook->add_navitem($item);
    }
}
