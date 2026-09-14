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
 * Collapse and expand the sidebar, and remember the choice.
 *
 * The state lives on the body as a class so the sidebar and the page margin move
 * together in CSS; the server renders the same class from the user preference, so
 * there is nothing to do on load and no flash.
 *
 * @module     theme_atrium/sidebar
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {setUserPreference} from 'core_user/repository';
import Log from 'core/log';

const SELECTORS = {
    TOGGLE: '[data-action="atrium-sidebar-toggle"]',
    SIDEBAR: '#atrium-sidebar',
    GROUP: '.atrium-nav-group',
};

const CLASSES = {
    COLLAPSED: 'atrium-sidebar-collapsed',
};

const PREFERENCE = 'theme_atrium_sidebar';

let initialised = false;

/**
 * Whether the sidebar is collapsed right now.
 *
 * @returns {boolean}
 */
const isCollapsed = () => document.body.classList.contains(CLASSES.COLLAPSED);

/**
 * Apply a state to the DOM and save it.
 *
 * @param {boolean} collapsed
 */
const setCollapsed = (collapsed) => {
    document.body.classList.toggle(CLASSES.COLLAPSED, collapsed);
    document.querySelectorAll(SELECTORS.TOGGLE).forEach((toggle) => {
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        const text = toggle.querySelector('.atrium-nav-text');
        if (text) {
            text.textContent = collapsed ? toggle.dataset.labelExpand : toggle.dataset.labelCollapse;
        }
    });
    if (document.body.classList.contains('notloggedin')) {
        return;
    }
    setUserPreference(PREFERENCE, collapsed ? 'collapsed' : 'expanded').catch(Log.error);
};

/**
 * Wire the toggle and the group behaviour once.
 */
export const init = () => {
    if (initialised) {
        return;
    }
    initialised = true;

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest(SELECTORS.TOGGLE);
        if (toggle) {
            event.preventDefault();
            setCollapsed(!isCollapsed());
            return;
        }
        // Opening a group while collapsed would show its children with no room for
        // them, so expand the sidebar first and let Bootstrap open the group.
        const group = event.target.closest(SELECTORS.GROUP);
        if (group && isCollapsed() && group.closest(SELECTORS.SIDEBAR)) {
            setCollapsed(false);
        }
    });
};
