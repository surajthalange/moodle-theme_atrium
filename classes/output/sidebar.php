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
 * The left navigation sidebar.
 *
 * Its items are core's primary navigation exactly as the more-menu exports them (Home,
 * Dashboard, My courses, Site administration, then the custom menu), so visibility rules,
 * ordering and active-state detection are core's. The sidebar only adds an icon per item
 * and remembers whether the user last left it expanded or collapsed.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sidebar implements renderable, templatable {
    /** @var string The user preference that remembers the state. */
    public const PREFERENCE = 'theme_atrium_sidebar';

    /** @var string */
    public const EXPANDED = 'expanded';

    /** @var string */
    public const COLLAPSED = 'collapsed';

    /** @var array<string, array{0: string, 1: string}> Primary node key => [pix key, component]. */
    private const ICONS = [
        'home' => ['i/home', 'core'],
        'myhome' => ['i/dashboard', 'core'],
        'courses' => ['i/course', 'core'],
        'siteadminnode' => ['i/settings', 'core'],
    ];

    /**
     * Constructor.
     *
     * @param array $nodes The primary navigation's more-menu node array.
     * @param bool $collapsed Whether to render collapsed to icons.
     */
    public function __construct(
        /** @var array The primary navigation nodes. */
        protected array $nodes,
        /** @var bool Whether the sidebar starts collapsed. */
        protected bool $collapsed,
    ) {
    }

    /**
     * Whether the sidebar is shown at all: only to real users. A visitor's primary
     * navigation is one item, and a marketing front page reads better without a rail.
     *
     * @return bool
     */
    public static function wanted(): bool {
        return isloggedin() && !isguestuser();
    }

    /**
     * Whether the current user's sidebar should start collapsed.
     *
     * The user's own choice wins; otherwise the site setting for first visits.
     *
     * @return bool
     */
    public static function starts_collapsed(): bool {
        if (isloggedin() && !isguestuser()) {
            $state = get_user_preferences(self::PREFERENCE);
            if ($state === self::COLLAPSED || $state === self::EXPANDED) {
                return $state === self::COLLAPSED;
            }
        }
        return get_config('theme_atrium', 'sidebardefault') === self::COLLAPSED;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG, $SITE;

        $items = [];
        foreach ($this->nodes as $node) {
            $items[] = $this->export_node($node);
        }

        return [
            'homeurl' => (new \moodle_url('/'))->out(false),
            'sitename' => format_string($SITE->shortname, true, ['context' => \context_course::instance(SITEID)]),
            'haslogo' => $output->should_display_navbar_logo(),
            'logourl' => $output->get_compact_logo_url(300, 44),
            'collapsed' => $this->collapsed,
            'items' => $items,
            'hasitems' => !empty($items),
            'wwwroot' => $CFG->wwwroot,
        ];
    }

    /**
     * One navigation node, with an icon chosen by its core key.
     *
     * @param array $node
     * @return array
     */
    private function export_node(array $node): array {
        $key = $node['key'] ?? '';
        [$pix, $component] = self::ICONS[$key] ?? ['link', 'theme_atrium'];
        $children = [];
        foreach ($node['children'] ?? [] as $child) {
            if (!empty($child['divider'])) {
                continue;
            }
            $children[] = [
                'text' => $child['text'],
                'url' => (string) ($child['url'] ?? '#'),
                'isactive' => !empty($child['isactive']),
            ];
        }
        return [
            'key' => $key,
            'text' => $node['text'],
            'title' => $node['title'] ?? $node['text'],
            'url' => (string) ($node['url'] ?? '#'),
            'isactive' => !empty($node['isactive']),
            'iconkey' => $pix,
            'iconcomponent' => $component,
            'haschildren' => !empty($children),
            'children' => $children,
            'id' => 'atrium-nav-' . ($key !== '' ? $key : substr(md5($node['text']), 0, 8)),
        ];
    }
}
