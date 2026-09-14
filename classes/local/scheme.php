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

use moodle_url;

/**
 * Resolves the colour scheme (light or dark) for the current request.
 *
 * Order: the user's saved preference, then the site default, then light. "System" means
 * the browser decides; the server still emits light so the page is never unstyled, and
 * a three-line script in the head swaps to dark before first paint when the browser
 * prefers it (see hook_callbacks::before_standard_head_html_generation).
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class scheme {
    /** @var string */
    public const LIGHT = 'light';

    /** @var string */
    public const DARK = 'dark';

    /** @var string Follow the browser's prefers-color-scheme. */
    public const SYSTEM = 'system';

    /** @var string The user preference name; declared to the privacy API. */
    public const PREFERENCE = 'theme_atrium_scheme';

    /**
     * Whether dark mode is enabled site-wide.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $value = get_config('theme_atrium', 'enabledarkmode');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * The scheme the current user should get: light, dark or system.
     *
     * @return string
     */
    public static function resolve(): string {
        if (!self::enabled()) {
            return self::LIGHT;
        }
        if (isloggedin() && !isguestuser()) {
            $preference = get_user_preferences(self::PREFERENCE);
            if (self::valid($preference)) {
                return $preference;
            }
        }
        $default = (string) get_config('theme_atrium', 'defaultscheme');
        return self::valid($default) ? $default : self::LIGHT;
    }

    /**
     * What to emit in the html element's data-bs-theme: never "system".
     *
     * @return string light or dark
     */
    public static function initial(): string {
        return self::resolve() === self::DARK ? self::DARK : self::LIGHT;
    }

    /**
     * Save the scheme for the current user.
     *
     * @param string $scheme
     */
    public static function set(string $scheme): void {
        if (!self::valid($scheme)) {
            throw new \invalid_parameter_exception('Unknown scheme');
        }
        set_user_preference(self::PREFERENCE, $scheme);
    }

    /**
     * The URL that flips to the other scheme without JavaScript.
     *
     * @param moodle_url|null $returnurl Where to come back to; defaults to the current page.
     * @return moodle_url
     */
    public static function toggle_url(?moodle_url $returnurl = null): moodle_url {
        global $PAGE;
        $returnurl = $returnurl ?? $PAGE->url;
        return new moodle_url('/theme/atrium/scheme.php', [
            'scheme' => self::initial() === self::DARK ? self::LIGHT : self::DARK,
            'sesskey' => sesskey(),
            'returnurl' => $returnurl->out_as_local_url(false),
        ]);
    }

    /**
     * Whether a toggle should be offered: dark mode on and a real user to remember it for.
     *
     * @return bool
     */
    public static function can_toggle(): bool {
        return self::enabled() && isloggedin() && !isguestuser();
    }

    /**
     * Whether a string names a scheme.
     *
     * @param mixed $scheme
     * @return bool
     */
    public static function valid($scheme): bool {
        return in_array($scheme, [self::LIGHT, self::DARK, self::SYSTEM], true);
    }
}
