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
 * Callbacks for the Atrium theme.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_atrium\local\presets;
use theme_atrium\local\scheme;

/**
 * Build the theme's SCSS: Boost's whole sheet, then Atrium appended.
 *
 * Appending rather than replacing means a Boost upgrade brings its changes with it and
 * this theme only restates the rules it wants different. Variables that must be set
 * before Boost compiles (radius, font, navbar height, accent) go through
 * theme_atrium_get_pre_scss() instead.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_atrium_get_main_scss_content($theme) {
    global $CFG;

    require_once($CFG->dirroot . '/theme/boost/lib.php');

    // Boost resolves its preset against theme_boost's settings, so hand it the parent
    // config rather than ours; Atrium's presets are variables, not whole sheets.
    $scss = theme_boost_get_main_scss_content(theme_config::load('boost'));

    $scss .= "\n" . file_get_contents(__DIR__ . '/scss/post.scss');

    return $scss;
}

/**
 * SCSS injected before Boost compiles, so it can set Boost's own variables.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_atrium_get_pre_scss($theme) {
    $scss = file_get_contents(__DIR__ . '/scss/pre.scss');

    $scss .= "\n\$primary: " . presets::accent() . ";\n";
    $scss .= '$atrium-sidebar-tone: ' . presets::sidebar_tone() . ";\n";

    $radius = (int) get_config('theme_atrium', 'radius');
    if (in_array($radius, [8, 12, 16], true)) {
        $scss .= "\$atrium-radius: {$radius}px;\n";
    }

    $fontscale = (string) get_config('theme_atrium', 'fontscale');
    if (in_array($fontscale, ['0.9375', '1', '1.0625'], true)) {
        $scss .= "\$atrium-font-scale: {$fontscale};\n";
    }

    if (defined('BEHAT_SITE_RUNNING')) {
        $scss .= "\$behatsite: true;\n";
    }

    $scsspre = get_config('theme_atrium', 'scsspre');
    if (!empty($scsspre)) {
        $scss .= "\n" . $scsspre;
    }

    return $scss;
}

/**
 * SCSS appended after the main sheet.
 *
 * Everything that comes from the database and the file system is published here as CSS
 * custom properties, so the stylesheets stay static files that read them.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_atrium_get_extra_scss($theme) {
    $scss = '';

    // Font files are served through the theme so the URLs carry the theme revision.
    $scss .= ':root {';
    foreach (['400', '500', '600', '700'] as $weight) {
        $url = $theme->font_url("inter-{$weight}.woff2", 'theme');
        $scss .= "--atrium-font-{$weight}: url('{$url}');";
    }
    $scss .= '}';

    // Login page.
    $imageurl = $theme->setting_file_url('loginbackgroundimage', 'loginbackgroundimage');
    if (empty($imageurl)) {
        $imageurl = $theme->image_url('login_background', 'theme');
    }
    $overlay = get_config('theme_atrium', 'loginoverlaycolor');
    if (empty($overlay)) {
        $overlay = '#1b1d4d';
    }
    $opacity = get_config('theme_atrium', 'loginoverlayopacity');
    if ($opacity === false || $opacity === '') {
        $opacity = '0.55';
    }
    $scss .= 'body.pagelayout-login {';
    $scss .= "--atrium-login-image: url('{$imageurl}');";
    $scss .= "--atrium-login-overlay: {$overlay};";
    $scss .= "--atrium-login-overlay-opacity: {$opacity};";
    $scss .= '}';

    // Dashboard hero image, when one is uploaded.
    $heroimage = $theme->setting_file_url('heroimage', 'heroimage');
    if (!empty($heroimage)) {
        $scss .= ".atrium-hero { --atrium-hero-image: url('{$heroimage}'); }";
    }

    $rawscss = get_config('theme_atrium', 'scss');
    if (!empty($rawscss)) {
        $scss .= "\n" . $rawscss;
    }

    return $scss;
}

/**
 * Serve the theme's uploaded files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_atrium_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    $areas = ['loginbackgroundimage', 'heroimage', 'fp_heroimage', 'fp_aboutimage', 'fp_ctaimage'];
    for ($i = 1; $i <= \theme_atrium\local\frontpage_settings::MAX_TESTIMONIALS; $i++) {
        $areas[] = 'fp_testimonial' . $i . '_photo';
    }
    if ($context->contextlevel !== CONTEXT_SYSTEM || !in_array($filearea, $areas, true)) {
        send_file_not_found();
    }

    $theme = theme_config::load('atrium');
    return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
}

/**
 * User preferences this theme owns, so core_user_set_user_preferences may write them.
 *
 * @return array
 */
function theme_atrium_user_preferences(): array {
    return [
        scheme::PREFERENCE => [
            'type' => PARAM_ALPHA,
            'null' => NULL_NOT_ALLOWED,
            'default' => scheme::LIGHT,
            'choices' => [scheme::LIGHT, scheme::DARK, scheme::SYSTEM],
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
        'theme_atrium_sidebar' => [
            'type' => PARAM_ALPHA,
            'null' => NULL_NOT_ALLOWED,
            'default' => 'expanded',
            'choices' => ['expanded', 'collapsed'],
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
        \theme_atrium\local\focusmode::PREFERENCE => [
            'type' => PARAM_BOOL,
            'null' => NULL_NOT_ALLOWED,
            'default' => 0,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
        \theme_atrium\local\catalogue::VIEW_PREFERENCE => [
            'type' => PARAM_ALPHA,
            'null' => NULL_NOT_ALLOWED,
            'default' => 'grid',
            'choices' => \theme_atrium\local\catalogue::VIEWS,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
    ];
}

/**
 * Font Awesome icons this theme adds to the core map.
 *
 * @return array<string, string>
 */
function theme_atrium_get_fontawesome_icon_map(): array {
    return [
        'theme_atrium:moon' => 'fa-moon',
        'theme_atrium:sun' => 'fa-sun',
        'theme_atrium:collapse' => 'fa-angles-left',
        'theme_atrium:expand' => 'fa-angles-right',
        'theme_atrium:inprogress' => 'fa-book-open',
        'theme_atrium:completed' => 'fa-circle-check',
        'theme_atrium:due' => 'fa-regular fa-calendar-check',
        'theme_atrium:unread' => 'fa-regular fa-envelope',
        'theme_atrium:link' => 'fa-link',
        'theme_atrium:courseindex' => 'fa-list',
        'theme_atrium:blocks' => 'fa-table-columns',
        'theme_atrium:grid' => 'fa-table-cells-large',
        'theme_atrium:list' => 'fa-list-ul',
        'theme_atrium:play' => 'fa-play',
        'theme_atrium:focus' => 'fa-expand',
        'theme_atrium:exitfocus' => 'fa-compress',
    ];
}
