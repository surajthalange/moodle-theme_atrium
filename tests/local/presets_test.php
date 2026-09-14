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
 * Tests for the colour presets, including the contrast guarantees the README makes.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\presets::class)]
final class presets_test extends \advanced_testcase {
    /**
     * Every preset accent passes WCAG AA as white-on-accent (buttons) and accent-on-white (links),
     * and its dark-scheme tint passes on the dark surface.
     */
    public function test_every_preset_meets_contrast(): void {
        foreach (presets::all() as $name => $preset) {
            $accent = $preset['accent'];
            $this->assertGreaterThanOrEqual(4.5, presets::contrast($accent, '#ffffff'), "$name: white text on accent");
            $this->assertGreaterThanOrEqual(4.5, presets::contrast($accent, presets::LIGHT_SURFACE), "$name: accent on light");
            $tinted = presets::tint($accent, presets::DARK_LINK_TINT);
            $darklink = presets::contrast($tinted, presets::DARK_SURFACE);
            $this->assertGreaterThanOrEqual(4.5, $darklink, "$name: link on dark ($tinted)");
            $this->assertContains($preset['sidebar'], [presets::TONE_LIGHT, presets::TONE_DARK], "$name: sidebar tone");
        }
    }

    /**
     * The tint used by the test is the tint used by the stylesheet, and the dark surface matches.
     */
    public function test_constants_match_the_scss(): void {
        $tokens = file_get_contents(__DIR__ . '/../../scss/atrium/_tokens.scss');
        $percent = (int) round(presets::DARK_LINK_TINT * 100);
        $this->assertStringContainsString("tint-color(\$primary, {$percent}%)", $tokens);
        $pre = file_get_contents(__DIR__ . '/../../scss/pre.scss');
        $this->assertStringContainsString('$body-secondary-bg-dark: ' . presets::DARK_SURFACE . ';', $pre);
    }

    /**
     * Contrast and luminance are the WCAG formulae.
     */
    public function test_contrast_formula(): void {
        $this->assertEqualsWithDelta(21.0, presets::contrast('#000000', '#ffffff'), 0.01);
        $this->assertEqualsWithDelta(1.0, presets::contrast('#123456', '#123456'), 0.001);
        $this->assertEqualsWithDelta(0.0, presets::luminance('#000000'), 0.0001);
        $this->assertEqualsWithDelta(1.0, presets::luminance('#ffffff'), 0.0001);
        $this->assertSame('#ffffff', presets::tint('#000000', 1.0));
        $this->assertSame('#808080', presets::tint('#000000', 0.5));
    }

    /**
     * Settings override the preset: brand colour replaces the accent, sidebar tone replaces the tone,
     * and an unknown preset name falls back to the default.
     */
    public function test_settings_override_the_preset(): void {
        $this->resetAfterTest();

        $this->assertSame(presets::DEFAULT, presets::current_name());
        $this->assertSame('#4f46e5', presets::accent());
        $this->assertSame(presets::TONE_LIGHT, presets::sidebar_tone());

        set_config('preset', 'slate', 'theme_atrium');
        $this->assertSame('slate', presets::current_name());
        $this->assertSame('#334155', presets::accent());
        $this->assertSame(presets::TONE_DARK, presets::sidebar_tone());

        set_config('brandcolor', '#AB12CD', 'theme_atrium');
        $this->assertSame('#ab12cd', presets::accent());
        set_config('brandcolor', 'not a colour', 'theme_atrium');
        $this->assertSame('#334155', presets::accent());

        set_config('sidebartone', presets::TONE_LIGHT, 'theme_atrium');
        $this->assertSame(presets::TONE_LIGHT, presets::sidebar_tone());
        set_config('sidebartone', 'preset', 'theme_atrium');
        $this->assertSame(presets::TONE_DARK, presets::sidebar_tone());

        set_config('preset', 'nosuchpreset', 'theme_atrium');
        $this->assertSame(presets::DEFAULT, presets::current_name());
        $this->assertSame(presets::get('nosuchpreset'), presets::get(presets::DEFAULT));
    }

    /**
     * The choices for the setting are every preset, localised.
     */
    public function test_choices(): void {
        $choices = presets::choices();
        $this->assertSame(array_keys(presets::all()), array_keys($choices));
        foreach ($choices as $label) {
            $this->assertStringNotContainsString('[[', $label);
        }
    }
}
