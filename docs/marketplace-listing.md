# Moodle Marketplace listing copy

Draft. Kept here so the wording is version controlled alongside the code it describes.
Field limits from the Marketplace provider documentation: short description 270
characters, of which plugin cards show the first 119; description 7,000; screenshots
128–480 px wide.

---

## Short description

### First 119 characters, as a standalone sentence

> A modern Boost child theme: sidebar, designed front page, course catalogue, dark
> mode, three login layouts, every page.

### Full short description

> A modern Boost child theme: sidebar, designed front page, course catalogue, dark
> mode, three login layouts, every page. Four template overrides, everything else SCSS, so
> it keeps working across Moodle releases. Nothing is fetched from outside your site.

---

## Description

### What it is

Atrium gives Moodle 5.1 and 5.2 the layout people pay for elsewhere: a left navigation
sidebar that collapses to icons, a designed front page, a course catalogue of cards, a
course landing page for enrolment, a dashboard with a greeting and progress tiles, a
course page made of cards with a focus mode, dark mode with a switch for every user, five
colour presets, three login layouts and a configurable footer. It is a Boost child, so
everything Boost does keeps working.

### Front page

Seven sections, each with its own settings and switch: a hero with image and two
buttons, feature blocks, a course showcase (the latest courses, a category, or courses
you choose), a numbers strip, testimonials, an about band and a call to action. The
navigation bar can go transparent over the hero.

### Catalogue and enrolment

Category and search pages become course cards or a list, with image, category,
teachers, enrolled count, progress for enrolled users and the price from fee or PayPal
enrolment; category chips, sorting, paging and search, the view remembered per user.
The enrolment page is a course landing page: banner, summary, facts, outline, teachers,
related courses, and the enrolment card kept in view.

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

Sections as cards, activities as rows with the activity icon in its purpose colour, and
a banner with the learner's progress and a resume link. Focus mode strips the course and
its activities down to the content with one switch, remembered per user. No template is
overridden here, which means editing mode, drag and drop, bulk editing and the activity
chooser are exactly Boost's.

### Site-wide

An announcement bar with four tones, dismissible per user until the text changes. A
quick links menu of icon links in the navigation bar. The profile page as a grid of
cards under a cover band. Gradebook, quiz, calendar, messaging, forum, admin pages and
the rest on the same tokens in both schemes.

### Login, header, footer

Three login layouts: the card centred over the image, or beside an image panel on the
left or the right, with panel copy, text above and below the form, a language menu
switch and "Create new account" as a button. The brand as logo, site name or both; a
standard or compact navigation bar, sticky or scrolling. Up to four footer columns, each
custom HTML, a menu, the social links or the contact details, with a footer logo and
privacy and terms links.

### Dark mode

A switch in the navigation bar and in the user menu, saved per user. The scheme is
applied on the server before the page is sent, so there is no flash of the wrong colours.
A site default of light, dark, or follow the device. Dark mode can be disabled site-wide.

### Presets and settings

Atrium, Indigo dark, Emerald, Rose and Slate. A brand colour overrides the preset's
accent; a sidebar tone overrides its tone. Inter (bundled) or the system font, heading
weight, text size, corner radius, raw SCSS before and after, custom CSS.

### Accessibility

Every accent in every preset passes WCAG 2.2 AA in both schemes, and a test computes
that from the preset data so it cannot drift. Focus rings are never removed. Nothing is
conveyed by colour alone. Reduced-motion preferences are respected.

### Privacy

Five user preferences (scheme, sidebar state, catalogue view, focus mode, dismissed
announcement), declared to the privacy API and included in exports. No tables. No
cookies of its own. Fonts are bundled; nothing is fetched from outside your site unless
you enter a Google Analytics 4 id, which is off by default.

### Built to last

Four Boost templates are overridden (drawers, navbar, footer, login layout) and two
renderers in the narrowest way (the catalogue and enrolment page, the profile cards).
Everything else, the course page included, is SCSS against the class names core already
emits. That is the whole point: fewer overrides, fewer things to break on a Moodle
release.

### Supported versions

Moodle 5.1 and 5.2, PHP 8.2–8.4, MySQL/MariaDB and PostgreSQL; every combination is
tested on every change. Moodle 4.5 is not supported: Boost's layout templates changed
with Bootstrap 5 between 4.5 and 5.x.

### Source, licence and support

GPLv3 or later. Inter typeface under the SIL Open Font License. Source, issue tracker
and continuous integration on GitHub.
