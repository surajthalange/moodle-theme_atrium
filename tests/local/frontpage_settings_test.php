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

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the front page settings model.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\frontpage_settings::class)]
final class frontpage_settings_test extends \advanced_testcase {
    /**
     * A fresh install has a finished front page: hero, three features, showcase, stats and CTA on;
     * testimonials and about off.
     */
    public function test_defaults(): void {
        $this->resetAfterTest();
        $this->assertTrue(frontpage_settings::enabled());
        $this->assertSame(
            ['hero' => true, 'features' => true, 'showcase' => true, 'stats' => true,
             'testimonials' => false, 'about' => false, 'cta' => true],
            array_combine(frontpage_settings::SECTIONS, array_map(
                fn($s) => frontpage_settings::section_enabled($s),
                frontpage_settings::SECTIONS
            ))
        );

        $hero = frontpage_settings::hero();
        $this->assertSame(get_string('fp_hero_heading_default', 'theme_atrium'), $hero['heading']);
        $this->assertSame('/course/index.php', $hero['button1url']);
        $this->assertSame('', $hero['button2text']);
        $this->assertSame('left', $hero['align']);
        $this->assertSame('standard', $hero['height']);
        $this->assertFalse($hero['transparentnavbar']);

        $features = frontpage_settings::features();
        $this->assertCount(3, $features['items']);
        $this->assertSame('fa-graduation-cap', $features['items'][0]['icon']);

        $this->assertSame('latest', frontpage_settings::showcase()['source']);
        $this->assertSame(6, frontpage_settings::showcase()['count']);
        $this->assertCount(4, frontpage_settings::stats());
        $this->assertSame('{courses}', frontpage_settings::stats()[0]['value']);
        $this->assertSame([], frontpage_settings::testimonials()['items']);
    }

    /**
     * The master switch turns every section off; a saved empty value is honoured as empty.
     */
    public function test_switches_and_saved_empties(): void {
        $this->resetAfterTest();
        set_config('fp_enable', 0, 'theme_atrium');
        foreach (frontpage_settings::SECTIONS as $section) {
            $this->assertFalse(frontpage_settings::section_enabled($section), $section);
        }

        set_config('fp_enable', 1, 'theme_atrium');
        set_config('fp_hero_enable', 0, 'theme_atrium');
        set_config('fp_testimonials_enable', 1, 'theme_atrium');
        $this->assertFalse(frontpage_settings::section_enabled('hero'));
        $this->assertTrue(frontpage_settings::section_enabled('testimonials'));

        set_config('fp_hero_button1text', '', 'theme_atrium');
        $this->assertSame('', frontpage_settings::hero()['button1text'], 'An emptied button stays empty');
        set_config('fp_feature2_title', '', 'theme_atrium');
        $this->assertCount(2, frontpage_settings::features()['items'], 'A block without a title is dropped');
    }

    /**
     * Choices are validated, counts clamped, ids parsed.
     */
    public function test_validation(): void {
        $this->resetAfterTest();
        set_config('fp_hero_align', 'diagonal', 'theme_atrium');
        set_config('fp_hero_height', 'huge', 'theme_atrium');
        set_config('fp_feature1_icon', 'fa-evil', 'theme_atrium');
        set_config('fp_showcase_count', 99, 'theme_atrium');
        set_config('fp_showcase_source', 'ids', 'theme_atrium');
        set_config('fp_showcase_ids', '5, 3,x, 9 3', 'theme_atrium');
        set_config('fp_cta_background', 'plaid', 'theme_atrium');

        $this->assertSame('left', frontpage_settings::hero()['align']);
        $this->assertSame('standard', frontpage_settings::hero()['height']);
        $this->assertSame(frontpage_settings::ICONS[0], frontpage_settings::features()['items'][0]['icon']);
        $showcase = frontpage_settings::showcase();
        $this->assertSame(12, $showcase['count']);
        $this->assertSame([5, 3, 9, 3], $showcase['ids']);
        $this->assertSame('accent', frontpage_settings::cta()['background']);

        set_config('fp_showcase_count', 1, 'theme_atrium');
        $this->assertSame(3, frontpage_settings::showcase()['count']);
    }

    /**
     * Placeholders resolve to the site name and formatted counts.
     */
    public function test_resolve(): void {
        global $SITE;
        $this->resetAfterTest();
        $counts = ['courses' => 12, 'users' => 1234, 'categories' => 3, 'completions' => 0];
        $this->assertSame(
            'Join 1,234 learners on ' . $SITE->fullname . ': 12 courses, 3 categories, 0 done',
            frontpage_settings::resolve(
                'Join {users} learners on {sitename}: {courses} courses, {categories} categories, {completions} done',
                $counts
            )
        );
        $this->assertSame('Plain', frontpage_settings::resolve('Plain', $counts));
    }

    /**
     * Testimonials keep their slot index so the photo file area can be found.
     */
    public function test_testimonials(): void {
        $this->resetAfterTest();
        set_config('fp_testimonial2_quote', 'Great', 'theme_atrium');
        set_config('fp_testimonial2_name', 'Ada', 'theme_atrium');
        set_config('fp_testimonial5_quote', 'Also great', 'theme_atrium');
        $items = frontpage_settings::testimonials()['items'];
        $this->assertSame([2, 5], array_column($items, 'index'));
        $this->assertSame('Ada', $items[0]['name']);
        $this->assertSame('', $items[1]['name']);
    }
}
