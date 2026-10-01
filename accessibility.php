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
 * The site's accessibility statement.
 *
 * Deliberately readable without signing in. A statement exists so that someone who cannot
 * use the site can find out what to expect and how to complain, and putting it behind the
 * login page would defeat that. It shows nothing that is not already public: the site
 * name, the administrator's own words, and which accessibility features are switched on.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\accessibility_statement;

require(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(accessibility_statement::url());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('a11ystatement', 'theme_atrium'));
$PAGE->set_heading(get_string('a11ystatement', 'theme_atrium'));

if (!accessibility_statement::enabled()) {
    throw new moodle_exception('a11ystatement_off', 'theme_atrium', (new moodle_url('/'))->out(false));
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_atrium/accessibility_statement', accessibility_statement::export());
echo $OUTPUT->footer();
