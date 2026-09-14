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

namespace theme_atrium\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use theme_atrium\local\scheme;
use theme_atrium\output\sidebar;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the privacy provider.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\privacy\provider::class)]
final class provider_test extends provider_testcase {
    /**
     * Two preferences are declared, each with a string that exists.
     */
    public function test_metadata(): void {
        $collection = provider::get_metadata(new collection('theme_atrium'));
        $items = $collection->get_collection();
        $this->assertCount(2, $items);
        $names = array_map(fn($item) => $item->get_name(), $items);
        $this->assertSame([scheme::PREFERENCE, sidebar::PREFERENCE], $names);
        foreach ($items as $item) {
            $this->assertInstanceOf(\core_privacy\local\metadata\types\user_preference::class, $item);
            $this->assertNotEmpty(get_string($item->get_summary(), 'theme_atrium'));
        }
    }

    /**
     * Export contains the saved preferences with readable descriptions, and nothing for a user without any.
     */
    public function test_export_user_preferences(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        set_user_preference(scheme::PREFERENCE, scheme::DARK, $user);
        set_user_preference(sidebar::PREFERENCE, sidebar::COLLAPSED, $user);

        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $this->assertTrue($writer->has_any_data());
        $preferences = $writer->get_user_preferences('theme_atrium');
        $this->assertSame(scheme::DARK, $preferences->{scheme::PREFERENCE}->value);
        $this->assertSame(get_string('scheme:dark', 'theme_atrium'), $preferences->{scheme::PREFERENCE}->description);
        $this->assertSame(sidebar::COLLAPSED, $preferences->{sidebar::PREFERENCE}->value);
        $this->assertSame(get_string('sidebar:collapsed', 'theme_atrium'), $preferences->{sidebar::PREFERENCE}->description);

        writer::reset();
        provider::export_user_preferences($other->id);
        $this->assertFalse(writer::with_context(\context_system::instance())->has_any_data());
    }

    /**
     * Deleting the user through core removes the preferences with them.
     */
    public function test_preferences_go_with_the_user(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        set_user_preference(scheme::PREFERENCE, scheme::DARK, $user);
        set_user_preference(sidebar::PREFERENCE, sidebar::COLLAPSED, $user);
        $where = "userid = ? AND name LIKE 'theme_atrium%'";
        $this->assertSame(2, $DB->count_records_select('user_preferences', $where, [$user->id]));

        delete_user($user);
        $this->assertSame(0, $DB->count_records_select('user_preferences', $where, [$user->id]));
    }
}
