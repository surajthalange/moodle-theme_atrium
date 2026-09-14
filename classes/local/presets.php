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
 * The colour presets: a name, an accent, and whether the sidebar is light or dark.
 *
 * A preset is a starting point, not a lock: the brand colour setting overrides the
 * accent and the sidebar tone setting overrides the tone, each independently.
 *
 * Accents were chosen so that white text on the accent, and the accent as text on the
 * light and dark surfaces, all pass WCAG 2.2 AA (4.5:1); tests/local/presets_test.php
 * computes the contrast from this data so a future edit cannot quietly break that.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class presets {
    /** @var string The preset a fresh install uses. */
    public const DEFAULT = 'atrium';

    /** @var string Light sidebar tone. */
    public const TONE_LIGHT = 'light';

    /** @var string Dark sidebar tone. */
    public const TONE_DARK = 'dark';

    /** @var float How far the accent is tinted towards white for link text in the dark scheme; matches _tokens.scss. */
    public const DARK_LINK_TINT = 0.45;

    /** @var string The light surface accents are read against. */
    public const LIGHT_SURFACE = '#ffffff';

    /** @var string The dark surface accents are read against; matches $body-secondary-bg-dark in pre.scss. */
    public const DARK_SURFACE = '#171a23';

    /** @var array<string, array{accent: string, sidebar: string}> Preset definitions, in display order. */
    private const PRESETS = [
        'atrium' => ['accent' => '#4f46e5', 'sidebar' => self::TONE_LIGHT],
        'indigodark' => ['accent' => '#4f46e5', 'sidebar' => self::TONE_DARK],
        'emerald' => ['accent' => '#047857', 'sidebar' => self::TONE_LIGHT],
        'rose' => ['accent' => '#be123c', 'sidebar' => self::TONE_LIGHT],
        'slate' => ['accent' => '#334155', 'sidebar' => self::TONE_DARK],
    ];

    /**
     * Every preset, keyed by name.
     *
     * @return array<string, array{accent: string, sidebar: string}>
     */
    public static function all(): array {
        return self::PRESETS;
    }

    /**
     * One preset; an unknown name falls back to the default rather than failing a page.
     *
     * @param string $name
     * @return array{accent: string, sidebar: string}
     */
    public static function get(string $name): array {
        return self::PRESETS[$name] ?? self::PRESETS[self::DEFAULT];
    }

    /**
     * The preset named in the theme settings.
     *
     * @return string
     */
    public static function current_name(): string {
        $name = (string) get_config('theme_atrium', 'preset');
        return isset(self::PRESETS[$name]) ? $name : self::DEFAULT;
    }

    /**
     * The accent colour in effect: the brand colour setting when set, else the preset's.
     *
     * @return string A CSS hex colour.
     */
    public static function accent(): string {
        $brand = trim((string) get_config('theme_atrium', 'brandcolor'));
        if (preg_match('/^#[0-9a-f]{6}$/i', $brand)) {
            return strtolower($brand);
        }
        return self::get(self::current_name())['accent'];
    }

    /**
     * The sidebar tone in effect: the setting when it is not "follow preset", else the preset's.
     *
     * @return string One of the TONE_* constants.
     */
    public static function sidebar_tone(): string {
        $tone = (string) get_config('theme_atrium', 'sidebartone');
        if ($tone === self::TONE_LIGHT || $tone === self::TONE_DARK) {
            return $tone;
        }
        return self::get(self::current_name())['sidebar'];
    }

    /**
     * Choices for the preset setting, localised.
     *
     * @return array<string, string>
     */
    public static function choices(): array {
        $choices = [];
        foreach (array_keys(self::PRESETS) as $name) {
            $choices[$name] = get_string('preset:' . $name, 'theme_atrium');
        }
        return $choices;
    }

    /**
     * WCAG relative luminance of a hex colour.
     *
     * @param string $hex "#rrggbb"
     * @return float 0 (black) to 1 (white)
     */
    public static function luminance(string $hex): float {
        $channels = [];
        foreach (str_split(ltrim($hex, '#'), 2) as $pair) {
            $value = hexdec($pair) / 255;
            $channels[] = $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }
        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * Mix a colour towards white, the way Bootstrap's tint-color() does.
     *
     * @param string $hex "#rrggbb"
     * @param float $weight 0 (unchanged) to 1 (white)
     * @return string "#rrggbb"
     */
    public static function tint(string $hex, float $weight): string {
        $out = '#';
        foreach (str_split(ltrim($hex, '#'), 2) as $pair) {
            $value = hexdec($pair);
            $out .= str_pad(dechex((int) round($value + (255 - $value) * $weight)), 2, '0', STR_PAD_LEFT);
        }
        return $out;
    }

    /**
     * WCAG contrast ratio between two hex colours.
     *
     * @param string $a
     * @param string $b
     * @return float 1 to 21
     */
    public static function contrast(string $a, string $b): float {
        $la = self::luminance($a);
        $lb = self::luminance($b);
        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }
}
