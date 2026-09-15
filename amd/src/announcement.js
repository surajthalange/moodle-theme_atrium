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
 * Dismiss the announcement bar without a page load.
 *
 * The dismiss control is a form that posts to announcement.php, which works with
 * JavaScript off. Here the submit is intercepted, the bar removed at once, and the
 * same endpoint called in the background.
 *
 * @module     theme_atrium/announcement
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';

const SELECTORS = {
    BAR: '[data-region="atrium-announcement"]',
    FORM: '.atrium-announcement-dismiss',
};

let initialised = false;

/**
 * Intercept the dismiss form, once.
 */
export const init = () => {
    if (initialised) {
        return;
    }
    initialised = true;

    document.addEventListener('submit', (event) => {
        const form = event.target.closest(SELECTORS.FORM);
        if (!form) {
            return;
        }
        event.preventDefault();
        const bar = form.closest(SELECTORS.BAR);
        if (bar) {
            bar.remove();
        }
        const body = new FormData(form);
        body.append('ajax', '1');
        fetch(form.action, {method: 'POST', body, credentials: 'same-origin'}).catch(Log.error);
    });
};
