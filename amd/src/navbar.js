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
 * Solidify the navigation bar once the page scrolls past the hero.
 *
 * Only active when the front page hero asks for a transparent navigation bar; the
 * class it adds is what the stylesheet paints against.
 *
 * @module     theme_atrium/navbar
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTORS = {
    NAVBAR: '.atrium-navbar',
};

const CLASSES = {
    TRANSPARENT: 'atrium-transparent-navbar',
    SCROLLED: 'atrium-navbar-scrolled',
};

const THRESHOLD = 40;

let initialised = false;

/**
 * Watch the scroll position and toggle the scrolled class.
 */
export const init = () => {
    if (initialised || !document.body.classList.contains(CLASSES.TRANSPARENT)) {
        return;
    }
    initialised = true;
    const navbar = document.querySelector(SELECTORS.NAVBAR);
    if (!navbar) {
        return;
    }
    const update = () => {
        navbar.classList.toggle(CLASSES.SCROLLED, window.scrollY > THRESHOLD);
    };
    window.addEventListener('scroll', update, {passive: true});
    update();
};
