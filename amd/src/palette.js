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
 * The command palette: Ctrl+K (Cmd+K on a Mac) or the navigation bar button opens a box
 * that finds courses, activities in the current course, common pages and admin pages, and
 * runs commands such as turning editing on, switching colour scheme or purging caches.
 *
 * Results come from the theme_atrium_search_palette web service and are rendered through
 * theme_atrium/palette_results. Keyboard: arrows move, Enter opens, Escape closes; the
 * results are a listbox and the input a combobox, so screen readers announce the
 * highlighted result.
 *
 * A place to go is a link and is followed. A command is a button, and activating it posts
 * to paletteaction.php with the session key, because a command changes something and so
 * must never be reachable by following a URL.
 *
 * @module     theme_atrium/palette
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Config from 'core/config';
import Log from 'core/log';
import Templates from 'core/templates';

const SELECTORS = {
    ROOT: '[data-region="atrium-palette"]',
    OPEN: '[data-action="atrium-palette-open"]',
    CLOSE: '[data-action="atrium-palette-close"]',
    INPUT: '[data-region="atrium-palette-input"]',
    RESULTS: '[data-region="atrium-palette-results"]',
    OPTION: '[role="option"]',
};

let root = null;
let input = null;
let results = null;
let lastFocus = null;
let timer = null;
let requestId = 0;
let active = -1;

/**
 * Render the result groups.
 *
 * The option ids are numbered here rather than in the template, because the count runs
 * across groups and aria-activedescendant has to point at one of them.
 *
 * @param {Array} groups
 * @returns {Promise}
 */
const render = (groups) => {
    active = -1;
    let index = 0;
    const context = {
        hasresults: groups.length > 0,
        groups: groups.map((group) => ({
            title: group.title,
            items: group.items.map((item) => ({
                ...item,
                id: `atrium-palette-option-${index++}`,
                iscommand: Boolean(item.action),
            })),
        })),
    };
    return Templates.renderForPromise('theme_atrium/palette_results', context)
        .then(({html, js}) => {
            Templates.replaceNodeContents(results, html, js);
            setActive(0);
            return null;
        });
};

/**
 * Highlight one result.
 *
 * @param {number} next
 */
const setActive = (next) => {
    const options = results.querySelectorAll(SELECTORS.OPTION);
    if (!options.length) {
        return;
    }
    if (active >= 0 && options[active]) {
        options[active].setAttribute('aria-selected', 'false');
        options[active].classList.remove('active');
    }
    active = (next + options.length) % options.length;
    options[active].setAttribute('aria-selected', 'true');
    options[active].classList.add('active');
    options[active].scrollIntoView({block: 'nearest'});
    input.setAttribute('aria-activedescendant', options[active].id);
};

/**
 * The page the palette was opened from, as a site-local path.
 *
 * @returns {string}
 */
const here = () => window.location.pathname + window.location.search;

/**
 * Run a command by posting it, so that it carries the session key and cannot be triggered
 * by following a link. The server does the work and sends the user back here.
 *
 * @param {string} action
 */
const run = (action) => {
    const actionurl = root.dataset.actionurl;
    if (!actionurl) {
        return;
    }
    const form = document.createElement('form');
    form.method = 'post';
    form.action = actionurl;
    form.hidden = true;
    [['action', action], ['sesskey', Config.sesskey], ['returnurl', here()]].forEach(([name, value]) => {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        field.value = value;
        form.appendChild(field);
    });
    document.body.appendChild(form);
    form.submit();
};

/**
 * Activate one result: follow a place, run a command.
 *
 * @param {Element} option
 */
const choose = (option) => {
    const action = option.dataset.paletteAction;
    if (action) {
        run(action);
    } else if (option.href) {
        window.location.href = option.href;
    }
};

/**
 * Fetch results for the current query.
 */
const search = () => {
    const id = ++requestId;
    Ajax.call([{
        methodname: 'theme_atrium_search_palette',
        args: {
            query: input.value,
            courseid: parseInt(root.dataset.courseid || '0', 10),
            page: here(),
        },
    }])[0]
        // Keystrokes outrun replies, so an answer to a query the user has already typed
        // past is dropped rather than painted over the newer one.
        .then((data) => (id === requestId ? render(data.groups || []) : null))
        .catch(Log.error);
};

/**
 * Open the palette.
 */
const open = () => {
    if (!root.hidden) {
        return;
    }
    lastFocus = document.activeElement;
    root.hidden = false;
    document.body.classList.add('atrium-palette-open');
    input.value = '';
    input.focus();
    search();
};

/**
 * Close the palette.
 */
const close = () => {
    if (root.hidden) {
        return;
    }
    root.hidden = true;
    document.body.classList.remove('atrium-palette-open');
    if (lastFocus && typeof lastFocus.focus === 'function') {
        lastFocus.focus();
    }
};

/**
 * Wire the palette, once.
 */
export const init = () => {
    root = document.querySelector(SELECTORS.ROOT);
    if (!root || root.dataset.initialised) {
        return;
    }
    root.dataset.initialised = '1';
    input = root.querySelector(SELECTORS.INPUT);
    results = root.querySelector(SELECTORS.RESULTS);

    document.addEventListener('click', (event) => {
        if (event.target.closest(SELECTORS.OPEN)) {
            event.preventDefault();
            open();
        } else if (event.target.closest(SELECTORS.CLOSE)) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && !event.shiftKey && !event.altKey && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            if (root.hidden) {
                open();
            } else {
                close();
            }
        } else if (event.key === 'Escape' && !root.hidden) {
            event.preventDefault();
            close();
        }
    });

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(search, 180);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(active + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(active - 1);
        } else if (event.key === 'Enter') {
            const option = results.querySelectorAll(SELECTORS.OPTION)[active];
            if (option) {
                event.preventDefault();
                choose(option);
            }
        }
    });

    results.addEventListener('click', (event) => {
        // Links navigate by themselves; commands are buttons and need running.
        const option = event.target.closest('[data-palette-action]');
        if (option) {
            event.preventDefault();
            choose(option);
        }
    });

    results.addEventListener('mousemove', (event) => {
        const option = event.target.closest(SELECTORS.OPTION);
        if (option) {
            const options = Array.from(results.querySelectorAll(SELECTORS.OPTION));
            setActive(options.indexOf(option));
        }
    });
};
