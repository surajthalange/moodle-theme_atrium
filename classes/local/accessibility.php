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
 * The accessibility toolbar: text size, a reading font, high contrast and reduced motion,
 * each a per-user preference applied on the server as a body class, so there is no flash
 * and every page, including the login page after sign-in, honours it.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class accessibility {
    /** @var string Text size preference: normal, large or larger. */
    public const PREF_TEXTSIZE = 'theme_atrium_textsize';

    /** @var string Reading font preference: 0 or 1. */
    public const PREF_FONT = 'theme_atrium_readingfont';

    /** @var string High contrast preference: 0 or 1. */
    public const PREF_CONTRAST = 'theme_atrium_contrast';

    /** @var string Reduced motion preference: 0 or 1. */
    public const PREF_MOTION = 'theme_atrium_reducemotion';

    /** @var string[] Text sizes. */
    public const SIZES = ['normal', 'large', 'larger'];

    /**
     * Whether the site offers the toolbar.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $value = get_config('theme_atrium', 'navbar_a11y');
        return $value === false || $value === '' ? true : (bool) $value;
    }

    /**
     * Whether the toolbar is offered to the current user.
     *
     * @return bool
     */
    public static function wanted(): bool {
        return self::enabled() && isloggedin() && !isguestuser();
    }

    /**
     * The current user's choices.
     *
     * @return array{textsize: string, font: bool, contrast: bool, motion: bool}
     */
    public static function state(): array {
        if (!isloggedin() || isguestuser()) {
            return ['textsize' => 'normal', 'font' => false, 'contrast' => false, 'motion' => false];
        }
        $size = (string) get_user_preferences(self::PREF_TEXTSIZE, 'normal');
        return [
            'textsize' => in_array($size, self::SIZES, true) ? $size : 'normal',
            'font' => (bool) get_user_preferences(self::PREF_FONT, 0),
            'contrast' => (bool) get_user_preferences(self::PREF_CONTRAST, 0),
            'motion' => (bool) get_user_preferences(self::PREF_MOTION, 0),
        ];
    }

    /**
     * Body classes for the current user's choices.
     *
     * @return string[]
     */
    public static function body_classes(): array {
        $state = self::state();
        $classes = [];
        if ($state['textsize'] !== 'normal') {
            $classes[] = 'atrium-text-' . $state['textsize'];
        }
        if ($state['font']) {
            $classes[] = 'atrium-font-reading';
        }
        if ($state['contrast']) {
            $classes[] = 'atrium-contrast';
        }
        if ($state['motion']) {
            $classes[] = 'atrium-reduce-motion';
        }
        return $classes;
    }

    /**
     * Save choices. Unknown values are ignored.
     *
     * @param array $values textsize, font, contrast, motion
     */
    public static function save(array $values): void {
        if (isset($values['textsize']) && in_array($values['textsize'], self::SIZES, true)) {
            set_user_preference(self::PREF_TEXTSIZE, $values['textsize']);
        }
        foreach (['font' => self::PREF_FONT, 'contrast' => self::PREF_CONTRAST, 'motion' => self::PREF_MOTION] as $key => $name) {
            if (isset($values[$key])) {
                set_user_preference($name, (int) (bool) $values[$key]);
            }
        }
    }

    /**
     * Back to the defaults.
     */
    public static function reset(): void {
        foreach ([self::PREF_TEXTSIZE, self::PREF_FONT, self::PREF_CONTRAST, self::PREF_MOTION] as $name) {
            unset_user_preference($name);
        }
    }

    /**
     * Template context for the toolbar.
     *
     * @return array
     */
    public static function export(): array {
        global $PAGE;
        $state = self::state();
        $sizes = [];
        foreach (self::SIZES as $size) {
            $sizes[] = [
                'value' => $size,
                'label' => get_string('a11y_textsize_' . $size, 'theme_atrium'),
                'checked' => $state['textsize'] === $size,
            ];
        }
        return [
            'action' => (new moodle_url('/theme/atrium/a11y.php'))->out(false),
            'returnurl' => $PAGE->url->out_as_local_url(false),
            'sizes' => $sizes,
            'font' => $state['font'],
            'contrast' => $state['contrast'],
            'motion' => $state['motion'],
            'sesskey' => sesskey(),
        ];
    }
}
