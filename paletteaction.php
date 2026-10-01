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
 * Run one command from the palette, then return to the page it was run from.
 *
 * Posted, with a session key, and the command is checked against the user's own
 * permissions again here rather than trusted from the palette that offered it.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\palette;
use theme_atrium\local\palette_actions;

require(__DIR__ . '/../../config.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$returnurl = optional_param('returnurl', '/', PARAM_LOCALURL);

require_login(null, false);
require_sesskey();
if (isguestuser() || !palette::enabled()) {
    throw new moodle_exception('noguest');
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/theme/atrium/paletteaction.php');

palette_actions::execute($action);

// PARAM_LOCALURL keeps this on this site; an empty or rejected value falls back to the home page.
redirect(new moodle_url($returnurl === '' ? '/' : $returnurl));
