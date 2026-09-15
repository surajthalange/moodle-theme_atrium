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

namespace theme_atrium\output\core_user\myprofile;

use core_user\output\myprofile\category;
use core_user\output\myprofile\node;
use core_user\output\myprofile\tree;

/**
 * Profile page renderer: the profile tree as a grid of cards with an icon per section.
 *
 * Node rendering is core's; only the tree and category wrappers change, so every
 * plugin that adds a node to the profile keeps working.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \core_user\output\myprofile\renderer {
    /** @var array<string, string> Category name => Font Awesome icon class. */
    private const ICONS = [
        'contact' => 'fa-address-card',
        'coursedetails' => 'fa-graduation-cap',
        'miscellaneous' => 'fa-shapes',
        'reports' => 'fa-chart-line',
        'administration' => 'fa-gear',
        'loginactivity' => 'fa-clock-rotate-left',
        'badges' => 'fa-award',
        'privacyandpolicies' => 'fa-shield-halved',
        'mobile' => 'fa-mobile-screen',
    ];

    /**
     * The tree as a card grid.
     *
     * @param tree $tree
     * @return string
     */
    public function render_tree(tree $tree) {
        $cards = [];
        foreach ($tree->categories as $category) {
            $html = $this->render($category);
            if ($html !== '') {
                $cards[] = $html;
            }
        }
        return $this->render_from_template('theme_atrium/profile', ['cards' => $cards, 'hascards' => !empty($cards)]);
    }

    /**
     * One category as a card.
     *
     * @param category $category
     * @return string
     */
    public function render_category(category $category) {
        $nodes = $category->nodes;
        if (empty($nodes)) {
            return '';
        }
        $items = [];
        foreach ($nodes as $node) {
            $items[] = $this->render($node);
        }
        return $this->render_from_template('theme_atrium/profile_category', [
            'name' => (string) $category->name,
            'title' => $category->title,
            'icon' => self::ICONS[(string) $category->name] ?? 'fa-circle-info',
            'classes' => (string) $category->classes,
            'items' => $items,
        ]);
    }
}
