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
 * The login page settings: layout, panel copy, text around the form.
 *
 * Every page on the login layout (login, sign up, forgotten password, MFA) shares it.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class loginpage {
    /** @var string[] Layouts: the card centred over the image, or beside an image panel. */
    public const LAYOUTS = ['centred', 'panelleft', 'panelright'];

    /**
     * The configured layout.
     *
     * @return string
     */
    public static function layout(): string {
        $layout = (string) get_config('theme_atrium', 'loginlayout');
        return in_array($layout, self::LAYOUTS, true) ? $layout : 'centred';
    }

    /**
     * Whether the layout has an image panel beside the card.
     *
     * @return bool
     */
    public static function panel(): bool {
        return self::layout() !== 'centred';
    }

    /**
     * Body classes naming the layout and the switches.
     *
     * @return string[]
     */
    public static function body_classes(): array {
        $classes = ['atrium-login-' . self::layout()];
        $langmenu = get_config('theme_atrium', 'loginshowlangmenu');
        if ($langmenu !== false && $langmenu !== '' && !(bool) $langmenu) {
            $classes[] = 'atrium-login-nolangmenu';
        }
        if ((bool) get_config('theme_atrium', 'loginsignupbutton')) {
            $classes[] = 'atrium-login-signupbutton';
        }
        return $classes;
    }

    /**
     * Template context for the login template.
     *
     * @return array{layout: string, panel: bool, panelheading: string, paneltext: string, textabove: string, textbelow: string}
     */
    public static function export(): array {
        $context = \context_system::instance();
        $text = function (string $name) use ($context): string {
            $value = (string) get_config('theme_atrium', $name);
            if (trim(strip_tags($value)) === '') {
                return '';
            }
            return format_text($value, FORMAT_HTML, ['context' => $context, 'noclean' => true]);
        };
        return [
            'layout' => self::layout(),
            'panel' => self::panel(),
            'panelheading' => format_string(
                (string) get_config('theme_atrium', 'loginpanelheading'),
                true,
                ['context' => $context]
            ),
            'paneltext' => $text('loginpaneltext'),
            'textabove' => $text('logintextabove'),
            'textbelow' => $text('logintextbelow'),
        ];
    }
}
