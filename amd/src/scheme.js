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
 * Switch between the light and dark colour schemes without a page load.
 *
 * The switches are plain links to scheme.php, which works with JavaScript off. Here a
 * click is intercepted, the data-bs-theme attribute is flipped at once, and the same
 * preference scheme.php would write is saved through the web service.
 *
 * @module     theme_atrium/scheme
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {setUserPreference} from 'core_user/repository';
import {getStrings} from 'core/str';
import Log from 'core/log';

const SELECTORS = {
    NAVBAR_TOGGLE: '[data-action="atrium-scheme-toggle"]',
    ANY_TOGGLE: 'a[href*="/theme/atrium/scheme.php"]',
};

const PREFERENCE = 'theme_atrium_scheme';

let initialised = false;
let labels = null;

/**
 * The scheme in effect right now.
 *
 * @returns {string} light or dark
 */
const current = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';

/**
 * Rewrite a switch link so a later plain click, or a second toggle, goes the right way.
 *
 * @param {HTMLAnchorElement} link
 * @param {string} scheme The scheme now in effect
 */
const relabel = (link, scheme) => {
    const next = scheme === 'dark' ? 'light' : 'dark';
    const url = new URL(link.href, window.location.href);
    url.searchParams.set('scheme', next);
    link.href = url.toString();

    if (!labels) {
        return;
    }
    const label = labels[next];
    if (link.matches(SELECTORS.NAVBAR_TOGGLE)) {
        link.setAttribute('aria-label', label);
        link.setAttribute('title', label);
        return;
    }
    // The user menu item carries its text as content and a core icon.
    const text = [...link.childNodes].find((node) => node.nodeType === Node.TEXT_NODE && node.textContent.trim());
    if (text) {
        text.textContent = ' ' + label;
    }
    const icon = link.querySelector('.icon');
    if (icon) {
        icon.classList.toggle('fa-moon', next === 'dark');
        icon.classList.toggle('fa-sun', next === 'light');
    }
};

/**
 * Apply a scheme, tell the switches, and save it.
 *
 * @param {string} scheme light or dark
 */
const apply = (scheme) => {
    document.documentElement.setAttribute('data-bs-theme', scheme);
    document.documentElement.setAttribute('data-atrium-scheme', scheme);
    document.querySelectorAll(SELECTORS.ANY_TOGGLE).forEach((link) => relabel(link, scheme));
    setUserPreference(PREFERENCE, scheme).catch(Log.error);
};

/**
 * Intercept clicks on every scheme switch on the page, once.
 */
export const init = () => {
    if (initialised) {
        return;
    }
    initialised = true;

    if (!document.querySelector(SELECTORS.ANY_TOGGLE)) {
        return;
    }

    getStrings([
        {key: 'switchtolight', component: 'theme_atrium'},
        {key: 'switchtodark', component: 'theme_atrium'},
    ]).then(([light, dark]) => {
        labels = {light, dark};
        return labels;
    }).catch(Log.error);

    document.addEventListener('click', (event) => {
        const link = event.target.closest(SELECTORS.ANY_TOGGLE);
        if (!link) {
            return;
        }
        event.preventDefault();
        apply(current() === 'dark' ? 'light' : 'dark');
    });
};
