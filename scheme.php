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
 * Switch colour scheme without JavaScript: save the preference, go back.
 *
 * The switches in the navigation bar and the user menu link here; scheme.js intercepts
 * the click when it can and calls the same preference through the web service instead.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\scheme;

require(__DIR__ . '/../../config.php');

$target = required_param('scheme', PARAM_ALPHA);
$returnurl = optional_param('returnurl', '/', PARAM_LOCALURL);

require_login(null, false);
require_sesskey();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

if (scheme::enabled()) {
    scheme::set($target);
}

redirect(new moodle_url($returnurl));
