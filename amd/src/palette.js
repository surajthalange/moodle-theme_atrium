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
 * that finds courses, activities in the current course, common pages and admin pages.
 *
 * Results come from palette.php. Keyboard: arrows move, Enter opens, Escape closes; the
 * results are a listbox and the input a combobox, so screen readers announce the
 * highlighted result.
 *
 * @module     theme_atrium/palette
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';
import {getString} from 'core/str';

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
 * Escape text for HTML.
 *
 * @param {string} text
 * @returns {string}
 */
const escape = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

/**
 * Render the result groups.
 *
 * @param {Array} groups
 */
const render = async(groups) => {
    active = -1;
    if (!groups.length) {
        const empty = await getString('palette_empty', 'theme_atrium');
        results.innerHTML = `<p class="atrium-palette-empty">${escape(empty)}</p>`;
        return;
    }
    let index = 0;
    results.innerHTML = groups.map((group) => {
        const items = group.items.map((item) => {
            const icon = item.icon
                ? `<i class="fa ${escape(item.icon)}" aria-hidden="true"></i>`
                : '<span class="atrium-palette-dot" aria-hidden="true"></span>';
            const meta = item.meta ? `<span class="atrium-palette-meta">${escape(item.meta)}</span>` : '';
            const id = `atrium-palette-option-${index++}`;
            return `<a href="${escape(item.url)}" class="atrium-palette-item" role="option" id="${id}" aria-selected="false">
                ${icon}<span class="atrium-palette-label">${escape(item.label)}</span>${meta}</a>`;
        }).join('');
        return `<div class="atrium-palette-group" role="group" aria-label="${escape(group.title)}">
            <p class="atrium-palette-group-title">${escape(group.title)}</p>${items}</div>`;
    }).join('');
    setActive(0);
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
 * Fetch results for the current query.
 */
const search = () => {
    const id = ++requestId;
    const url = new URL(root.dataset.searchurl, window.location.href);
    url.searchParams.set('q', input.value);
    url.searchParams.set('courseid', root.dataset.courseid || '0');
    fetch(url, {credentials: 'same-origin'})
        .then((response) => response.json())
        .then((data) => {
            if (id === requestId) {
                render(data.groups || []);
            }
            return null;
        })
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
                window.location.href = option.href;
            }
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
