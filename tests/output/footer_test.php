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

namespace theme_atrium\output;

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the configurable footer.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\output\footer::class)]
final class footer_test extends \advanced_testcase {
    /**
     * Social links: one per line, platform|url, unknown platforms and non-http URLs dropped.
     */
    public function test_parse_social_links(): void {
        $links = footer::parse_social_links(
            "youtube|https://youtube.com/@school\r\n" .
            "X | https://x.com/school\n" .
            "myspace|https://myspace.com\n" .
            "github|javascript:alert(1)\n" .
            "broken line\n" .
            "\n" .
            "linkedin|http://linkedin.com/school"
        );
        $this->assertSame(['youtube', 'x', 'linkedin'], array_column($links, 'platform'));
        $this->assertSame('https://x.com/school', $links[1]['url']);
        $this->assertSame('fa-brands fa-x-twitter', $links[1]['icon']);
        $this->assertSame([], footer::parse_social_links(''));
    }

    /**
     * Columns render only when they have content, the legal line replaces its placeholders,
     * and the Moodle credit follows its setting.
     */
    public function test_export(): void {
        global $PAGE, $SITE;
        $this->resetAfterTest();
        $output = $PAGE->get_renderer('core');

        $data = (new footer())->export_for_template($output);
        $this->assertFalse($data['hascolumns']);
        $this->assertFalse($data['hassocial']);
        $this->assertTrue($data['showpoweredby']);
        $this->assertStringContainsString(userdate(time(), '%Y'), $data['legal']);
        $this->assertStringContainsString(format_string($SITE->fullname), $data['legal']);

        set_config('footercolumns', 3, 'theme_atrium');
        set_config('footercol1title', 'About', 'theme_atrium');
        set_config('footercol1html', '<p>Hello</p>', 'theme_atrium');
        set_config('footercol3html', '<ul><li>Late</li></ul>', 'theme_atrium');
        set_config('footerlegal', 'Made in {year} by {sitename}', 'theme_atrium');
        set_config('showpoweredby', 0, 'theme_atrium');
        set_config('sociallinks', 'github|https://github.com/example', 'theme_atrium');

        $data = (new footer())->export_for_template($output);
        $this->assertTrue($data['hascolumns']);
        $this->assertCount(2, $data['columns'], 'The empty second column is skipped');
        $this->assertSame('atrium-footer-columns-2', $data['columnclass']);
        $this->assertSame('About', $data['columns'][0]['title']);
        $this->assertStringContainsString('Hello', $data['columns'][0]['html']);
        $this->assertSame('', $data['columns'][1]['title']);
        $this->assertSame('Made in ' . userdate(time(), '%Y') . ' by ' . format_string($SITE->fullname), $data['legal']);
        $this->assertFalse($data['showpoweredby']);
        $this->assertTrue($data['hassocial']);
        $this->assertSame('github', $data['social'][0]['platform']);
    }
}
