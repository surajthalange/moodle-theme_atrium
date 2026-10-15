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

namespace theme_atrium\external;

use core\context\system as context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_text;
use moodle_exception;
use theme_atrium\local\palette;

/**
 * Search the command palette.
 *
 * The palette answers a keystroke, so this is a read with no side effects. Everything it
 * returns comes from Moodle's own APIs asked with the caller's permissions; the course id
 * in particular is a request to look inside that course, and palette::activities() decides
 * whether the caller may.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_palette extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'The text typed into the palette', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_INT, 'The course the palette was opened in, or 0', VALUE_DEFAULT, 0),
            'page' => new external_value(
                PARAM_LOCALURL,
                'The page the palette was opened from, so that commands about this page are offered',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    /**
     * Search.
     *
     * @param string $query
     * @param int $courseid
     * @param string $page
     * @return array
     */
    public static function execute(string $query = '', int $courseid = 0, string $page = ''): array {
        global $PAGE;

        [
            'query' => $query,
            'courseid' => $courseid,
            'page' => $page,
        ] = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'courseid' => $courseid,
            'page' => $page,
        ]);

        self::validate_context(context_system::instance());

        if (isguestuser() || !palette::enabled()) {
            throw new moodle_exception('noguest');
        }

        // Whether editing applies is a question for the page the palette was opened from,
        // so $PAGE has to be that page rather than this endpoint. PARAM_LOCALURL has
        // already kept it on this site.
        if ($page !== '') {
            $PAGE->set_url($page);
        }

        return ['groups' => palette::search(core_text::substr($query, 0, 100), $courseid, $page)];
    }

    /**
     * Return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'groups' => new external_multiple_structure(
                new external_single_structure([
                    'title' => new external_value(PARAM_TEXT, 'The heading above this set of results'),
                    'items' => new external_multiple_structure(
                        new external_single_structure([
                            'label' => new external_value(PARAM_TEXT, 'What the result is called'),
                            'url' => new external_value(PARAM_URL, 'Where it goes'),
                            'icon' => new external_value(PARAM_NOTAGS, 'A Font Awesome class, or empty'),
                            'meta' => new external_value(PARAM_TEXT, 'A second line of context, or empty'),
                            'action' => new external_value(
                                PARAM_ALPHANUMEXT,
                                'The command id when this result runs a command, otherwise empty'
                            ),
                        ])
                    ),
                ])
            ),
        ]);
    }
}
