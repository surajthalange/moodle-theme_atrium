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

namespace theme_atrium\local;

/**
 * Quick start: one click makes a fresh site look like the theme's screenshots.
 *
 * Writes a coherent set of settings (front page slides, features, showcase, numbers, call
 * to action, footer columns, quick links, login panel, course options) built from the
 * site's own name, summary, support contact and courses. Nothing is invented about the
 * site: no testimonials, no announcement, no social links. Every value is a normal theme
 * setting, so anything can be changed afterwards, and reset() puts them all back.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class quickstart {
    /**
     * The settings quick start writes, so reset() knows what to clear.
     *
     * @return string[]
     */
    public static function settings(): array {
        return [
            'fp_enable', 'fp_hero_enable', 'fp_hero_heading', 'fp_hero_subheading', 'fp_hero_button1text',
            'fp_hero_button1url', 'fp_hero_button2text', 'fp_hero_button2url', 'fp_hero_align', 'fp_hero_height',
            'fp_hero_autoplay', 'fp_hero_interval',
            'fp_hero_slide2_heading', 'fp_hero_slide2_subheading', 'fp_hero_slide2_buttontext', 'fp_hero_slide2_buttonurl',
            'fp_hero_slide3_heading', 'fp_hero_slide3_subheading', 'fp_hero_slide3_buttontext', 'fp_hero_slide3_buttonurl',
            'fp_features_enable', 'fp_features_heading', 'fp_features_count',
            'fp_feature1_icon', 'fp_feature1_title', 'fp_feature1_text',
            'fp_feature2_icon', 'fp_feature2_title', 'fp_feature2_text',
            'fp_feature3_icon', 'fp_feature3_title', 'fp_feature3_text',
            'fp_showcase_enable', 'fp_showcase_source', 'fp_showcase_count',
            'fp_stats_enable', 'fp_about_enable', 'fp_about_heading', 'fp_about_text',
            'fp_cta_enable', 'fp_cta_heading', 'fp_cta_text', 'fp_cta_buttontext', 'fp_cta_buttonurl', 'fp_cta_background',
            'quicklinks',
            'footercolumns', 'footercol1type', 'footercol1title', 'footercol1html',
            'footercol2type', 'footercol2title', 'footercol2menu',
            'footercol3type', 'footercol3title', 'footercontactemail',
            'footercol4type', 'footercol4title',
            'loginlayout', 'loginpanelheading', 'loginpaneltext', 'logintextbelow',
            'course_showbanner', 'course_enablefocus', 'course_showstats',
            'catalogue_showenrolled', 'catalogue_showactivities', 'navbar_recentcourses',
        ];
    }

    /**
     * Apply the quick start settings.
     *
     * @return string[] Names of the settings written.
     */
    public static function apply(): array {
        global $SITE, $CFG, $DB;
        $context = \context_course::instance(SITEID);
        $sitename = format_string($SITE->fullname, true, ['context' => $context]);
        $summary = trim(strip_tags((string) $SITE->summary));
        $coursecount = max(0, $DB->count_records('course', ['visible' => 1]) - 1);
        $str = fn(string $key, $a = null): string => get_string('quickstart:' . $key, 'theme_atrium', $a);

        $values = [
            'fp_enable' => 1,
            'fp_hero_enable' => 1,
            'fp_hero_heading' => $str('hero_heading', $sitename),
            'fp_hero_subheading' => $summary !== '' ? shorten_text($summary, 160) : $str('hero_subheading'),
            'fp_hero_button1text' => $str('browse'),
            'fp_hero_button1url' => '/course/index.php',
            'fp_hero_button2text' => $str('login'),
            'fp_hero_button2url' => '/login/index.php',
            'fp_hero_align' => 'left',
            'fp_hero_height' => 'standard',
            'fp_hero_autoplay' => 1,
            'fp_hero_interval' => '6',
            'fp_hero_slide2_heading' => $str('slide2_heading', $coursecount),
            'fp_hero_slide2_subheading' => $str('slide2_text'),
            'fp_hero_slide2_buttontext' => $str('browse'),
            'fp_hero_slide2_buttonurl' => '/course/index.php',
            'fp_hero_slide3_heading' => $str('slide3_heading'),
            'fp_hero_slide3_subheading' => $str('slide3_text'),
            'fp_hero_slide3_buttontext' => $str('login'),
            'fp_hero_slide3_buttonurl' => '/login/index.php',
            'fp_features_enable' => 1,
            'fp_features_heading' => $str('features_heading'),
            'fp_features_count' => 3,
            'fp_feature1_icon' => 'fa-graduation-cap',
            'fp_feature1_title' => $str('feature1_title'),
            'fp_feature1_text' => $str('feature1_text'),
            'fp_feature2_icon' => 'fa-mobile-screen',
            'fp_feature2_title' => $str('feature2_title'),
            'fp_feature2_text' => $str('feature2_text'),
            'fp_feature3_icon' => 'fa-chart-line',
            'fp_feature3_title' => $str('feature3_title'),
            'fp_feature3_text' => $str('feature3_text'),
            'fp_showcase_enable' => 1,
            'fp_showcase_source' => 'latest',
            'fp_showcase_count' => 6,
            'fp_stats_enable' => 1,
            'fp_about_enable' => $summary !== '' ? 1 : 0,
            'fp_about_heading' => $str('about_heading', $sitename),
            'fp_about_text' => $summary !== ''
                ? format_text($SITE->summary, (int) $SITE->summaryformat, ['context' => $context]) : '',
            'fp_cta_enable' => 1,
            'fp_cta_heading' => $str('cta_heading'),
            'fp_cta_text' => $str('cta_text'),
            'fp_cta_buttontext' => $str('browse'),
            'fp_cta_buttonurl' => '/course/index.php',
            'fp_cta_background' => 'accent',
            'quicklinks' => implode("\n", [
                'fa-book|' . $str('ql_courses') . '|/course/index.php',
                'fa-calendar|' . $str('ql_calendar') . '|/calendar/view.php?view=month',
                'fa-comments|' . $str('ql_messages') . '|/message/index.php',
                'fa-chart-line|' . $str('ql_grades') . '|/grade/report/overview/index.php',
                'fa-user|' . $str('ql_profile') . '|/user/profile.php',
                'fa-circle-question|' . $str('ql_help') . '|https://docs.moodle.org|newtab',
            ]),
            'footercolumns' => 4,
            'footercol1type' => 'html',
            'footercol1title' => $sitename,
            'footercol1html' => '<p>' . s($summary !== '' ? shorten_text($summary, 200) : $str('footer_about')) . '</p>',
            'footercol2type' => 'menu',
            'footercol2title' => $str('footer_explore'),
            'footercol2menu' => implode("\n", [
                $str('ql_courses') . '|/course/index.php',
                $str('ql_calendar') . '|/calendar/view.php?view=month',
                get_string('myhome') . '|/my/',
            ]),
            'footercol3type' => 'contact',
            'footercol3title' => $str('footer_contact'),
            'footercontactemail' => !empty($CFG->supportemail) ? $CFG->supportemail : '',
            'footercol4type' => 'social',
            'footercol4title' => $str('footer_follow'),
            'loginlayout' => 'panelleft',
            'loginpanelheading' => $str('login_heading', $sitename),
            'loginpaneltext' => '<p>' . s($str('login_text')) . '</p>',
            'logintextbelow' => !empty($CFG->supportemail)
                ? '<p>' . s($str('login_support', $CFG->supportemail)) . '</p>' : '',
            'course_showbanner' => 1,
            'course_enablefocus' => 1,
            'course_showstats' => 1,
            'catalogue_showenrolled' => 1,
            'catalogue_showactivities' => 1,
            'navbar_recentcourses' => 1,
        ];
        foreach ($values as $name => $value) {
            set_config($name, $value, 'theme_atrium');
        }
        theme_reset_all_caches();
        return array_keys($values);
    }

    /**
     * Clear every setting quick start writes, so the theme's own defaults apply again.
     */
    public static function reset(): void {
        foreach (self::settings() as $name) {
            unset_config($name, 'theme_atrium');
        }
        theme_reset_all_caches();
    }
}
