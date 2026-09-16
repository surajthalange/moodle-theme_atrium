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
 * The accessibility toolbar: apply each change the moment it is made.
 *
 * The panel is a form that posts to a11y.php, which works with JavaScript off. Here
 * every change swaps the body classes at once and saves in the background, so there is
 * no page load and the Save button is only needed without JavaScript.
 *
 * @module     theme_atrium/a11y
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';

const SELECTORS = {
    FORM: '[data-region="atrium-a11y-form"]',
    SAVE: '[data-region="atrium-a11y-save"]',
};

const CLASSES = ['atrium-text-large', 'atrium-text-larger', 'atrium-font-reading', 'atrium-contrast', 'atrium-reduce-motion'];

/**
 * Body classes for the form's current values.
 *
 * @param {HTMLFormElement} form
 * @returns {string[]}
 */
const classesFor = (form) => {
    const data = new FormData(form);
    const classes = [];
    const size = data.get('textsize');
    if (size && size !== 'normal') {
        classes.push('atrium-text-' + size);
    }
    if (data.get('font')) {
        classes.push('atrium-font-reading');
    }
    if (data.get('contrast')) {
        classes.push('atrium-contrast');
    }
    if (data.get('motion')) {
        classes.push('atrium-reduce-motion');
    }
    return classes;
};

/**
 * Apply and save the form's values.
 *
 * @param {HTMLFormElement} form
 */
const apply = (form) => {
    document.body.classList.remove(...CLASSES);
    document.body.classList.add(...classesFor(form));
    const body = new FormData(form);
    body.append('ajax', '1');
    fetch(form.action, {method: 'POST', body, credentials: 'same-origin'}).catch(Log.error);
};

/**
 * Wire the toolbar, once.
 */
export const init = () => {
    const form = document.querySelector(SELECTORS.FORM);
    if (!form || form.dataset.initialised) {
        return;
    }
    form.dataset.initialised = '1';
    const save = form.querySelector(SELECTORS.SAVE);
    if (save) {
        save.hidden = true;
    }
    form.addEventListener('change', () => apply(form));
    form.addEventListener('submit', (event) => {
        // The reset button: clear the form, apply, and save the reset in the background.
        if (event.submitter && event.submitter.name === 'reset') {
            event.preventDefault();
            form.querySelector('input[name="textsize"][value="normal"]').checked = true;
            form.querySelectorAll('input[type="checkbox"]').forEach((box) => {
                box.checked = false;
            });
            document.body.classList.remove(...CLASSES);
            const body = new FormData(form);
            body.append('reset', '1');
            body.append('ajax', '1');
            fetch(form.action, {method: 'POST', body, credentials: 'same-origin'}).catch(Log.error);
        }
    });
};
