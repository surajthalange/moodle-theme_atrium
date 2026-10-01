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
 * Command palette search, as JSON.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\palette;

define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

$query = optional_param('q', '', PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
// The page the palette was opened from. PARAM_LOCALURL keeps it on this site; it is used
// to return here after a command and to let core decide whether editing applies there.
$pageurl = optional_param('page', '', PARAM_LOCALURL);

require_login(null, false);
require_sesskey();
if (isguestuser() || !palette::enabled()) {
    throw new moodle_exception('noguest');
}
$PAGE->set_context(context_system::instance());
if ($pageurl !== '') {
    // Whether editing is allowed is a question for the page itself, so $PAGE has to be
    // set to the page the palette was opened from rather than to this endpoint.
    $PAGE->set_url($pageurl);
}

echo json_encode([
    'groups' => palette::search(core_text::substr($query, 0, 100), $courseid, $pageurl),
    'actionurl' => (new moodle_url('/theme/atrium/paletteaction.php'))->out(false),
    'sesskey' => sesskey(),
]);
