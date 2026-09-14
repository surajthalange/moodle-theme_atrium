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
 * Strings for the Atrium theme.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advancedsettings'] = 'Advanced';
$string['brandcolor'] = 'Brand colour';
$string['brandcolor_desc'] = 'Overrides the preset\'s accent colour for buttons, links, the active navigation item and focus rings. Leave empty to use the preset\'s own accent. Choose a colour dark enough for white text to read on it.';
$string['choosereadme'] = 'Atrium is a modern Boost child theme: a left navigation sidebar that collapses to icons, a card dashboard with a greeting and progress tiles, a card course page, dark mode with a per-user switch, five colour presets, a centred login card over a full-bleed image, and a configurable footer. Fonts are bundled; the theme makes no requests to outside services.';
$string['collapsesidebar'] = 'Collapse sidebar';
$string['configtitle'] = 'Atrium settings';
$string['dashboardsettings'] = 'Dashboard';
$string['defaultscheme'] = 'Default colour scheme';
$string['defaultscheme_desc'] = 'The scheme for users who have not chosen one. "Follow the device" uses the browser\'s own light or dark preference.';
$string['enabledarkmode'] = 'Enable dark mode';
$string['enabledarkmode_desc'] = 'When disabled, every user sees the light scheme and no switch is shown. Saved user choices are kept and honoured again if dark mode is re-enabled.';
$string['expandsidebar'] = 'Expand sidebar';
$string['fontscale'] = 'Text size';
$string['fontscale:compact'] = 'Compact';
$string['fontscale:default'] = 'Default';
$string['fontscale:large'] = 'Large';
$string['fontscale_desc'] = 'Scales all text in the theme. Compact suits sites with many courses on screen; large suits reading-heavy sites.';
$string['footercolhtml'] = 'Column {$a} content';
$string['footercoltitle'] = 'Column {$a} title';
$string['footercolumns'] = 'Footer columns';
$string['footercolumns_desc'] = 'How many content columns the footer shows above the legal line. Zero shows only the legal line and links.';
$string['footerlegal'] = 'Legal line';
$string['footerlegal_default'] = '© {year} {sitename}';
$string['footerlegal_desc'] = 'Shown at the bottom of every page. {year} and {sitename} are replaced.';
$string['footersettings'] = 'Footer';
$string['generalsettings'] = 'General';
$string['herogreeting'] = 'Greeting';
$string['herogreeting_default'] = 'Welcome back, {firstname}';
$string['herogreeting_desc'] = 'The heading of the dashboard hero. {firstname} and {fullname} are replaced with the user\'s name.';
$string['heroimage'] = 'Hero image';
$string['heroimage_desc'] = 'An optional image shown behind the dashboard greeting, darkened for legibility. Wide images work best. Leave empty for a flat colour.';
$string['loginbackgroundimage'] = 'Login background image';
$string['loginbackgroundimage_desc'] = 'The image shown behind the login card. Leave this empty to use the image the theme ships with. Use a wide, landscape image: it is scaled to cover the window, so tall images are cropped heavily. Anything busy in the centre will sit behind the card and be hidden.';
$string['loginoverlaycolor'] = 'Login overlay colour';
$string['loginoverlaycolor_desc'] = 'A wash of this colour is laid over the background image, which is what keeps the card readable no matter how bright or busy the photograph is.';
$string['loginoverlayopacity'] = 'Login overlay strength';
$string['loginoverlayopacity_desc'] = 'How strongly the overlay colour covers the image. Lower values show more of the photograph; higher values give the card more contrast to sit against. 55% suits most images.';
$string['loginsettings'] = 'Login page';
$string['mainnavigation'] = 'Main navigation';
$string['pluginname'] = 'Atrium';
$string['preset'] = 'Preset';
$string['preset:atrium'] = 'Atrium (indigo, light sidebar)';
$string['preset:emerald'] = 'Emerald (green, light sidebar)';
$string['preset:indigodark'] = 'Indigo dark (indigo, dark sidebar)';
$string['preset:rose'] = 'Rose (red, light sidebar)';
$string['preset:slate'] = 'Slate (grey, dark sidebar)';
$string['preset_desc'] = 'A starting point: an accent colour and a sidebar tone. The brand colour and sidebar tone settings below each override one half of it.';
$string['privacy:metadata:preference:theme_atrium_scheme'] = 'Whether the user chose the light or dark colour scheme, or to follow their device.';
$string['privacy:metadata:preference:theme_atrium_sidebar'] = 'Whether the user last left the navigation sidebar expanded or collapsed.';
$string['radius'] = 'Corner radius';
$string['radius_desc'] = 'The rounding of cards, buttons and inputs.';
$string['rawscss'] = 'Raw SCSS';
$string['rawscss_desc'] = 'SCSS appended after the theme\'s own stylesheet. Useful for a quick experiment; anything worth keeping belongs in the theme so it is version controlled.';
$string['rawscsspre'] = 'Raw initial SCSS';
$string['rawscsspre_desc'] = 'SCSS injected before Boost compiles, for overriding Bootstrap and Boost variables.';
$string['region-side-pre'] = 'Right';
$string['scheme:dark'] = 'Dark';
$string['scheme:light'] = 'Light';
$string['scheme:system'] = 'Follow the device';
$string['showhero'] = 'Show the dashboard hero';
$string['showhero_desc'] = 'The greeting band and progress tiles at the top of the dashboard. When off, the dashboard is Boost\'s.';
$string['showpoweredby'] = 'Show "Powered by Moodle"';
$string['showpoweredby_desc'] = 'The line in the footer that credits Moodle. Not required by the licence; on by default out of courtesy.';
$string['showschemetoggle'] = 'Show the scheme switch in the navigation bar';
$string['showschemetoggle_desc'] = 'A sun/moon button beside the user menu. The switch in the user menu itself is always present while dark mode is enabled.';
$string['showstat_completed'] = 'Show the "Completed" tile';
$string['showstat_desc'] = 'One of the four progress tiles under the greeting.';
$string['showstat_due'] = 'Show the "Due this week" tile';
$string['showstat_inprogress'] = 'Show the "In progress" tile';
$string['showstat_unread'] = 'Show the "Unread" tile';
$string['sidebar:collapsed'] = 'Collapsed to icons';
$string['sidebar:expanded'] = 'Expanded';
$string['sidebardefault'] = 'Sidebar on first visit';
$string['sidebardefault_desc'] = 'How the sidebar starts for a user who has never changed it. Each user\'s own choice is remembered afterwards.';
$string['sidebarsettings'] = 'Sidebar';
$string['sidebartone'] = 'Sidebar tone';
$string['sidebartone:preset'] = 'Follow the preset';
$string['sidebartone_desc'] = 'A light sidebar matches the page; a dark one anchors it. In dark mode the sidebar is always dark.';
$string['sociallinks'] = 'Social links';
$string['sociallinks_desc'] = 'One per line as "platform|URL", for example "youtube|https://youtube.com/@yourschool". Platforms: facebook, instagram, linkedin, x, youtube, github, mastodon, bluesky, tiktok, whatsapp, telegram, discord.';
$string['stat:completed'] = 'Completed';
$string['stat:due'] = 'Due this week';
$string['stat:inprogress'] = 'In progress';
$string['stat:unread'] = 'Unread';
$string['stat:unread_help'] = 'Unread messages and notifications';
$string['switchtodark'] = 'Switch to dark mode';
$string['switchtolight'] = 'Switch to light mode';
