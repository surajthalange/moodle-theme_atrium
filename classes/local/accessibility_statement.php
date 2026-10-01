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

use moodle_url;

/**
 * The accessibility statement: a published page saying how accessible this site is.
 *
 * Public bodies in the UK and the EU are required to publish one, and institutions
 * currently write it by hand. Most of it is boilerplate, but one part is not: the list of
 * what the platform actually offers. That part is generated here from the site's live
 * settings, so turning the accessibility toolbar off also removes the claim that it exists.
 * The parts that only a human can answer, principally the known problems and the
 * complaints procedure, are left to the administrator and are not invented.
 *
 * Nothing here asserts conformance on the administrator's behalf: the conformance line is
 * a setting whose default is the cautious one.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class accessibility_statement {
    /** @var string Conformance claim: everything tested passes. */
    public const FULL = 'full';

    /** @var string Conformance claim: mostly passes, with the exceptions listed. */
    public const PARTIAL = 'partial';

    /** @var string Conformance claim: does not meet the standard. */
    public const NONE = 'none';

    /** @var string[] The claims an administrator may make. */
    public const CONFORMANCE = [self::FULL, self::PARTIAL, self::NONE];

    /**
     * Whether the site publishes a statement.
     *
     * Off by default: an empty statement is worse than none, because it reads as a claim.
     *
     * @return bool
     */
    public static function enabled(): bool {
        return (bool) get_config('theme_atrium', 'a11ystatement_enable');
    }

    /**
     * Where the statement lives.
     *
     * @return moodle_url
     */
    public static function url(): moodle_url {
        return new moodle_url('/theme/atrium/accessibility.php');
    }

    /**
     * What this site offers its users, read from the settings rather than asserted.
     *
     * @return array<int, array{text: string}>
     */
    public static function features(): array {
        $features = [];
        if (accessibility::enabled()) {
            $features[] = get_string('a11ystatement_feature_toolbar', 'theme_atrium');
            $features[] = get_string('a11ystatement_feature_font', 'theme_atrium');
        }
        if (scheme::enabled()) {
            $features[] = get_string('a11ystatement_feature_scheme', 'theme_atrium');
        }
        if (palette::enabled()) {
            $features[] = get_string('a11ystatement_feature_palette', 'theme_atrium');
        }
        // True of the theme however it is configured.
        $features[] = get_string('a11ystatement_feature_keyboard', 'theme_atrium');
        $features[] = get_string('a11ystatement_feature_zoom', 'theme_atrium');
        $features[] = get_string('a11ystatement_feature_fonts', 'theme_atrium');

        return array_map(fn(string $text): array => ['text' => $text], $features);
    }

    /**
     * Everything the page needs.
     *
     * @return array
     */
    public static function export(): array {
        global $SITE;

        $context = \context_system::instance();
        $config = fn(string $name): string => (string) (get_config('theme_atrium', $name) ?: '');
        $sitename = format_string($SITE->fullname, true, ['context' => $context]);

        $organisation = trim($config('a11ystatement_org'));
        if ($organisation === '') {
            $organisation = $sitename;
        }

        $claim = $config('a11ystatement_conformance');
        if (!in_array($claim, self::CONFORMANCE, true)) {
            $claim = self::PARTIAL;
        }

        $contact = trim($config('a11ystatement_contact'));
        $contacturl = '';
        if ($contact !== '') {
            // An email address or a page; anything else is ignored rather than linked blindly.
            if (strpos($contact, '@') !== false && strpos($contact, ' ') === false) {
                $contacturl = 'mailto:' . $contact;
            } else if (preg_match('#^(https?://|/)#i', $contact)) {
                $contacturl = (new moodle_url($contact))->out(false);
            }
        }

        $known = trim($config('a11ystatement_known'));
        $enforcement = trim($config('a11ystatement_enforcement'));
        $extra = trim($config('a11ystatement_extra'));
        $reviewed = trim($config('a11ystatement_reviewed'));

        return [
            'sitename' => $sitename,
            'siteurl' => (new moodle_url('/'))->out(false),
            'organisation' => $organisation,
            'conformance' => get_string('a11ystatement_conformance_' . $claim, 'theme_atrium', $organisation),
            'ispartial' => $claim === self::PARTIAL,
            'isnone' => $claim === self::NONE,
            'features' => self::features(),
            'hasknown' => $known !== '',
            'known' => format_text($known, FORMAT_HTML, ['context' => $context]),
            'hascontact' => $contact !== '',
            'contact' => $contact,
            'contacturl' => $contacturl,
            'hasenforcement' => $enforcement !== '',
            'enforcement' => format_text($enforcement, FORMAT_HTML, ['context' => $context]),
            'hasextra' => $extra !== '',
            'extra' => format_text($extra, FORMAT_HTML, ['context' => $context]),
            'hasreviewed' => $reviewed !== '',
            'reviewed' => s($reviewed),
            // The series only. This page is readable without signing in, and the exact
            // build number is of no use to a reader and of some use to an attacker.
            'moodleversion' => moodle_major_version(),
        ];
    }
}
