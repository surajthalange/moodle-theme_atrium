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
 * Tests for the accessibility statement.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\accessibility_statement::class)]
final class accessibility_statement_test extends \advanced_testcase {
    /**
     * Off unless the administrator turns it on: an empty statement reads as a claim.
     */
    public function test_off_by_default(): void {
        $this->resetAfterTest();
        $this->assertFalse(accessibility_statement::enabled());

        set_config('a11ystatement_enable', 1, 'theme_atrium');
        $this->assertTrue(accessibility_statement::enabled());
    }

    /**
     * The feature list is generated, not asserted: switching the toolbar off removes the
     * claim that the toolbar exists.
     */
    public function test_features_follow_the_settings(): void {
        $this->resetAfterTest();

        $all = array_column(accessibility_statement::features(), 'text');
        $this->assertNotEmpty($all);
        $toolbar = get_string('a11ystatement_feature_toolbar', 'theme_atrium');
        $this->assertContains($toolbar, $all);

        set_config('navbar_a11y', 0, 'theme_atrium');
        $without = array_column(accessibility_statement::features(), 'text');
        $this->assertNotContains($toolbar, $without);

        // What is true of the theme however it is set up stays in the list.
        $this->assertContains(get_string('a11ystatement_feature_keyboard', 'theme_atrium'), $without);
    }

    /**
     * The scheme and palette claims follow their own settings too.
     */
    public function test_features_drop_disabled_pieces(): void {
        $this->resetAfterTest();
        set_config('enabledarkmode', 0, 'theme_atrium');
        set_config('navbar_palette', 0, 'theme_atrium');

        $texts = array_column(accessibility_statement::features(), 'text');
        $this->assertNotContains(get_string('a11ystatement_feature_scheme', 'theme_atrium'), $texts);
        $this->assertNotContains(get_string('a11ystatement_feature_palette', 'theme_atrium'), $texts);
    }

    /**
     * With nothing filled in, the statement still exports, names the site, and makes the
     * cautious conformance claim rather than the flattering one.
     */
    public function test_export_defaults_are_cautious(): void {
        global $SITE;
        $this->resetAfterTest();

        $data = accessibility_statement::export();
        $this->assertSame($SITE->fullname, $data['organisation']);
        $this->assertTrue($data['ispartial']);
        $this->assertFalse($data['isnone']);
        $this->assertFalse($data['hasknown']);
        $this->assertFalse($data['hascontact']);
        $this->assertFalse($data['hasenforcement']);
        $this->assertFalse($data['hasreviewed']);
        $this->assertStringContainsString($SITE->fullname, $data['conformance']);
    }

    /**
     * An administrator's own wording comes through, and the organisation overrides the
     * site name.
     */
    public function test_export_uses_what_was_entered(): void {
        $this->resetAfterTest();
        set_config('a11ystatement_org', 'Northgate College', 'theme_atrium');
        set_config('a11ystatement_conformance', accessibility_statement::NONE, 'theme_atrium');
        set_config('a11ystatement_known', '<p>Some older PDFs are not tagged.</p>', 'theme_atrium');
        set_config('a11ystatement_reviewed', '1 October 2026', 'theme_atrium');

        $data = accessibility_statement::export();
        $this->assertSame('Northgate College', $data['organisation']);
        $this->assertTrue($data['isnone']);
        $this->assertFalse($data['ispartial']);
        $this->assertStringContainsString('Northgate College', $data['conformance']);
        $this->assertTrue($data['hasknown']);
        $this->assertStringContainsString('not tagged', $data['known']);
        $this->assertSame('1 October 2026', $data['reviewed']);
    }

    /**
     * The contact becomes a link only when it is one. Anything else is shown as text
     * rather than turned into a URL on the administrator's behalf.
     */
    public function test_contact_is_linked_only_when_it_can_be(): void {
        $this->resetAfterTest();

        set_config('a11ystatement_contact', 'access@example.edu', 'theme_atrium');
        $this->assertSame('mailto:access@example.edu', accessibility_statement::export()['contacturl']);

        set_config('a11ystatement_contact', 'https://example.edu/access', 'theme_atrium');
        $this->assertStringContainsString('example.edu/access', accessibility_statement::export()['contacturl']);

        set_config('a11ystatement_contact', 'Ring the help desk on 1234', 'theme_atrium');
        $data = accessibility_statement::export();
        $this->assertSame('', $data['contacturl']);
        $this->assertTrue($data['hascontact']);
    }

    /**
     * An unrecognised stored claim falls back to the cautious one rather than to "full".
     */
    public function test_unknown_claim_falls_back_to_partial(): void {
        $this->resetAfterTest();
        set_config('a11ystatement_conformance', 'something_else', 'theme_atrium');
        $this->assertTrue(accessibility_statement::export()['ispartial']);
    }
}
