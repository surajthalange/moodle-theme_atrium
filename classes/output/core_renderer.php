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

namespace theme_atrium\output;

/**
 * Core renderer: adds the one method the footer template needs and nothing else.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * The configurable footer content (columns, social links, legal line).
     *
     * Available to every layout that includes theme_boost/footer, including the login
     * page, without each layout having to build it. Empty on the maintenance layout,
     * which must not read configuration.
     *
     * @return string
     */
    public function atrium_footer(): string {
        if ($this->page->pagelayout === 'maintenance' || during_initial_install()) {
            return '';
        }
        return $this->render_from_template('theme_atrium/footer', (new footer())->export_for_template($this));
    }
}
