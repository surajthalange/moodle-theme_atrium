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

namespace theme_atrium\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\user_preference_provider;
use core_privacy\local\request\writer;
use theme_atrium\local\announcement;
use theme_atrium\local\catalogue;
use theme_atrium\local\focusmode;
use theme_atrium\local\scheme;
use theme_atrium\output\sidebar;

/**
 * Privacy provider: five user preferences, nothing else.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, user_preference_provider {
    /**
     * Describe the preferences.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference(scheme::PREFERENCE, 'privacy:metadata:preference:theme_atrium_scheme');
        $collection->add_user_preference(sidebar::PREFERENCE, 'privacy:metadata:preference:theme_atrium_sidebar');
        $collection->add_user_preference(catalogue::VIEW_PREFERENCE, 'privacy:metadata:preference:theme_atrium_catalogueview');
        $collection->add_user_preference(focusmode::PREFERENCE, 'privacy:metadata:preference:theme_atrium_focusmode');
        $collection->add_user_preference(announcement::PREFERENCE, 'privacy:metadata:preference:theme_atrium_announcement');
        return $collection;
    }

    /**
     * Export a user's preferences.
     *
     * @param int $userid
     */
    public static function export_user_preferences(int $userid): void {
        $preferences = [
            scheme::PREFERENCE => ['light' => 'scheme:light', 'dark' => 'scheme:dark', 'system' => 'scheme:system'],
            sidebar::PREFERENCE => ['expanded' => 'sidebar:expanded', 'collapsed' => 'sidebar:collapsed'],
            catalogue::VIEW_PREFERENCE => ['grid' => 'catalogue_view_grid', 'list' => 'catalogue_view_list'],
            focusmode::PREFERENCE => ['1' => 'focus_on', '0' => 'focus_off'],
            announcement::PREFERENCE => [],
        ];
        foreach ($preferences as $name => $labels) {
            $value = get_user_preferences($name, null, $userid);
            if ($value === null) {
                continue;
            }
            $description = isset($labels[$value]) ? get_string($labels[$value], 'theme_atrium') : $value;
            writer::export_user_preference('theme_atrium', $name, $value, $description);
        }
    }
}
