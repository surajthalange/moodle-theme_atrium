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
 * Dismiss the site announcement for the current user, then go back.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\announcement;

require(__DIR__ . '/../../config.php');

$hash = required_param('hash', PARAM_ALPHANUM);

require_login(null, false);
require_sesskey();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

// Only the announcement that is actually showing can be dismissed.
if ($hash === announcement::hash()) {
    announcement::dismiss();
}

if (AJAX_SCRIPT || optional_param('ajax', 0, PARAM_BOOL)) {
    echo json_encode(['dismissed' => true]);
    exit;
}
redirect(new moodle_url(get_local_referer(false) ?: '/'));
