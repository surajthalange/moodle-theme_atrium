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
 * Settings for the Atrium theme.
 *
 * Defaults are chosen so a fresh install with nothing changed already looks finished.
 * Every setting that reaches the stylesheet resets the theme caches on save.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\presets;
use theme_atrium\local\scheme;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingatrium', get_string('configtitle', 'theme_atrium'));

    /**
     * Add a setting whose value reaches the compiled stylesheet.
     *
     * @param admin_settingpage $page
     * @param admin_setting $setting
     */
    $addcss = function (admin_settingpage $page, admin_setting $setting): void {
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    };

    // --- General -----------------------------------------------------------------
    $page = new admin_settingpage('theme_atrium_general', get_string('generalsettings', 'theme_atrium'));

    $addcss($page, new admin_setting_configselect(
        'theme_atrium/preset',
        get_string('preset', 'theme_atrium'),
        get_string('preset_desc', 'theme_atrium'),
        presets::DEFAULT,
        presets::choices()
    ));

    $addcss($page, new admin_setting_configcolourpicker(
        'theme_atrium/brandcolor',
        get_string('brandcolor', 'theme_atrium'),
        get_string('brandcolor_desc', 'theme_atrium'),
        ''
    ));

    $addcss($page, new admin_setting_configselect(
        'theme_atrium/fontscale',
        get_string('fontscale', 'theme_atrium'),
        get_string('fontscale_desc', 'theme_atrium'),
        '1',
        ['0.9375' => get_string('fontscale:compact', 'theme_atrium'),
         '1' => get_string('fontscale:default', 'theme_atrium'),
         '1.0625' => get_string('fontscale:large', 'theme_atrium')]
    ));

    $addcss($page, new admin_setting_configselect(
        'theme_atrium/radius',
        get_string('radius', 'theme_atrium'),
        get_string('radius_desc', 'theme_atrium'),
        '12',
        ['8' => '8px', '12' => '12px', '16' => '16px']
    ));

    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/enabledarkmode',
        get_string('enabledarkmode', 'theme_atrium'),
        get_string('enabledarkmode_desc', 'theme_atrium'),
        1
    ));

    $page->add(new admin_setting_configselect(
        'theme_atrium/defaultscheme',
        get_string('defaultscheme', 'theme_atrium'),
        get_string('defaultscheme_desc', 'theme_atrium'),
        scheme::LIGHT,
        [scheme::LIGHT => get_string('scheme:light', 'theme_atrium'),
         scheme::DARK => get_string('scheme:dark', 'theme_atrium'),
         scheme::SYSTEM => get_string('scheme:system', 'theme_atrium')]
    ));

    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/showschemetoggle',
        get_string('showschemetoggle', 'theme_atrium'),
        get_string('showschemetoggle_desc', 'theme_atrium'),
        1
    ));

    $settings->add($page);

    // --- Sidebar -----------------------------------------------------------------
    $page = new admin_settingpage('theme_atrium_sidebar', get_string('sidebarsettings', 'theme_atrium'));

    $page->add(new admin_setting_configselect(
        'theme_atrium/sidebardefault',
        get_string('sidebardefault', 'theme_atrium'),
        get_string('sidebardefault_desc', 'theme_atrium'),
        'expanded',
        ['expanded' => get_string('sidebar:expanded', 'theme_atrium'),
         'collapsed' => get_string('sidebar:collapsed', 'theme_atrium')]
    ));

    $addcss($page, new admin_setting_configselect(
        'theme_atrium/sidebartone',
        get_string('sidebartone', 'theme_atrium'),
        get_string('sidebartone_desc', 'theme_atrium'),
        'preset',
        ['preset' => get_string('sidebartone:preset', 'theme_atrium'),
         presets::TONE_LIGHT => get_string('scheme:light', 'theme_atrium'),
         presets::TONE_DARK => get_string('scheme:dark', 'theme_atrium')]
    ));

    $settings->add($page);

    // --- Login page ---------------------------------------------------------------
    $page = new admin_settingpage('theme_atrium_login', get_string('loginsettings', 'theme_atrium'));

    $addcss($page, new admin_setting_configstoredfile(
        'theme_atrium/loginbackgroundimage',
        get_string('loginbackgroundimage', 'theme_atrium'),
        get_string('loginbackgroundimage_desc', 'theme_atrium'),
        'loginbackgroundimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    ));

    $addcss($page, new admin_setting_configcolourpicker(
        'theme_atrium/loginoverlaycolor',
        get_string('loginoverlaycolor', 'theme_atrium'),
        get_string('loginoverlaycolor_desc', 'theme_atrium'),
        '#1b1d4d'
    ));

    $addcss($page, new admin_setting_configselect(
        'theme_atrium/loginoverlayopacity',
        get_string('loginoverlayopacity', 'theme_atrium'),
        get_string('loginoverlayopacity_desc', 'theme_atrium'),
        '0.55',
        ['0.25' => '25%', '0.35' => '35%', '0.45' => '45%', '0.55' => '55%', '0.65' => '65%', '0.75' => '75%', '0.85' => '85%']
    ));

    $settings->add($page);

    // --- Dashboard ----------------------------------------------------------------
    $page = new admin_settingpage('theme_atrium_dashboard', get_string('dashboardsettings', 'theme_atrium'));

    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/showhero',
        get_string('showhero', 'theme_atrium'),
        get_string('showhero_desc', 'theme_atrium'),
        1
    ));

    $addcss($page, new admin_setting_configstoredfile(
        'theme_atrium/heroimage',
        get_string('heroimage', 'theme_atrium'),
        get_string('heroimage_desc', 'theme_atrium'),
        'heroimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    ));

    $page->add(new admin_setting_configtext(
        'theme_atrium/herogreeting',
        get_string('herogreeting', 'theme_atrium'),
        get_string('herogreeting_desc', 'theme_atrium'),
        get_string('herogreeting_default', 'theme_atrium'),
        PARAM_TEXT
    ));

    foreach (['inprogress', 'completed', 'due', 'unread'] as $tile) {
        $page->add(new admin_setting_configcheckbox(
            'theme_atrium/showstat_' . $tile,
            get_string('showstat_' . $tile, 'theme_atrium'),
            get_string('showstat_desc', 'theme_atrium'),
            1
        ));
    }

    $settings->add($page);

    // --- Footer -------------------------------------------------------------------
    $page = new admin_settingpage('theme_atrium_footer', get_string('footersettings', 'theme_atrium'));

    $page->add(new admin_setting_configselect(
        'theme_atrium/footercolumns',
        get_string('footercolumns', 'theme_atrium'),
        get_string('footercolumns_desc', 'theme_atrium'),
        '0',
        ['0' => '0', '1' => '1', '2' => '2', '3' => '3']
    ));

    for ($i = 1; $i <= 3; $i++) {
        $page->add(new admin_setting_configtext(
            'theme_atrium/footercol' . $i . 'title',
            get_string('footercoltitle', 'theme_atrium', $i),
            '',
            '',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_confightmleditor(
            'theme_atrium/footercol' . $i . 'html',
            get_string('footercolhtml', 'theme_atrium', $i),
            '',
            ''
        ));
    }

    $page->add(new admin_setting_configtextarea(
        'theme_atrium/sociallinks',
        get_string('sociallinks', 'theme_atrium'),
        get_string('sociallinks_desc', 'theme_atrium'),
        '',
        PARAM_RAW
    ));

    $page->add(new admin_setting_configtext(
        'theme_atrium/footerlegal',
        get_string('footerlegal', 'theme_atrium'),
        get_string('footerlegal_desc', 'theme_atrium'),
        get_string('footerlegal_default', 'theme_atrium'),
        PARAM_TEXT
    ));

    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/showpoweredby',
        get_string('showpoweredby', 'theme_atrium'),
        get_string('showpoweredby_desc', 'theme_atrium'),
        1
    ));

    $settings->add($page);

    // --- Advanced -----------------------------------------------------------------
    $page = new admin_settingpage('theme_atrium_advanced', get_string('advancedsettings', 'theme_atrium'));

    $addcss($page, new admin_setting_scsscode(
        'theme_atrium/scsspre',
        get_string('rawscsspre', 'theme_atrium'),
        get_string('rawscsspre_desc', 'theme_atrium'),
        '',
        PARAM_RAW
    ));

    $addcss($page, new admin_setting_scsscode(
        'theme_atrium/scss',
        get_string('rawscss', 'theme_atrium'),
        get_string('rawscss_desc', 'theme_atrium'),
        '',
        PARAM_RAW
    ));

    $settings->add($page);
}
