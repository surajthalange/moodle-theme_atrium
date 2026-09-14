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
| Login | One card centred over a full-bleed image with a colour wash whose colour and strength are settings. |
| Footer | Up to three content columns, social links, a legal line with `{year}` and `{sitename}`, and the Moodle credit as a setting. |
| Type | Inter, bundled in four weights, with a system fallback stack. Text size and corner radius are settings. |

## Where it sits beside Boost and the paid themes

| | Boost (core) | Paid themes | **Atrium** |
|---|---|---|---|
| Navigation | Top bar + course index | Left sidebar | Left sidebar, collapsible, course index docked |
| Dashboard | Blocks | Hero, tiles, styled cards | Hero, four tiles, styled cards |
| Dark mode | No | Some | Yes, per user, no flash |
| Presets | One | Many, plus a page builder | Five, no builder |
| Front page builder | No | Yes | No (planned for 1.1) |
| Fonts | System | Google Fonts, fetched | Bundled, nothing fetched |
| Price | Free | Paid | Free, GPL |

Atrium is deliberately the part of a paid theme that lives *in the theme*. Course
formats, form builders and page builders are separate plugin types and are not here.

## How it is built, and why it should survive upgrades

Every template override is a maintenance liability, because core may change what the
parent template expects. Atrium overrides **four**:

| Template | Why |
|---|---|
| `theme_boost/drawers` | Renders the sidebar and the dashboard hero. Copied from Moodle 5.2; Boost's drawers are left as Boost renders them. |
| `theme_boost/navbar` | Drops the primary navigation (the sidebar carries it), shows the brand only on small screens, adds the scheme switch. |
| `theme_boost/footer` | A real footer instead of Boost's popover. Every link and fragment the popover contained is still rendered. |
| `core/login_layout` | The centred card. |

Everything else, the course page included, is SCSS written against the class names core
already emits. One renderer method is added (`core_renderer::atrium_footer()`), no
renderer method is overridden.

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

Two user preferences, both declared to the privacy API and included in exports:
`theme_atrium_scheme` (light, dark or system) and `theme_atrium_sidebar` (expanded or
collapsed). No tables, no external requests, no cookies of its own.

## Settings

*Site administration → Appearance → Themes → Atrium.* Tabs: General (preset, brand
colour, text size, corner radius, dark mode), Sidebar (first-visit state, tone), Login
page (image, wash colour, wash strength), Dashboard (hero, image, greeting, the four
tiles), Footer (columns, social links, legal line, Moodle credit), Advanced (raw SCSS
before and after).

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
