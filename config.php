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
 * Configuration for the Atrium theme.
 *
 * Atrium is a Boost child. Boost's layouts array is inherited whole; the layout files
 * this theme ships (layout/drawers.php) take precedence for every page layout that
 * names them, and the rest resolve to Boost's own files.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$THEME->name = 'atrium';
$THEME->parents = ['boost'];

// No static stylesheets: everything is compiled from SCSS so it can use Boost's
// variables rather than restating their values.
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->editor_scss = ['editor'];
$THEME->usefallback = true;

$THEME->scss = function ($theme) {
    return theme_atrium_get_main_scss_content($theme);
};

$THEME->prescsscallback = 'theme_atrium_get_pre_scss';
$THEME->extrascsscallback = 'theme_atrium_get_extra_scss';

$THEME->yuicssmodules = [];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->requiredblocks = '';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;
$THEME->haseditswitch = true;
$THEME->usescourseindex = true;
$THEME->enable_dock = false;

$THEME->activityheaderconfig = [
    'notitle' => true,
];
