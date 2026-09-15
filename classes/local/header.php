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

use renderer_base;

/**
 * The header settings: what the brand shows, the navigation bar height, whether it sticks.
 *
 * The height is a Sass variable, so it is applied in theme_atrium_get_pre_scss(); the
 * rest are body classes and template context.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class header {
    /** @var string[] What the brand shows. */
    public const BRANDSTYLES = ['both', 'logo', 'name'];

    /** @var array<string, int> Navigation bar heights in pixels. */
    public const HEIGHTS = ['standard' => 56, 'compact' => 48];

    /**
     * The configured brand style.
     *
     * @return string
     */
    public static function brandstyle(): string {
        $style = (string) get_config('theme_atrium', 'brandstyle');
        return in_array($style, self::BRANDSTYLES, true) ? $style : 'both';
    }

    /**
     * The navigation bar height in pixels.
     *
     * @return int
     */
    public static function height(): int {
        $height = (string) get_config('theme_atrium', 'navbarheight');
        return self::HEIGHTS[$height] ?? self::HEIGHTS['standard'];
    }

    /**
     * Whether the navigation bar stays at the top while the page scrolls.
     *
     * @return bool
     */
    public static function sticky(): bool {
        $value = get_config('theme_atrium', 'navbarsticky');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * Body classes for the switches.
     *
     * @return string[]
     */
    public static function body_classes(): array {
        return self::sticky() ? [] : ['atrium-navbar-static'];
    }

    /**
     * The brand: logo and/or site name, as the sidebar and the navigation bar show it.
     *
     * A logo is shown only when the site has one, and the name is kept when it has none,
     * so the brand is never empty.
     *
     * @param renderer_base $output
     * @return array{sitename: string, haslogo: bool, logourl: string, showname: bool}
     */
    public static function brand(renderer_base $output): array {
        global $SITE;
        $style = self::brandstyle();
        $haslogo = $output->should_display_navbar_logo() && $style !== 'name';
        return [
            'sitename' => format_string($SITE->shortname, true, ['context' => \context_course::instance(SITEID)]),
            'haslogo' => $haslogo,
            'logourl' => $haslogo ? (string) $output->get_compact_logo_url(300, 44) : '',
            'showname' => !$haslogo || $style !== 'logo',
        ];
    }
}
