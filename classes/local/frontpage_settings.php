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
 * The front page settings as a typed model.
 *
 * Everything the front page shows comes through here, so the renderable never reads
 * config directly and the defaults live in one place. Defaults are chosen so a fresh
 * install already has a finished front page: a hero named after the site, three feature
 * blocks, the latest courses, four counters and a call to action. Testimonials and the
 * about band start off, because they need real content.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class frontpage_settings {
    /** @var string[] Section keys in display order. */
    public const SECTIONS = ['hero', 'features', 'showcase', 'stats', 'testimonials', 'about', 'cta'];

    /** @var int Feature blocks the settings page offers. */
    public const MAX_FEATURES = 6;

    /** @var int Testimonials the settings page offers. */
    public const MAX_TESTIMONIALS = 6;

    /** @var int Counters in the stats strip. */
    public const MAX_STATS = 4;

    /** @var string[] Icons an administrator can pick for a feature block, Font Awesome 6 free. */
    public const ICONS = [
        'fa-graduation-cap', 'fa-book-open', 'fa-chalkboard-user', 'fa-users', 'fa-certificate', 'fa-chart-line',
        'fa-clock', 'fa-globe', 'fa-lightbulb', 'fa-laptop', 'fa-mobile-screen', 'fa-comments',
        'fa-star', 'fa-shield-halved', 'fa-headset', 'fa-video', 'fa-pen-to-square', 'fa-puzzle-piece',
        'fa-rocket', 'fa-trophy', 'fa-heart', 'fa-language', 'fa-calendar-check', 'fa-circle-check',
    ];

    /** @var array<string, string> Placeholders the stats strip and hero resolve, keyed by token. */
    public const STAT_TOKENS = ['{courses}', '{users}', '{categories}', '{completions}'];

    /**
     * Whether the designed front page is on at all.
     *
     * @return bool
     */
    public static function enabled(): bool {
        return self::bool('fp_enable', true);
    }

    /**
     * Whether one section is on.
     *
     * @param string $section One of SECTIONS.
     * @return bool
     */
    public static function section_enabled(string $section): bool {
        $defaults = ['hero' => true, 'features' => true, 'showcase' => true, 'stats' => true,
            'testimonials' => false, 'about' => false, 'cta' => true];
        return self::enabled() && self::bool('fp_' . $section . '_enable', $defaults[$section] ?? false);
    }

    /**
     * Hero settings.
     *
     * @return array{heading: string, subheading: string, button1text: string, button1url: string,
     *     button2text: string, button2url: string, align: string, height: string, transparentnavbar: bool}
     */
    public static function hero(): array {
        return [
            'heading' => self::text('fp_hero_heading', get_string('fp_hero_heading_default', 'theme_atrium')),
            'subheading' => self::text('fp_hero_subheading', get_string('fp_hero_subheading_default', 'theme_atrium')),
            'button1text' => self::text('fp_hero_button1text', get_string('fp_hero_button1text_default', 'theme_atrium')),
            'button1url' => self::text('fp_hero_button1url', '/course/index.php'),
            'button2text' => self::text('fp_hero_button2text', ''),
            'button2url' => self::text('fp_hero_button2url', ''),
            'align' => self::choice('fp_hero_align', ['left', 'center'], 'left'),
            'height' => self::choice('fp_hero_height', ['compact', 'standard', 'tall'], 'standard'),
            'transparentnavbar' => self::bool('fp_hero_transparentnavbar', false),
        ];
    }

    /**
     * Feature blocks that have at least a title.
     *
     * @return array{heading: string, items: array<int, array{icon: string, title: string, text: string, url: string}>}
     */
    public static function features(): array {
        $count = (int) self::choice('fp_features_count', ['3', '6'], '3');
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $title = self::text("fp_feature{$i}_title", $i <= 3 ? get_string("fp_feature{$i}_title_default", 'theme_atrium') : '');
            if ($title === '') {
                continue;
            }
            $icon = self::text("fp_feature{$i}_icon", self::ICONS[($i - 1) % count(self::ICONS)]);
            $items[] = [
                'icon' => in_array($icon, self::ICONS, true) ? $icon : self::ICONS[0],
                'title' => $title,
                'text' => self::text(
                    "fp_feature{$i}_text",
                    $i <= 3 ? get_string("fp_feature{$i}_text_default", 'theme_atrium') : ''
                ),
                'url' => self::text("fp_feature{$i}_url", ''),
            ];
        }
        return [
            'heading' => self::text('fp_features_heading', get_string('fp_features_heading_default', 'theme_atrium')),
            'items' => $items,
        ];
    }

    /**
     * Course showcase settings.
     *
     * @return array{heading: string, source: string, category: int, ids: int[], count: int}
     */
    public static function showcase(): array {
        $ids = array_values(array_filter(array_map('intval', preg_split('/[\s,]+/', self::text('fp_showcase_ids', '')))));
        return [
            'heading' => self::text('fp_showcase_heading', get_string('fp_showcase_heading_default', 'theme_atrium')),
            'source' => self::choice('fp_showcase_source', ['latest', 'category', 'ids'], 'latest'),
            'category' => (int) get_config('theme_atrium', 'fp_showcase_category'),
            'ids' => $ids,
            'count' => max(3, min(12, (int) (get_config('theme_atrium', 'fp_showcase_count') ?: 6))),
        ];
    }

    /**
     * Stats strip: label and raw value (tokens unresolved) for each counter that has a label.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function stats(): array {
        $defaults = [
            1 => [get_string('fp_stat1_label_default', 'theme_atrium'), '{courses}'],
            2 => [get_string('fp_stat2_label_default', 'theme_atrium'), '{users}'],
            3 => [get_string('fp_stat3_label_default', 'theme_atrium'), '{categories}'],
            4 => [get_string('fp_stat4_label_default', 'theme_atrium'), '{completions}'],
        ];
        $items = [];
        for ($i = 1; $i <= self::MAX_STATS; $i++) {
            $label = self::text("fp_stat{$i}_label", $defaults[$i][0]);
            if ($label === '') {
                continue;
            }
            $items[] = ['label' => $label, 'value' => self::text("fp_stat{$i}_value", $defaults[$i][1])];
        }
        return $items;
    }

    /**
     * Testimonials that have a quote.
     *
     * @return array{heading: string, items: array<int, array{index: int, quote: string, name: string, role: string}>}
     */
    public static function testimonials(): array {
        $items = [];
        for ($i = 1; $i <= self::MAX_TESTIMONIALS; $i++) {
            $quote = self::text("fp_testimonial{$i}_quote", '');
            if ($quote === '') {
                continue;
            }
            $items[] = [
                'index' => $i,
                'quote' => $quote,
                'name' => self::text("fp_testimonial{$i}_name", ''),
                'role' => self::text("fp_testimonial{$i}_role", ''),
            ];
        }
        return [
            'heading' => self::text('fp_testimonials_heading', get_string('fp_testimonials_heading_default', 'theme_atrium')),
            'items' => $items,
        ];
    }

    /**
     * About band settings.
     *
     * @return array{heading: string, text: string, imageside: string, buttontext: string, buttonurl: string}
     */
    public static function about(): array {
        return [
            'heading' => self::text('fp_about_heading', ''),
            'text' => (string) get_config('theme_atrium', 'fp_about_text'),
            'imageside' => self::choice('fp_about_imageside', ['left', 'right'], 'right'),
            'buttontext' => self::text('fp_about_buttontext', ''),
            'buttonurl' => self::text('fp_about_buttonurl', ''),
        ];
    }

    /**
     * Call-to-action band settings.
     *
     * @return array{heading: string, text: string, buttontext: string, buttonurl: string, background: string}
     */
    public static function cta(): array {
        return [
            'heading' => self::text('fp_cta_heading', get_string('fp_cta_heading_default', 'theme_atrium')),
            'text' => self::text('fp_cta_text', get_string('fp_cta_text_default', 'theme_atrium')),
            'buttontext' => self::text('fp_cta_buttontext', get_string('fp_cta_buttontext_default', 'theme_atrium')),
            'buttonurl' => self::text('fp_cta_buttonurl', '/login/index.php'),
            'background' => self::choice('fp_cta_background', ['accent', 'dark', 'image'], 'accent'),
        ];
    }

    /**
     * Replace {sitename} and the stat tokens in a string.
     *
     * @param string $text
     * @param array $counts Token (without braces) => value.
     * @return string
     */
    public static function resolve(string $text, array $counts): string {
        global $SITE;
        $replace = ['{sitename}' => format_string($SITE->fullname, true, ['context' => \context_system::instance()])];
        foreach ($counts as $token => $value) {
            $replace['{' . $token . '}'] = number_format((float) $value);
        }
        return strtr($text, $replace);
    }

    /**
     * A text setting, trimmed; the default applies only while the setting has never been saved.
     *
     * An empty saved value is deliberate (a second button switched off, a spare feature
     * block left blank), so it is returned as empty rather than replaced by the default.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    private static function text(string $name, string $default): string {
        $value = get_config('theme_atrium', $name);
        return $value === false || $value === null ? $default : trim((string) $value);
    }

    /**
     * A boolean setting with a default.
     *
     * @param string $name
     * @param bool $default
     * @return bool
     */
    private static function bool(string $name, bool $default): bool {
        $value = get_config('theme_atrium', $name);
        return $value === false || $value === '' ? $default : (bool) $value;
    }

    /**
     * A setting that must be one of a list.
     *
     * @param string $name
     * @param string[] $choices
     * @param string $default
     * @return string
     */
    private static function choice(string $name, array $choices, string $default): string {
        $value = (string) get_config('theme_atrium', $name);
        return in_array($value, $choices, true) ? $value : $default;
    }
}
