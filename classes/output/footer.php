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

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * The configurable part of the page footer: up to four typed columns and the bottom bar.
 *
 * A column is custom HTML, a menu ("Label|URL" per line), the social links, or the
 * contact details. The bottom bar carries the legal line, the privacy and terms links
 * and the Moodle credit.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements renderable, templatable {
    /** @var int Columns at most. */
    public const MAX_COLUMNS = 4;

    /** @var string[] Column types. */
    public const TYPES = ['html', 'menu', 'social', 'contact'];

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
     * Parse a menu column: one "Label|URL" per line, in the custom menu's notation.
     *
     * Leading hyphens (custom menu nesting) are dropped, so a copy of the site's custom
     * menu text works as is. Lines without a usable URL are skipped.
     *
     * @param string $setting
     * @return array<int, array{label: string, url: string}>
     */
    public static function parse_menu(string $setting): array {
        $items = [];
        foreach (preg_split('/\R/', $setting) as $line) {
            $parts = array_map('trim', explode('|', ltrim($line, " \t-")));
            if (count($parts) < 2 || $parts[0] === '' || !preg_match('#^(https?://|/|\#)#i', $parts[1])) {
                continue;
            }
            $items[] = ['label' => $parts[0], 'url' => $parts[1]];
        }
        return $items;
    }

    /**
     * Build one column from its settings, or null when it has nothing to show.
     *
     * @param int $i Column number
     * @param array $social The parsed social links
     * @param \context $context
     * @return array|null
     */
    private function column(int $i, array $social, \context $context): ?array {
        $config = fn(string $name): string => (string) get_config('theme_atrium', $name);
        $type = $config('footercol' . $i . 'type');
        $type = in_array($type, self::TYPES, true) ? $type : 'html';
        $column = [
            'type' => $type,
            'title' => format_string($config('footercol' . $i . 'title'), true, ['context' => $context]),
            'ishtml' => false,
            'ismenu' => false,
            'issocial' => false,
            'iscontact' => false,
        ];
        switch ($type) {
            case 'menu':
                $items = self::parse_menu($config('footercol' . $i . 'menu'));
                if (!$items) {
                    return null;
                }
                foreach ($items as &$item) {
                    $item['label'] = format_string($item['label'], true, ['context' => $context]);
                    $item['url'] = (new moodle_url($item['url']))->out(false);
                }
                $column['ismenu'] = true;
                $column['items'] = $items;
                break;
            case 'social':
                if (!$social) {
                    return null;
                }
                $column['issocial'] = true;
                $column['social'] = $social;
                break;
            case 'contact':
                $address = trim($config('footercontactaddress'));
                $phone = trim($config('footercontactphone'));
                $email = trim($config('footercontactemail'));
                $email = validate_email($email) ? $email : '';
                if ($address === '' && $phone === '' && $email === '') {
                    return null;
                }
                $column['iscontact'] = true;
                $column['address'] = $address === '' ? '' : nl2br(s($address));
                $column['phone'] = $phone;
                $column['phoneurl'] = 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
                $column['email'] = $email;
                break;
            default:
                $html = $config('footercol' . $i . 'html');
                if (trim($column['title']) === '' && trim(strip_tags($html)) === '') {
                    return null;
                }
                $column['ishtml'] = true;
                $column['html'] = format_text($html, FORMAT_HTML, ['context' => $context, 'noclean' => true]);
        }
        return $column;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $PAGE, $SITE;
        $context = \context_system::instance();
        $config = fn(string $name): string => (string) get_config('theme_atrium', $name);
        $social = self::parse_social_links($config('sociallinks'));

        $columns = [];
        $socialincolumn = false;
        $wanted = min(self::MAX_COLUMNS, (int) $config('footercolumns'));
        for ($i = 1; $i <= $wanted; $i++) {
            $column = $this->column($i, $social, $context);
            if ($column !== null) {
                $columns[] = $column;
                $socialincolumn = $socialincolumn || $column['issocial'];
            }
        }

        $legal = $config('footerlegal');
        if (trim($legal) === '') {
            $legal = get_string('footerlegal_default', 'theme_atrium');
        }
        $sitename = format_string($SITE->fullname, true, ['context' => $context]);
        $legal = str_replace(['{year}', '{sitename}'], [userdate(time(), '%Y'), $sitename], $legal);

        $links = [];
        foreach (['privacy' => 'footerprivacyurl', 'terms' => 'footertermsurl'] as $key => $name) {
            $url = trim($config($name));
            if ($url !== '' && preg_match('#^(https?://|/)#i', $url)) {
                $links[] = [
                    'label' => get_string('footer_' . $key, 'theme_atrium'),
                    'url' => (new moodle_url($url))->out(false),
                ];
            }
        }

        $poweredby = get_config('theme_atrium', 'showpoweredby');
        return [
            'columns' => $columns,
            'hascolumns' => !empty($columns),
            'columnclass' => 'atrium-footer-columns-' . max(1, count($columns)),
            'social' => $social,
            'hassocial' => !empty($social) && !$socialincolumn,
            'legal' => format_string($legal, true, ['context' => $context]),
            'links' => $links,
            'haslinks' => !empty($links),
            'logourl' => (string) $PAGE->theme->setting_file_url('footerlogo', 'footerlogo'),
            'sitename' => $sitename,
            'showpoweredby' => $poweredby === false || $poweredby === '' ? true : (bool) $poweredby,
        ];
    }
}
