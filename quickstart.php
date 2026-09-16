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
 * Quick start: apply or reset the settings that make a fresh site look finished.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\quickstart;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$action = optional_param('action', '', PARAM_ALPHA);

admin_externalpage_setup('theme_atrium_quickstart');
$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'themesettingatrium']);
$homeurl = new moodle_url('/', ['redirect' => 0]);

if ($action === 'apply' || $action === 'reset') {
    require_sesskey();
    if ($action === 'apply') {
        quickstart::apply();
        $message = get_string('quickstart_applied', 'theme_atrium');
    } else {
        quickstart::reset();
        $message = get_string('quickstart_resetdone', 'theme_atrium');
    }
    redirect($homeurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('quickstart', 'theme_atrium'));
echo $OUTPUT->render_from_template('theme_atrium/quickstart', [
    'intro' => get_string('quickstart_intro', 'theme_atrium'),
    'items' => [
        get_string('quickstart_item_frontpage', 'theme_atrium'),
        get_string('quickstart_item_footer', 'theme_atrium'),
        get_string('quickstart_item_quicklinks', 'theme_atrium'),
        get_string('quickstart_item_login', 'theme_atrium'),
        get_string('quickstart_item_course', 'theme_atrium'),
    ],
    'note' => get_string('quickstart_note', 'theme_atrium'),
    'applyurl' => (new moodle_url('/theme/atrium/quickstart.php', ['action' => 'apply', 'sesskey' => sesskey()]))->out(false),
    'reseturl' => (new moodle_url('/theme/atrium/quickstart.php', ['action' => 'reset', 'sesskey' => sesskey()]))->out(false),
    'settingsurl' => $settingsurl->out(false),
]);
echo $OUTPUT->footer();
