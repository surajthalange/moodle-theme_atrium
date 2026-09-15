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

/**
 * The site-wide announcement bar.
 *
 * Dismissal is remembered per user as a hash of the announcement text, so a changed
 * announcement reappears on its own and an unchanged one stays dismissed.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class announcement {
    /** @var string The user preference holding the hash of the dismissed text. */
    public const PREFERENCE = 'theme_atrium_announcement';

    /** @var string[] Bootstrap alert tones the bar may use. */
    public const TYPES = ['info', 'success', 'warning', 'danger'];

    /**
     * Whether an announcement is configured and switched on.
     *
     * @return bool
     */
    public static function enabled(): bool {
        return (bool) get_config('theme_atrium', 'announcement_enable') && trim(strip_tags(self::raw())) !== '';
    }

    /**
     * The announcement text as saved.
     *
     * @return string
     */
    public static function raw(): string {
        return (string) get_config('theme_atrium', 'announcement_text');
    }

    /**
     * The hash that identifies the current text.
     *
     * @return string
     */
    public static function hash(): string {
        return sha1(trim(self::raw()));
    }

    /**
     * Whether the current user dismissed this exact announcement.
     *
     * @return bool
     */
    public static function dismissed(): bool {
        if (!isloggedin() || isguestuser()) {
            return false;
        }
        return get_user_preferences(self::PREFERENCE) === self::hash();
    }

    /**
     * Remember that the current user dismissed the current announcement.
     */
    public static function dismiss(): void {
        set_user_preference(self::PREFERENCE, self::hash());
    }

    /**
     * Whether the bar should render for the current user.
     *
     * @return bool
     */
    public static function wanted(): bool {
        return self::enabled() && !self::dismissed();
    }

    /**
     * Template context.
     *
     * @return array{text: string, type: string, dismissible: bool, hash: string}
     */
    public static function export(): array {
        $type = (string) get_config('theme_atrium', 'announcement_type');
        // Dismissible unless the admin switched it off; the setting's default is on.
        $dismissible = get_config('theme_atrium', 'announcement_dismissible');
        $dismissible = $dismissible === false || (bool) $dismissible;
        return [
            'text' => format_text(self::raw(), FORMAT_HTML, ['context' => \context_system::instance(), 'noclean' => true]),
            'type' => in_array($type, self::TYPES, true) ? $type : 'info',
            'dismissible' => $dismissible && isloggedin() && !isguestuser(),
            'hash' => self::hash(),
        ];
    }
}
