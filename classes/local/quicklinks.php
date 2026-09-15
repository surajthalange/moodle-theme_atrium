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
 * Quick links: a grid of icon links behind a button in the navigation bar.
 *
 * One per line in the setting: "icon|Label|URL" with an optional "|newtab" at the end.
 * Icons are Font Awesome class names; an unknown or missing icon falls back to a link icon.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class quicklinks {
    /** @var int Links the menu shows at most. */
    public const MAX = 12;

    /**
     * Parse the setting.
     *
     * @param string $setting
     * @return array<int, array{icon: string, label: string, url: string, newtab: bool}>
     */
    public static function parse(string $setting): array {
        $links = [];
        foreach (preg_split('/\R/', $setting) as $line) {
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) < 3 || $parts[1] === '' || $parts[2] === '') {
                continue;
            }
            $icon = preg_match('/^fa-[a-z0-9-]+$/', $parts[0]) ? $parts[0] : 'fa-link';
            $url = $parts[2];
            if (!preg_match('#^(https?://|/)#i', $url)) {
                continue;
            }
            $links[] = [
                'icon' => $icon,
                'label' => $parts[1],
                'url' => $url,
                'newtab' => isset($parts[3]) && strtolower($parts[3]) === 'newtab',
            ];
            if (count($links) === self::MAX) {
                break;
            }
        }
        return $links;
    }

    /**
     * Template context for the navbar menu, or false when there are no links.
     *
     * @return array|false
     */
    public static function export() {
        $links = self::parse((string) get_config('theme_atrium', 'quicklinks'));
        if (!$links) {
            return false;
        }
        $context = \context_system::instance();
        foreach ($links as &$link) {
            $link['label'] = format_string($link['label'], true, ['context' => $context]);
            $link['url'] = (new moodle_url($link['url']))->out(false);
        }
        return ['links' => $links];
    }
}
