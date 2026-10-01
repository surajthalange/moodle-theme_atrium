# Moodle Marketplace listing copy

Draft. Kept here so the wording is version controlled alongside the code it describes.
Field limits from the Marketplace provider documentation: short description 270
characters, of which plugin cards show the first 119; description 7,000; screenshots
128–480 px wide.

---

## Short description

### First 119 characters, as a standalone sentence

> A modern Boost child theme: Ctrl+K commands, an accessibility statement, one-click setup, dark mode, print that works.

### Full short description

> A modern Boost child theme: Ctrl+K search that also runs commands, a generated accessibility statement, one-click setup, designed front page, catalogue, dark mode, print that works. Four template overrides. Nothing fetched from outside your site.

---

## Description

### What it is

Atrium gives Moodle 5.1 and 5.2 the layout people pay for elsewhere: a collapsible left
sidebar, a designed front page, a catalogue of course cards, a landing page for enrolment, a
dashboard with progress tiles, a course page of cards with a focus mode, dark mode per user,
seven colour presets, three login layouts and a configurable footer. It is a Boost child, so
everything Boost does keeps working.

### Four things no other free theme does

**Quick start.** One click on a fresh site sets up the front page, footer, quick links and
login page from the site's own name, summary, support contact and courses. One click puts
it back. **Search and go.** Ctrl+K from any page finds courses, activities in the current
course, the pages people go to and, for administrators, admin pages, with the user's own
permissions. **Accessibility toolbar.** Each user can choose a larger text size, a reading
font (Atkinson Hyperlegible, bundled), high contrast and reduced motion, remembered and
applied on every page. **Accessibility statement.** Public bodies in the UK and EU
have to publish one and currently write it by hand. Atrium builds it, and the list of what
the site offers is generated from your live settings rather than asserted, so turning a
feature off also removes the claim that it exists. Readable without signing in. Off by
default.

### Front page

Seven sections, each with its own settings and switch: a hero with image and two
buttons, or a carousel of up to five slides, feature blocks, a course showcase (the latest courses, a category, or courses
you choose), a numbers strip, testimonials, an about band and a call to action. The
navigation bar can go transparent over the hero.

### Catalogue and enrolment

Category and search pages become course cards or a list: image, category, teachers, enrolled
and activity counts, progress, and the price from fee or PayPal enrolment, with category
chips, sorting, paging and search remembered per user. The enrolment page becomes a landing
page: banner, summary, facts, outline, teachers and the enrolment card kept in view.

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

Sections as cards, activities as rows with the activity icon in its purpose colour, and a
banner carrying the learner's progress and a resume link, or the course's numbers for staff.
Focus mode strips a course to its content with one switch, remembered per user. No template
is overridden here, so editing, drag and drop and the activity chooser stay Boost's.

### Site-wide

An announcement bar with four tones, dismissible per user until the text changes. A
quick links menu of icon links in the navigation bar. The profile page as a grid of
cards under a cover band. Gradebook, quiz, calendar, messaging, forum, admin pages and
the rest on the same tokens in both schemes.

### Commands

The same Ctrl+K box runs things as well as finding them: turn editing on, switch scheme,
enter focus mode, change an accessibility setting, purge caches, sign out. A command posts
with a session key and is re-checked against your permissions when it runs. With an empty
box the first result is always a place to go, never something that changes the site.

### Print

Pages print as content on paper: navigation, drawers, controls and footer dropped, the dark
scheme flattened to ink on white rather than solid black, collapsed sections opened, tables
keeping their borders. Link addresses after links, off by default.

### Empty states

A dashboard with nothing on it says what to do next rather than showing four zeros, and says
something different depending on whether you may create a course, browse for one, or have
enrolment arranged for you.

### Speed

Fonts are served by your site, so no page waits on an outside host. Hovering a link fetches
that page early, within a budget, never for a link carrying a session key, and not at all on
a metered connection. The stylesheet is 199 KB gzipped against Boost's 175 KB. Method and
figures are in the repository; nothing is claimed about render speed, which was not measured.

### Login, header, footer

Three login layouts: the card centred over the image, or beside an image panel left or
right, with panel copy, text around the form and a language switch. The brand as logo, name
or both; a standard or compact navigation bar, sticky or scrolling; page width standard,
narrow or wide. Up to four footer columns, each custom HTML, a menu, social links or
contact details.

### Dark mode

A switch in the navigation bar and in the user menu, saved per user. The scheme is
applied on the server before the page is sent, so there is no flash of the wrong colours.
A site default of light, dark, or follow the device. Dark mode can be disabled site-wide.

### Presets and settings

Atrium teal (light or dark sidebar), Indigo (light or dark), Emerald, Rose and Slate. A
brand colour overrides the preset's
accent; a sidebar tone overrides its tone. Inter (bundled) or the system font, heading
weight, text size, corner radius, raw SCSS before and after, custom CSS.

### Accessibility

Every accent in every preset passes WCAG 2.2 AA in both schemes, and a test computes
that from the preset data so it cannot drift. Focus rings are never removed. Nothing is
conveyed by colour alone. Reduced-motion preferences are respected. A published accessibility statement can be generated from the site's own settings.

### Privacy

Nine user preferences (scheme, sidebar, catalogue view, focus mode, dismissed announcement
and the four accessibility choices), declared to the privacy API and included in exports. No
tables, no cookies of its own. Fonts are bundled; nothing is fetched from outside your site
unless you enter a Google Analytics 4 id, which is off by default.

### Built to last

Four Boost templates are overridden (drawers, navbar, footer, login) and two renderers in
the narrowest way. Everything else, the course page included, is SCSS against class names
core already emits. Fewer overrides, fewer things to break on a Moodle release.

### Supported versions

Moodle 5.1 and 5.2, PHP 8.2–8.4, MySQL/MariaDB and PostgreSQL; every combination is
tested on every change. Moodle 4.5 is not supported: Boost's layout templates changed
with Bootstrap 5 between 4.5 and 5.x.

### Source, licence and support

GPLv3 or later. Inter and Atkinson Hyperlegible typefaces under the SIL Open Font License. Source, issue tracker
and continuous integration on GitHub.
