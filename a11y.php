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
 * Save the accessibility toolbar choices, then go back.
 *
 * Works without JavaScript (a plain form post); theme_atrium/a11y calls it in the
 * background with ajax=1.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\accessibility;

require(__DIR__ . '/../../config.php');

$returnurl = optional_param('returnurl', '/', PARAM_LOCALURL);
$reset = optional_param('reset', 0, PARAM_BOOL);
$ajax = optional_param('ajax', 0, PARAM_BOOL);

require_login(null, false);
require_sesskey();
if (isguestuser() || !accessibility::enabled()) {
    throw new moodle_exception('noguest');
}

if ($reset) {
    accessibility::reset();
} else {
    accessibility::save([
        'textsize' => optional_param('textsize', null, PARAM_ALPHA),
        'font' => optional_param('font', 0, PARAM_BOOL),
        'contrast' => optional_param('contrast', 0, PARAM_BOOL),
        'motion' => optional_param('motion', 0, PARAM_BOOL),
    ]);
}

if ($ajax) {
    @header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['classes' => accessibility::body_classes()]);
    exit;
}
redirect(new moodle_url($returnurl ?: '/'));
