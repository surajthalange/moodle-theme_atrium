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

namespace theme_atrium\output;

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;

/**
 * The configurable part of the page footer: content columns, social links, legal line.
 *
 * Everything core requires in a footer (login info, documentation link, the standard
 * footer HTML that plugins inject) is rendered by the template from $OUTPUT as Boost
 * does; this class only supplies what the administrator configured.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements renderable, templatable {
    /** @var array<string, string> Platform => Font Awesome brand icon class. */
    public const PLATFORMS = [
        'facebook' => 'fa-brands fa-facebook',
        'instagram' => 'fa-brands fa-instagram',
        'linkedin' => 'fa-brands fa-linkedin',
        'x' => 'fa-brands fa-x-twitter',
        'youtube' => 'fa-brands fa-youtube',
        'github' => 'fa-brands fa-github',
        'mastodon' => 'fa-brands fa-mastodon',
        'bluesky' => 'fa-brands fa-bluesky',
        'tiktok' => 'fa-brands fa-tiktok',
        'whatsapp' => 'fa-brands fa-whatsapp',
        'telegram' => 'fa-brands fa-telegram',
        'discord' => 'fa-brands fa-discord',
    ];

    /**
     * Parse the social links setting: one "platform|url" per line, unknown platforms skipped.
     *
     * @param string $setting
     * @return array<int, array{platform: string, icon: string, url: string}>
     */
    public static function parse_social_links(string $setting): array {
        $links = [];
        foreach (preg_split('/\R/', $setting) as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            if (count($parts) !== 2) {
                continue;
            }
            $platform = strtolower($parts[0]);
            $url = $parts[1];
            if (!isset(self::PLATFORMS[$platform]) || !preg_match('#^https?://#i', $url)) {
                continue;
            }
            $links[] = ['platform' => $platform, 'icon' => self::PLATFORMS[$platform], 'url' => $url];
        }
        return $links;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $SITE;
        $context = \context_system::instance();

        $columns = [];
        $wanted = (int) get_config('theme_atrium', 'footercolumns');
        for ($i = 1; $i <= min(3, $wanted); $i++) {
            $title = (string) get_config('theme_atrium', 'footercol' . $i . 'title');
            $html = (string) get_config('theme_atrium', 'footercol' . $i . 'html');
            if (trim($title) === '' && trim(strip_tags($html)) === '') {
                continue;
            }
            $columns[] = [
                'title' => format_string($title, true, ['context' => $context]),
                'html' => format_text($html, FORMAT_HTML, ['context' => $context, 'noclean' => true]),
            ];
        }

        $legal = (string) get_config('theme_atrium', 'footerlegal');
        if (trim($legal) === '') {
            $legal = get_string('footerlegal_default', 'theme_atrium');
        }
        $sitename = format_string($SITE->fullname, true, ['context' => $context]);
        $legal = str_replace(['{year}', '{sitename}'], [userdate(time(), '%Y'), $sitename], $legal);

        $poweredby = get_config('theme_atrium', 'showpoweredby');
        $social = self::parse_social_links((string) get_config('theme_atrium', 'sociallinks'));

        return [
            'columns' => $columns,
            'hascolumns' => !empty($columns),
            'columnclass' => 'atrium-footer-columns-' . max(1, count($columns)),
            'social' => $social,
            'hassocial' => !empty($social),
            'legal' => format_string($legal, true, ['context' => $context]),
            'showpoweredby' => $poweredby === false || $poweredby === '' ? true : (bool) $poweredby,
        ];
    }
}
