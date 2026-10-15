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

namespace theme_atrium\external;

use core_external\external_api;
use moodle_exception;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the palette search web service.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\external\search_palette::class)]
final class search_palette_test extends \advanced_testcase {
    /**
     * Call the service and put the answer through the declared return structure, which is
     * what the AJAX layer does and where a shape the structure does not describe fails.
     *
     * @param string $query
     * @param int $courseid
     * @return array
     */
    private function call(string $query = '', int $courseid = 0): array {
        return external_api::clean_returnvalue(
            search_palette::execute_returns(),
            search_palette::execute($query, $courseid, '/my/')
        );
    }

    /**
     * An ordinary user gets groups of results, and every item has every declared key.
     */
    public function test_search_returns_groups(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $result = $this->call();

        $this->assertNotEmpty($result['groups']);
        foreach ($result['groups'] as $group) {
            $this->assertNotSame('', $group['title']);
            foreach ($group['items'] as $item) {
                $this->assertSame(
                    ['label', 'url', 'icon', 'meta', 'action'],
                    array_keys($item)
                );
            }
        }
    }

    /**
     * A query narrows the answer to things that match it.
     */
    public function test_a_query_filters_the_results(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $labels = [];
        foreach ($this->call('calendar')['groups'] as $group) {
            $labels = array_merge($labels, array_column($group['items'], 'label'));
        }

        $this->assertNotEmpty($labels);
        foreach ($labels as $label) {
            $this->assertStringContainsStringIgnoringCase('calendar', $label);
        }
    }

    /**
     * The palette is for signed-in people; a guest is turned away rather than given a map
     * of the site.
     */
    public function test_guests_are_refused(): void {
        $this->resetAfterTest();
        $this->setGuestUser();

        $this->expectException(moodle_exception::class);
        search_palette::execute('', 0, '/my/');
    }

    /**
     * A site that has switched the palette off has no palette to search.
     */
    public function test_the_service_is_off_when_the_palette_is_off(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        set_config('navbar_palette', 0, 'theme_atrium');

        $this->expectException(moodle_exception::class);
        search_palette::execute('', 0, '/my/');
    }

    /**
     * The course id is a request, not a fact: it reaches the service from the browser, so
     * a course the caller cannot enter contributes nothing.
     */
    public function test_a_course_the_caller_cannot_enter_adds_nothing(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'name' => 'Welcome and housekeeping',
        ]);
        $this->setUser($this->getDataGenerator()->create_user());

        $titles = array_column($this->call('', $course->id)['groups'], 'title');
        $this->assertNotContains(get_string('palette_activities', 'theme_atrium'), $titles);
    }
}
