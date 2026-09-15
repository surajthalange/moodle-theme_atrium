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

use theme_atrium\local\frontpage_settings;
use theme_atrium\local\presets;
use theme_atrium\local\scheme;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingatrium', get_string('configtitle', 'theme_atrium'));

    // Add a setting whose value reaches the compiled stylesheet.
    $addcss = function (admin_settingpage $page, admin_setting $setting): void {
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    };

    // General.
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

    // Sidebar.
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

    // Login page.
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

    // Course.
    $page = new admin_settingpage('theme_atrium_course', get_string('coursesettings', 'theme_atrium'));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/course_showbanner',
        get_string('course_showbanner', 'theme_atrium'),
        get_string('course_showbanner_desc', 'theme_atrium'),
        1
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/course_enablefocus',
        get_string('course_enablefocus', 'theme_atrium'),
        get_string('course_enablefocus_desc', 'theme_atrium'),
        1
    ));
    $settings->add($page);

    // Dashboard.
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

    // Front page.
    $page = new admin_settingpage('theme_atrium_frontpage', get_string('frontpagesettings', 'theme_atrium'));
    $sectionenable = get_string('fp_section_enable', 'theme_atrium');
    $sectionheading = get_string('fp_section_heading', 'theme_atrium');

    $page->add(new admin_setting_heading(
        'theme_atrium/fp_intro',
        '',
        get_string('fp_intro', 'theme_atrium')
    ));

    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/fp_enable',
        get_string('fp_enable', 'theme_atrium'),
        get_string('fp_enable_desc', 'theme_atrium'),
        1
    ));

    // Hero.
    $page->add(new admin_setting_heading('theme_atrium/fp_hero', get_string('fp_hero', 'theme_atrium'), ''));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_hero_enable', $sectionenable, '', 1));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_hero_heading',
        get_string('fp_hero_heading', 'theme_atrium'),
        get_string('fp_placeholders_desc', 'theme_atrium'),
        get_string('fp_hero_heading_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtextarea(
        'theme_atrium/fp_hero_subheading',
        get_string('fp_hero_subheading', 'theme_atrium'),
        '',
        get_string('fp_hero_subheading_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_hero_button1text',
        get_string('fp_button1text', 'theme_atrium'),
        get_string('fp_buttontext_desc', 'theme_atrium'),
        get_string('fp_hero_button1text_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_hero_button1url',
        get_string('fp_button1url', 'theme_atrium'),
        get_string('fp_buttonurl_desc', 'theme_atrium'),
        '/course/index.php',
        PARAM_URL
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_hero_button2text',
        get_string('fp_button2text', 'theme_atrium'),
        get_string('fp_buttontext_desc', 'theme_atrium'),
        '',
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_hero_button2url',
        get_string('fp_button2url', 'theme_atrium'),
        get_string('fp_buttonurl_desc', 'theme_atrium'),
        '',
        PARAM_URL
    ));
    $addcss($page, new admin_setting_configstoredfile(
        'theme_atrium/fp_heroimage',
        get_string('fp_hero_image', 'theme_atrium'),
        get_string('fp_hero_image_desc', 'theme_atrium'),
        'fp_heroimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_hero_align',
        get_string('fp_hero_align', 'theme_atrium'),
        '',
        'left',
        ['left' => get_string('fp_align_left', 'theme_atrium'), 'center' => get_string('fp_align_center', 'theme_atrium')]
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_hero_height',
        get_string('fp_hero_height', 'theme_atrium'),
        '',
        'standard',
        ['compact' => get_string('fp_height_compact', 'theme_atrium'), 'standard' => get_string(
            'fp_height_standard',
            'theme_atrium'
        ),
        'tall' => get_string(
            'fp_height_tall',
            'theme_atrium'
        )]
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/fp_hero_transparentnavbar',
        get_string('fp_hero_transparentnavbar', 'theme_atrium'),
        get_string('fp_hero_transparentnavbar_desc', 'theme_atrium'),
        0
    ));

    // Feature blocks.
    $page->add(new admin_setting_heading(
        'theme_atrium/fp_features',
        get_string('fp_features', 'theme_atrium'),
        get_string('fp_features_desc', 'theme_atrium')
    ));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_features_enable', $sectionenable, '', 1));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_features_heading',
        $sectionheading,
        '',
        get_string('fp_features_heading_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_features_count',
        get_string('fp_features_count', 'theme_atrium'),
        '',
        '3',
        ['3' => '3', '6' => '6']
    ));
    $icons = array_combine(frontpage_settings::ICONS, frontpage_settings::ICONS);
    for ($i = 1; $i <= frontpage_settings::MAX_FEATURES; $i++) {
        $page->add(new admin_setting_configselect(
            "theme_atrium/fp_feature{$i}_icon",
            get_string('fp_feature_icon', 'theme_atrium', $i),
            '',
            frontpage_settings::ICONS[$i - 1],
            $icons
        ));
        $page->add(new admin_setting_configtext(
            "theme_atrium/fp_feature{$i}_title",
            get_string('fp_feature_title', 'theme_atrium', $i),
            '',
            $i <= 3 ? get_string("fp_feature{$i}_title_default", 'theme_atrium') : '',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtextarea(
            "theme_atrium/fp_feature{$i}_text",
            get_string('fp_feature_text', 'theme_atrium', $i),
            '',
            $i <= 3 ? get_string("fp_feature{$i}_text_default", 'theme_atrium') : '',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtext(
            "theme_atrium/fp_feature{$i}_url",
            get_string('fp_feature_url', 'theme_atrium', $i),
            '',
            '',
            PARAM_URL
        ));
    }

    // Course showcase.
    $page->add(new admin_setting_heading('theme_atrium/fp_showcase', get_string('fp_showcase', 'theme_atrium'), ''));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_showcase_enable', $sectionenable, '', 1));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_showcase_heading',
        $sectionheading,
        '',
        get_string('fp_showcase_heading_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_showcase_source',
        get_string('fp_showcase_source', 'theme_atrium'),
        get_string('fp_showcase_source_desc', 'theme_atrium'),
        'latest',
        ['latest' => get_string('fp_showcase_latest', 'theme_atrium'), 'category' => get_string(
            'fp_showcase_category',
            'theme_atrium'
        ),
        'ids' => get_string(
            'fp_showcase_pick',
            'theme_atrium'
        )]
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_showcase_category',
        get_string('fp_showcase_category', 'theme_atrium'),
        '',
        0,
        [0 => get_string('none')] + core_course_category::make_categories_list()
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_showcase_ids',
        get_string('fp_showcase_ids', 'theme_atrium'),
        get_string('fp_showcase_ids_desc', 'theme_atrium'),
        '',
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_showcase_count',
        get_string('fp_showcase_count', 'theme_atrium'),
        '',
        6,
        array_combine(range(3, 12), range(3, 12))
    ));

    // Stats strip.
    $page->add(new admin_setting_heading(
        'theme_atrium/fp_stats',
        get_string('fp_stats', 'theme_atrium'),
        get_string('fp_stats_desc', 'theme_atrium')
    ));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_stats_enable', $sectionenable, '', 1));
    $statdefaults = [1 => '{courses}', 2 => '{users}', 3 => '{categories}', 4 => '{completions}'];
    for ($i = 1; $i <= frontpage_settings::MAX_STATS; $i++) {
        $page->add(new admin_setting_configtext(
            "theme_atrium/fp_stat{$i}_label",
            get_string('fp_stat_label', 'theme_atrium', $i),
            '',
            get_string("fp_stat{$i}_label_default", 'theme_atrium'),
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtext(
            "theme_atrium/fp_stat{$i}_value",
            get_string('fp_stat_value', 'theme_atrium', $i),
            '',
            $statdefaults[$i],
            PARAM_TEXT
        ));
    }

    // Testimonials.
    $page->add(new admin_setting_heading('theme_atrium/fp_testimonials', get_string('fp_testimonials', 'theme_atrium'), ''));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_testimonials_enable', $sectionenable, '', 0));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_testimonials_heading',
        $sectionheading,
        '',
        get_string('fp_testimonials_heading_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    for ($i = 1; $i <= frontpage_settings::MAX_TESTIMONIALS; $i++) {
        $page->add(new admin_setting_configtextarea(
            "theme_atrium/fp_testimonial{$i}_quote",
            get_string('fp_testimonial_quote', 'theme_atrium', $i),
            '',
            '',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtext(
            "theme_atrium/fp_testimonial{$i}_name",
            get_string('fp_testimonial_name', 'theme_atrium', $i),
            '',
            '',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtext(
            "theme_atrium/fp_testimonial{$i}_role",
            get_string('fp_testimonial_role', 'theme_atrium', $i),
            '',
            '',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configstoredfile(
            "theme_atrium/fp_testimonial{$i}_photo",
            get_string('fp_testimonial_photo', 'theme_atrium', $i),
            '',
            "fp_testimonial{$i}_photo",
            0,
            ['maxfiles' => 1, 'accepted_types' => ['web_image']]
        ));
    }

    // About band.
    $page->add(new admin_setting_heading('theme_atrium/fp_about', get_string('fp_about', 'theme_atrium'), ''));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_about_enable', $sectionenable, '', 0));
    $page->add(new admin_setting_configtext('theme_atrium/fp_about_heading', $sectionheading, '', '', PARAM_TEXT));
    $page->add(new admin_setting_confightmleditor('theme_atrium/fp_about_text', get_string(
        'fp_about_text',
        'theme_atrium'
    ), '', ''));
    $page->add(new admin_setting_configstoredfile(
        'theme_atrium/fp_aboutimage',
        get_string('fp_about_image', 'theme_atrium'),
        '',
        'fp_aboutimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_about_imageside',
        get_string('fp_about_imageside', 'theme_atrium'),
        '',
        'right',
        ['left' => get_string('fp_align_left', 'theme_atrium'), 'right' => get_string('fp_align_right', 'theme_atrium')]
    ));
    $page->add(new admin_setting_configtext('theme_atrium/fp_about_buttontext', get_string(
        'fp_button1text',
        'theme_atrium'
    ), '', '', PARAM_TEXT));
    $page->add(new admin_setting_configtext('theme_atrium/fp_about_buttonurl', get_string(
        'fp_button1url',
        'theme_atrium'
    ), '', '', PARAM_URL));

    // Call to action.
    $page->add(new admin_setting_heading('theme_atrium/fp_cta', get_string('fp_cta', 'theme_atrium'), ''));
    $page->add(new admin_setting_configcheckbox('theme_atrium/fp_cta_enable', $sectionenable, '', 1));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_cta_heading',
        $sectionheading,
        get_string('fp_placeholders_desc', 'theme_atrium'),
        get_string('fp_cta_heading_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtextarea(
        'theme_atrium/fp_cta_text',
        get_string('fp_cta_text', 'theme_atrium'),
        '',
        get_string('fp_cta_text_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_cta_buttontext',
        get_string('fp_button1text', 'theme_atrium'),
        '',
        get_string('fp_cta_buttontext_default', 'theme_atrium'),
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'theme_atrium/fp_cta_buttonurl',
        get_string('fp_button1url', 'theme_atrium'),
        '',
        '/login/index.php',
        PARAM_URL
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/fp_cta_background',
        get_string('fp_cta_background', 'theme_atrium'),
        '',
        'accent',
        ['accent' => get_string('fp_cta_bg_accent', 'theme_atrium'), 'dark' => get_string('fp_cta_bg_dark', 'theme_atrium'),
        'image' => get_string(
            'fp_cta_bg_image',
            'theme_atrium'
        )]
    ));
    $page->add(new admin_setting_configstoredfile(
        'theme_atrium/fp_ctaimage',
        get_string('fp_cta_image', 'theme_atrium'),
        '',
        'fp_ctaimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    ));

    $settings->add($page);

    // Catalogue.
    $page = new admin_settingpage('theme_atrium_catalogue', get_string('cataloguesettings', 'theme_atrium'));
    $page->add(new admin_setting_heading('theme_atrium/catalogue_intro', '', get_string('catalogue_intro', 'theme_atrium')));
    $page->add(new admin_setting_configselect(
        'theme_atrium/catalogue_perpage',
        get_string('catalogue_perpage', 'theme_atrium'),
        get_string('catalogue_perpage_desc', 'theme_atrium'),
        12,
        array_combine([6, 9, 12, 18, 24, 36, 48], [6, 9, 12, 18, 24, 36, 48])
    ));
    $page->add(new admin_setting_configselect(
        'theme_atrium/catalogue_defaultsort',
        get_string('catalogue_defaultsort', 'theme_atrium'),
        '',
        'name',
        [
            'name' => get_string('catalogue_sort_name', 'theme_atrium'),
            'newest' => get_string('catalogue_sort_newest', 'theme_atrium'),
            'popular' => get_string('catalogue_sort_popular', 'theme_atrium'),
        ]
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/catalogue_showenrolled',
        get_string('catalogue_showenrolled', 'theme_atrium'),
        get_string('catalogue_showenrolled_desc', 'theme_atrium'),
        1
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/catalogue_showprogress',
        get_string('catalogue_showprogress', 'theme_atrium'),
        get_string('catalogue_showprogress_desc', 'theme_atrium'),
        1
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/catalogue_showprice',
        get_string('catalogue_showprice', 'theme_atrium'),
        get_string('catalogue_showprice_desc', 'theme_atrium'),
        1
    ));
    $page->add(new admin_setting_heading(
        'theme_atrium/enrol_heading',
        get_string('enrolpagesettings', 'theme_atrium'),
        get_string('enrolpagesettings_desc', 'theme_atrium')
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/enrol_showoutline',
        get_string('enrol_showoutline', 'theme_atrium'),
        get_string('enrol_showoutline_desc', 'theme_atrium'),
        1
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/enrol_showinstructors',
        get_string('enrol_showinstructors', 'theme_atrium'),
        get_string('enrol_showinstructors_desc', 'theme_atrium'),
        1
    ));
    $page->add(new admin_setting_configcheckbox(
        'theme_atrium/enrol_showrelated',
        get_string('enrol_showrelated', 'theme_atrium'),
        get_string('enrol_showrelated_desc', 'theme_atrium'),
        1
    ));
    $settings->add($page);

    // Footer.
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

    // Advanced.
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
