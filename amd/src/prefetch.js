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
 * Fetch the next page while the pointer is still on its way to the link.
 *
 * A reader who hovers a course or an activity usually opens it, and the gap between
 * hovering and clicking is long enough to have asked for the page already. The browser is
 * told with <link rel="prefetch">, so it fetches at idle priority, obeys the cache, and
 * throws the result away if the guess was wrong.
 *
 * What is never prefetched matters more than what is. A link carrying a session key is an
 * action, not a destination: this theme's own palette offers "turn editing on" and "log
 * out" that way, and fetching one in the background would perform it. Those are excluded
 * by rule rather than by listing them, so a link added later is excluded too.
 *
 * @module     theme_atrium/prefetch
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @type {number} How long the pointer must rest before the guess is worth making. */
const DWELL = 120;

/** @type {number} At most this many prefetches per page, so a long list cannot flood. */
const BUDGET = 8;

/** @type {Set<string>} URLs already asked for. */
const asked = new Set();

let timer = null;
let spent = 0;

/**
 * Whether this link is a destination that is safe and worth fetching early.
 *
 * @param {HTMLAnchorElement} link
 * @returns {boolean}
 */
const worthFetching = (link) => {
    if (!link || !link.href || spent >= BUDGET) {
        return false;
    }
    // Anything that opens elsewhere or downloads is not this page's next page.
    if (link.target && link.target !== '_self') {
        return false;
    }
    if (link.hasAttribute('download') || link.dataset.noPrefetch !== undefined) {
        return false;
    }

    let url;
    try {
        url = new URL(link.href, window.location.href);
    } catch (e) {
        return false;
    }
    if (url.origin !== window.location.origin) {
        return false;
    }
    if (!/^https?:$/.test(url.protocol)) {
        return false;
    }
    // Same page, or only a fragment away from it.
    if (url.pathname === window.location.pathname && url.search === window.location.search) {
        return false;
    }
    // A session key means the link does something. Never fetch one in the background.
    if (url.searchParams.has('sesskey')) {
        return false;
    }
    // Belt and braces for the few core endpoints that act without a key in the query.
    if (/\/login\/logout|\/course\/togglecompletion|\/editmode\.php/.test(url.pathname)) {
        return false;
    }
    return !asked.has(url.href);
};

/**
 * Ask the browser to fetch it at idle priority.
 *
 * @param {string} href
 */
const prefetch = (href) => {
    asked.add(href);
    spent += 1;
    const tag = document.createElement('link');
    tag.rel = 'prefetch';
    tag.href = href;
    document.head.appendChild(tag);
};

/**
 * Consider the link under the pointer.
 *
 * @param {Event} event
 */
const consider = (event) => {
    const link = event.target.closest ? event.target.closest('a[href]') : null;
    clearTimeout(timer);
    if (!worthFetching(link)) {
        return;
    }
    timer = setTimeout(() => prefetch(new URL(link.href, window.location.href).href), DWELL);
};

/**
 * Wire it, once.
 */
export const init = () => {
    // Someone paying for their data, or asking the browser to go easy, is not helped by
    // speculative fetching.
    const connection = navigator.connection;
    if (connection && (connection.saveData || /2g/.test(connection.effectiveType || ''))) {
        return;
    }
    // The layout adds this class when the setting is on; without it there is nothing to do.
    if (!document.body.classList.contains('atrium-prefetch')) {
        return;
    }
    if (document.body.dataset.atriumPrefetch) {
        return;
    }
    document.body.dataset.atriumPrefetch = '1';

    document.addEventListener('mouseover', consider, {passive: true});
    document.addEventListener('mouseout', () => clearTimeout(timer), {passive: true});
    // Keyboard users arrive by focus rather than by pointer, and deserve the same head start.
    document.addEventListener('focusin', consider, {passive: true});
};
