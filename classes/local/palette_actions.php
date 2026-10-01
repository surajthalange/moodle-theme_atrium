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
 * The commands the palette can run, as opposed to the places it can take you.
 *
 * Every command here is something the user can already do with the mouse: a preference of
 * their own, or an operation their role already permits. Nothing grants a capability, and
 * each one is checked again at the point it runs, not only when the list is built, so a
 * stale palette cannot be replayed into an action the user has since lost.
 *
 * Commands with a core endpoint (edit mode, signing out) are offered as links to that
 * endpoint, because core already does the checking and redirecting correctly. The rest
 * post to action.php.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class palette_actions {
    /**
     * Every command available to the current user, in the order they should be offered.
     *
     * @param string $pageurl The page the user is on, already validated as local, or ''.
     * @return array<int, array{id: string, label: string, icon: string, url: string, post: bool}>
     */
    public static function available(string $pageurl = ''): array {
        global $PAGE, $USER;

        $actions = [];

        // Edit mode, through core's own endpoint so that core decides what editing means here.
        if ($pageurl !== '' && $PAGE->user_allowed_editing()) {
            $editing = !empty($USER->editing);
            $actions[] = [
                'id' => 'editmode',
                'label' => get_string($editing ? 'turneditingoff' : 'turneditingon'),
                'icon' => 'fa-pen-to-square',
                'url' => (new moodle_url('/editmode.php', [
                    'setmode' => $editing ? 0 : 1,
                    'pageurl' => $pageurl,
                    'sesskey' => sesskey(),
                ]))->out(false),
                'post' => false,
            ];
        }

        if (scheme::can_toggle()) {
            $current = scheme::resolve();
            if ($current !== scheme::DARK) {
                $actions[] = self::post('scheme_dark', get_string('switchtodark', 'theme_atrium'), 'fa-moon');
            }
            if ($current !== scheme::LIGHT) {
                $actions[] = self::post('scheme_light', get_string('switchtolight', 'theme_atrium'), 'fa-sun');
            }
        }

        if (focusmode::applies()) {
            $on = focusmode::on();
            $actions[] = self::post(
                $on ? 'focus_off' : 'focus_on',
                get_string($on ? 'action_focus_off' : 'action_focus_on', 'theme_atrium'),
                'fa-compress'
            );
        }

        if (accessibility::wanted()) {
            $state = accessibility::state();
            if ($state['textsize'] === 'normal') {
                $actions[] = self::post('a11y_text_large', get_string('action_text_large', 'theme_atrium'), 'fa-text-height');
            } else {
                $actions[] = self::post('a11y_text_normal', get_string('action_text_normal', 'theme_atrium'), 'fa-text-height');
            }
            $actions[] = self::post(
                'a11y_font',
                get_string($state['font'] ? 'action_font_off' : 'action_font_on', 'theme_atrium'),
                'fa-font'
            );
            $actions[] = self::post(
                'a11y_contrast',
                get_string($state['contrast'] ? 'action_contrast_off' : 'action_contrast_on', 'theme_atrium'),
                'fa-circle-half-stroke'
            );
            $actions[] = self::post(
                'a11y_motion',
                get_string($state['motion'] ? 'action_motion_off' : 'action_motion_on', 'theme_atrium'),
                'fa-person-running'
            );
            if ($state['textsize'] !== 'normal' || $state['font'] || $state['contrast'] || $state['motion']) {
                $actions[] = self::post('a11y_reset', get_string('action_a11y_reset', 'theme_atrium'), 'fa-rotate-left');
            }
        }

        if (is_siteadmin()) {
            // Not core's "Purge all caches": that string also labels the page of options,
            // which the palette already offers, and two identical labels help nobody.
            $actions[] = self::post('purgecaches', get_string('action_purgecaches', 'theme_atrium'), 'fa-broom');
        }

        if (isloggedin() && !isguestuser()) {
            $actions[] = [
                'id' => 'logout',
                'label' => get_string('logout'),
                'icon' => 'fa-right-from-bracket',
                'url' => (new moodle_url('/login/logout.php', ['sesskey' => sesskey()]))->out(false),
                'post' => false,
            ];
        }

        return $actions;
    }

    /**
     * Those commands whose label matches the query, or the first few when it is empty.
     *
     * @param string $query
     * @param string $pageurl
     * @param int $limit
     * @return array
     */
    public static function search(string $query, string $pageurl = '', int $limit = 6): array {
        $items = [];
        foreach (self::available($pageurl) as $action) {
            if ($query !== '' && !self::matches($action['label'], $query)) {
                continue;
            }
            $items[] = [
                'label' => $action['label'],
                'url' => $action['url'],
                'icon' => $action['icon'],
                'meta' => '',
                'action' => $action['post'] ? $action['id'] : '',
            ];
            if (count($items) === $limit) {
                break;
            }
        }
        // With no query the palette leads with where you can go, so keep commands short.
        return $query === '' ? array_slice($items, 0, 3) : $items;
    }

    /**
     * Run one command, having checked that it is still available to this user.
     *
     * @param string $id
     * @return bool Whether anything ran.
     */
    public static function execute(string $id): bool {
        if (!self::permitted($id)) {
            return false;
        }
        switch ($id) {
            case 'scheme_light':
                scheme::set(scheme::LIGHT);
                return true;
            case 'scheme_dark':
                scheme::set(scheme::DARK);
                return true;
            case 'focus_on':
                focusmode::set(true);
                return true;
            case 'focus_off':
                focusmode::set(false);
                return true;
            case 'a11y_text_large':
                accessibility::save(['textsize' => 'large']);
                return true;
            case 'a11y_text_normal':
                accessibility::save(['textsize' => 'normal']);
                return true;
            case 'a11y_font':
                accessibility::save(['font' => !accessibility::state()['font']]);
                return true;
            case 'a11y_contrast':
                accessibility::save(['contrast' => !accessibility::state()['contrast']]);
                return true;
            case 'a11y_motion':
                accessibility::save(['motion' => !accessibility::state()['motion']]);
                return true;
            case 'a11y_reset':
                accessibility::reset();
                return true;
            case 'purgecaches':
                purge_all_caches();
                return true;
            default:
                return false;
        }
    }

    /**
     * Whether this user may run this command right now.
     *
     * Checked here rather than by looking the id up in available(), because that list
     * depends on current state: "switch to dark" drops out of it the moment you are in
     * dark mode, and re-running it must stay harmless rather than become forbidden.
     *
     * @param string $id
     * @return bool
     */
    private static function permitted(string $id): bool {
        if (!isloggedin() || isguestuser()) {
            return false;
        }
        switch ($id) {
            case 'scheme_light':
            case 'scheme_dark':
                return scheme::can_toggle();
            case 'focus_on':
            case 'focus_off':
                return focusmode::applies();
            case 'a11y_text_large':
            case 'a11y_text_normal':
            case 'a11y_font':
            case 'a11y_contrast':
            case 'a11y_motion':
            case 'a11y_reset':
                return accessibility::wanted();
            case 'purgecaches':
                return is_siteadmin();
            default:
                return false;
        }
    }

    /**
     * A command that posts to action.php.
     *
     * @param string $id
     * @param string $label
     * @param string $icon
     * @return array
     */
    private static function post(string $id, string $label, string $icon): array {
        return ['id' => $id, 'label' => $label, 'icon' => $icon, 'url' => '', 'post' => true];
    }

    /**
     * Case-insensitive "contains".
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private static function matches(string $haystack, string $needle): bool {
        return \core_text::strpos(\core_text::strtolower($haystack), \core_text::strtolower($needle)) !== false;
    }
}
