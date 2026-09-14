# Moodle Marketplace listing copy

Draft. Kept here so the wording is version controlled alongside the code it describes.
Field limits from the Marketplace provider documentation: short description 270
characters, of which plugin cards show the first 119; description 7,000; screenshots
128–480 px wide.

---

## Short description

### First 119 characters, as a standalone sentence

> A modern Boost child theme: left sidebar, card dashboard, card course page, dark mode,
> five presets, bundled fonts.

### Full short description

> A modern Boost child theme: left sidebar, card dashboard, card course page, dark mode,
> five presets, bundled fonts. Four template overrides, everything else SCSS, so it keeps
> working across Moodle releases. Nothing is fetched from outside your site.

---

## Description

### What it is

Atrium gives Moodle 5.1 and 5.2 the layout people pay for elsewhere: a left navigation
sidebar that collapses to icons, a dashboard with a greeting and progress tiles, a course
page made of cards, dark mode with a switch for every user, five colour presets and a
configurable footer. It is a Boost child, so everything Boost does keeps working.

### Navigation

The sidebar carries Moodle's own primary navigation and your custom menu. Expanded, it
shows icons and labels; collapsed, a 64-pixel icon rail with labels on hover. Each user's
choice is remembered. On a course page Boost's course index docks beside it, unchanged.
On phones the navigation bar's menu button opens Boost's drawer.

### Dashboard

A greeting band with the date and four tiles: courses in progress, courses completed,
items due this week, unread messages and notifications. Each tile links to the page it
counts; each can be turned off; so can the band. The counts are cached so the dashboard
is not slowed.

### Course page

Sections as cards, activities as rows with the activity icon in its purpose colour.
No template is overridden here, which means editing mode, drag and drop, bulk editing
and the activity chooser are exactly Boost's.

### Dark mode

A switch in the navigation bar and in the user menu, saved per user. The scheme is
applied on the server before the page is sent, so there is no flash of the wrong colours.
A site default of light, dark, or follow the device. Dark mode can be disabled site-wide.

### Presets and settings

Atrium, Indigo dark, Emerald, Rose and Slate. A brand colour overrides the preset's
accent; a sidebar tone overrides its tone. Text size, corner radius, login image and
wash, dashboard greeting and image, footer columns, social links and legal line, raw
SCSS before and after.

### Accessibility

Every accent in every preset passes WCAG 2.2 AA in both schemes, and a test computes
that from the preset data so it cannot drift. Focus rings are never removed. Nothing is
conveyed by colour alone. Reduced-motion preferences are respected.

### Privacy

Two user preferences (scheme and sidebar state), declared to the privacy API and
included in exports. No tables. No cookies of its own. Fonts are bundled; nothing is
fetched from outside your site.

### Built to last

Four Boost templates are overridden (drawers, navbar, footer, login layout). Everything
else, the course page included, is SCSS against the class names core already emits. That
is the whole point: fewer overrides, fewer things to break on a Moodle release.

### Supported versions

Moodle 5.1 and 5.2, PHP 8.2–8.4, MySQL/MariaDB and PostgreSQL; every combination is
tested on every change. Moodle 4.5 is not supported: Boost's layout templates changed
with Bootstrap 5 between 4.5 and 5.x.

### Source, licence and support

GPLv3 or later. Inter typeface under the SIL Open Font License. Source, issue tracker
and continuous integration on GitHub.
