# Atrium

[![Moodle Plugin CI](https://github.com/surajthalange/moodle-theme_atrium/actions/workflows/ci.yml/badge.svg)](https://github.com/surajthalange/moodle-theme_atrium/actions/workflows/ci.yml)

A modern Boost child theme for Moodle 5.1 and 5.2: a left navigation sidebar that
collapses to icons, a card dashboard with a greeting and progress tiles, a card course
page, dark mode with a per-user switch, five colour presets, a centred login card over a
full-bleed image, and a configurable footer. Fonts are bundled; the theme makes no
requests to outside services.

- **Component:** `theme_atrium`
- **Moodle support:** 5.1 and 5.2 (see *Why not 4.5* below)
- **Parent:** Boost
- **Licence:** GPLv3 or later; the Inter typeface is under the SIL Open Font License (`fonts/OFL.txt`)

## What you get

| Area | Atrium |
|---|---|
| Navigation | A left sidebar carrying Moodle's primary navigation (Home, Dashboard, My courses, Site administration, your custom menu). Expanded at 248 px or collapsed to a 64 px icon rail; each user's choice is remembered. Below 768 px the navigation bar's menu button opens Boost's own drawer. |
| Course index | Boost's course index drawer, docked against the sidebar. Its behaviour, keyboard handling and preferences are core's, untouched. |
| Dashboard | A greeting band with the date and four tiles: courses in progress, courses completed, items due this week, unread messages and notifications. Each tile links to the page it counts, each can be turned off, and the whole band can be. |
| Course page | Sections as cards, activities as rows, with the activity icon in its purpose colour. No template is overridden: editing mode, drag and drop, bulk editing and the activity chooser are Boost's. |
| Dark mode | A per-user switch in the navigation bar and in the user menu, saved as a preference. The scheme is applied on the server before the page is sent, so there is no flash on load. A site default of light, dark, or *follow the device*. Dark mode can be disabled site-wide. |
| Presets | Five colour presets, each an accent and a sidebar tone. A brand colour setting overrides the accent; a sidebar tone setting overrides the tone. |
| Front page | A designed site home for visitors: hero with image and two buttons (or a carousel of up to five slides), feature blocks, a course showcase (latest, a category, or chosen courses), a numbers strip, testimonials, an about band and a call to action. Every section is a setting and has a switch. |
| Catalogue | Category and search pages as course cards or a list: image, category, teachers, enrolled count, activity count, progress for enrolled users, and the price from fee or PayPal enrolment. Category chips, sort, paging, a search box; the view is remembered per user. |
| Enrolment page | A course landing page around the enrolment forms: banner, summary, facts, outline, teachers, related courses, and the enrolment card kept in view. |
| Course banner and focus mode | A banner with the learner's progress and a resume link, and for teaching staff the course's numbers (enrolled, started, completed, activities). Focus mode strips the course and its activities down to the content, with one switch, remembered per user. |
| Announcement and quick links | A site-wide bar with four tones, dismissible per user until the text changes; a quick links menu of icon links in the navigation bar. |
| Profile | Core's profile sections as a grid of cards under a cover band. |
| Login | Three layouts: the card centred over the image, or beside an image panel on the left or the right, with panel copy, text above and below the form, the language menu switch, and "Create new account" as a button. Sign up, forgotten password and MFA share the card. |
| Header | Brand as logo, site name or both; standard or compact bar; sticky or scrolling; transparent over the front page hero; a recent courses menu; page width standard, narrow or wide. |
| Footer | Up to four columns, each custom HTML, a menu, the social links or the contact details; a footer logo, a legal line with `{year}` and `{sitename}`, privacy and terms links, the Moodle credit as a setting. |
| Type | Inter, bundled in four weights, or the system font. Heading weight, text size and corner radius are settings. |
| The long tail | Gradebook, quiz, question bank, calendar, messaging, forum, assignment, workshop, backup, participants, admin pages and the rest, on the same tokens in both schemes. |

## Where it sits beside Boost and the paid themes

| | Boost (core) | Paid themes | **Atrium** |
|---|---|---|---|
| Navigation | Top bar + course index | Left sidebar | Left sidebar, collapsible, course index docked |
| Dashboard | Blocks | Hero, tiles, styled cards | Hero, four tiles, styled cards |
| Dark mode | No | Some | Yes, per user, no flash |
| Presets | One | Many, plus a page builder | Five, no builder |
| Front page | Course list | Page builder | Seven designed sections, each a setting |
| Catalogue and enrolment page | Lists and forms | Cards and a landing page | Cards and a landing page |
| Fonts | System | Google Fonts, fetched | Bundled, nothing fetched |
| Price | Free | Paid | Free, GPL |

Atrium is deliberately the part of a paid theme that lives *in the theme*. Course
formats, form builders and page builders are separate plugin types and are not here.

## How it is built, and why it should survive upgrades

Every template override is a maintenance liability, because core may change what the
parent template expects. Atrium overrides **four** Boost templates:

| Template | Why |
|---|---|
| `theme_boost/drawers` | Renders the sidebar and the dashboard hero. Copied from Moodle 5.2; Boost's drawers are left as Boost renders them. |
| `theme_boost/navbar` | Drops the primary navigation (the sidebar carries it), shows the brand only on small screens, adds the scheme switch. |
| `theme_boost/footer` | A real footer instead of Boost's popover. Every link and fragment the popover contained is still rendered. |
| `theme_boost/login` | The three login layouts, on both 5.1 (form rendered into the page) and 5.2 (split `core/login_layout`). |

Two core renderers are overridden, in the narrowest way that works:

| Renderer | What changes |
|---|---|
| `core_course_renderer` | `course_category()`, `search_courses()` and `enrolment_options()` render the catalogue and the enrolment page from Atrium templates. Every other method is core's. |
| `core_user\output\myprofile\renderer` | `render_tree()` and `render_category()` wrap core's nodes in cards; the node markup is core's. |

Everything else, the course page and the whole long tail included, is SCSS written
against the class names core already emits. One renderer method is added
(`core_renderer::atrium_footer()`). Three hook callbacks (html attributes, head HTML,
user menu) and three small endpoints (scheme, focus mode, announcement dismissal) carry
the per-user state, each working without JavaScript.

Colours are CSS custom properties defined in `scss/atrium/_tokens.scss` for the light
scheme and redefined under `[data-bs-theme="dark"]`. Bootstrap 5.3's own colour-mode
variables are aligned to the same tokens, so core components follow the scheme without
Atrium chasing each of them; `_dark.scss` restates only the rules Boost compiles to fixed
greys.

### Why not 4.5

Between 4.5 and 5.x Boost moved from Bootstrap 4 to 5 and reshaped `drawers.mustache`
and `navbar.mustache`. A theme that overrides those templates cannot serve both from
one codebase. Supporting 4.5 means a separate `MOODLE_405_STABLE` branch; that is a
maintenance decision to take once 1.0 has users, and the tracker is the place to ask.

## Accessibility

- Every accent in every preset passes WCAG 2.2 AA (4.5:1) as white text on the accent, as
  the accent on the light surface, and as the dark-scheme link tint on the dark surface.
  `tests/local/presets_test.php` computes this from the preset data and fails if an
  accent is changed to one that does not.
- Focus rings are the accent, 2 px, offset 2 px, never removed.
- The sidebar, its collapse control, the scheme switch and the tiles are real links and
  buttons with names; collapsed labels are shown on hover and on focus.
- Nothing is conveyed by colour alone.
- `prefers-reduced-motion` shortens every transition.

## Privacy

Five user preferences, all declared to the privacy API and included in exports:
`theme_atrium_scheme` (light, dark or system), `theme_atrium_sidebar` (expanded or
collapsed), `theme_atrium_catalogueview` (grid or list), `theme_atrium_focusmode` (on or
off) and `theme_atrium_announcement` (a fingerprint of the dismissed announcement). No
tables, no cookies of its own. Fonts are bundled. The theme makes no request to any
outside service unless an administrator enters a Google Analytics 4 measurement id, in
which case the Google tag loads with IP anonymisation, not for site administrators, and
the setting says so.

## Settings

*Site administration → Appearance → Themes → Atrium.* Tabs: General (preset, brand
colour, text size, corner radius, dark mode, font, heading weight), Header (brand,
height, sticky, recent courses menu, page width), Sidebar (first-visit state, tone), Login page (image, wash, layout,
panel copy, text around the form, language menu, sign-up button), Course (banner, focus
mode, the numbers for teaching staff), Dashboard (hero, image, greeting, the four tiles),
Front page (every section, hero slides),
Catalogue (per page, sort, what the cards show, the enrolment page), Site (announcement,
quick links), Footer (columns and their types, contact details, logo, legal line,
privacy and terms links, Moodle credit), Advanced (raw SCSS before and after, custom
CSS, analytics).

## Development

```bash
# Checks the CI runs, from a moodle-plugin-ci checkout.
moodle-plugin-ci phplint && moodle-plugin-ci phpcs --max-warnings 0 && moodle-plugin-ci phpcpd
moodle-plugin-ci mustache && moodle-plugin-ci grunt --max-lint-warnings 0
moodle-plugin-ci phpunit --fail-on-warning && moodle-plugin-ci behat --profile chrome
```

AMD sources are in `amd/src`; rebuild with `grunt amd --root=theme/atrium` from the
Moodle root. SCSS partials are in `scss/atrium`; the theme compiles them after Boost's
sheet, so nothing is precompiled.

## Credits

Boost, the parent, is Moodle core. Edwiser RemUI, Moove and Learnr established what a
modern Moodle theme is expected to contain; nothing from them is used. Inter is by Rasmus
Andersson and contributors, under the SIL Open Font License 1.1.
