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

/**
 * The login layout: one card centred over a full-bleed image.
 *
 * Copied from theme/boost/layout/login.php as of Moodle 5.2. The instructions panel
 * context is only built on 5.2 and later: earlier Boost shows the instructions inside
 * the form at every width, so rendering them beside the card too would show them twice.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$bodyattributes = $OUTPUT->body_attributes();

$leftinstructions = null;
if ($CFG->branch >= 502 && !empty($CFG->auth_instructions)) {
    $leftinstructions = format_text($CFG->auth_instructions, FORMAT_MOODLE, ['context' => context_system::instance()]);
}

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'leftinstructions' => $leftinstructions,
];

echo $OUTPUT->render_from_template('theme_boost/login', $templatecontext);
