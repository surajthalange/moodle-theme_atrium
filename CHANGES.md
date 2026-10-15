# Changes

## 1.4.0 (2026-10-15)

Moodle 5.3, and the findings from the Marketplace review of 1.3.0 (MMR-347).

### Marketplace review

- **The command palette now checks that the caller may enter a course before listing its
  activities.** The course id reaches the palette from the browser, and the only test
  applied to it was modinfo's uservisible, which asks about an activity's visibility,
  availability and `mod/<name>:view` capability but not about the course: enrolment, guest
  access and course visibility are no part of it, and many module types define no view
  capability at all or grant it to every authenticated user. A signed-in user could
  therefore put another course id on the request and read back the names, types and links
  of its activities, including those of a hidden course. `can_access_course()` is now asked
  first, and a course id that is not a course is an empty answer rather than an exception.
  Reported in review as an approval blocker on security grounds; five tests cover it.
- **The palette search is a web service.** `palette.php` has gone; the search is
  `theme_atrium_search_palette` in `classes/external/`, called through `core/ajax`, with
  its arguments and its answer described by the external API rather than by hand. The
  endpoints that are forms, and work without JavaScript, stay forms.
- **The palette results are a template.** `theme_atrium/palette_results` and
  `theme_atrium/palette_result_body` replace the markup the module used to assemble in
  strings, which also retires the module's own HTML escaping.
- **Google Analytics is declared in the privacy provider.** With a measurement id set,
  every page served to anyone who is not a site administrator loads Google's tag; the
  provider now records that as an external location with the fields that are sent, so the
  site's privacy registry describes it.
- **The bundled typefaces are declared in `thirdpartylibs.xml`:** Inter 4.001 in four
  weights and Atkinson Hyperlegible 1.006 in two, both OFL 1.1.
- **The four `cachedef_` strings `db/caches.php` declares are now defined.**

### Moodle 5.3

- The course index drawer keeps its controls on 5.3. theme_boost/drawer.mustache
  renamed the block they are placed in from drawerheadercontent to drawercontrols,
  and Mustache discards an override for a block the parent does not declare without
  raising anything, so on 5.3 the controls simply stopped rendering. This theme now
  declares both names; the running Moodle uses the one it knows and ignores the other,
  which keeps 5.1 and 5.2 working from the same template.
- The light/dark switch keeps its icon in the user menu on 5.3. 5.3 replaced
  add_navitem() with add_menu_item() and typed value objects and deprecated the old
  call; its compatibility shim reads titleattribute and pixicon, where the stdClass this
  theme passed carries titleidentifier and pix, so the item survived but arrived without
  its icon and raised a deprecation notice. The callback now uses the new API where it
  exists and the old one otherwise, from the same source.
- The secondary navigation uses 5.3's React nav where 5.3 is what is running. The new
  core/secondarymoremenu partial does not exist before 5.3, so rather than check the
  release the template checks the data: core only exports reactprops on 5.3, and the
  partial that needs it is only reached when it is there. Older releases keep
  core/moremenu and never try to load a partial they do not have. The nav pills 5.3
  draws are styled to match the underline the Bootstrap nav links already had, so the
  course tabs look the same on every supported release.

  The approach is Michael Milette's, from theme_trema. I had concluded this could not be
  done without a version check and said so; he showed otherwise.
- 5.3 added to the CI matrix on PHP 8.3 and 8.4 against both databases, those versions
  read from 5.3's own workflow files.

## 1.3.0 (2026-10-01)

Paper, empty pages, and the next page before you ask for it.

- Print: pages print as content. Navigation, drawers, controls and the footer are dropped,
  the dark scheme flattens to ink on white instead of solid black panels, sections
  collapsed on screen are opened for paper, and tables keep their borders. A setting
  prints the address after each link that leads off the site, off by default.
- Empty states: a dashboard with no enrolments says what to do next instead of showing
  four zeros, and says something different depending on whether the person may create a
  course, may browse for one, or has enrolment arranged for them.
- Prefetching: resting the pointer on a link asks the browser for that page early, within
  a per-page budget. Links carrying a session key are never fetched this way, because
  those do something rather than go somewhere, and nothing is fetched for anyone whose
  browser reports a metered or slow connection. On by default, with a setting.
- Measurements of stylesheet weight against Boost, Classic and Moove, with the method,
  published in docs/BENCHMARKS.md. No claim is made about render speed, which was not
  measured.

## 1.2.0 (2026-10-01)

The palette does things, and the site can publish an accessibility statement.

- Commands: the Ctrl+K box now runs things as well as finding them, including turning
  editing on, switching colour scheme, entering focus mode, changing any accessibility
  setting, purging caches and signing out. A command posts with a session key and is
  checked against the user's own permissions at the moment it runs, not when it was
  listed. With an empty box the first result is always a place to go, never something
  that changes the site.
- Accessibility statement: a published statement at its own address, readable without
  signing in, of the kind public bodies in the UK and EU are required to have. The list of
  what the site offers is generated from the live settings rather than asserted, so
  turning a feature off also removes the claim that it exists; the parts only a human can
  answer are left to the administrator. Off by default, linked from the footer when on,
  and forced login is honoured.

## 1.1.0 (2026-09-15)

Every page, not only the shell. Moodle 5.1 and 5.2.

- Quick start: one click on a fresh site sets up the front page, footer, quick links and
  login page from the site's own details; one click resets it.
- Search and go: Ctrl+K or a navigation bar button finds courses, activities in the
  current course, common pages and (for administrators) admin pages.
- Accessibility toolbar: text size, a reading font (Atkinson Hyperlegible, bundled under
  the OFL), high contrast and reduced motion, per user, applied on the server.
- Front page: seven designed sections (hero, features, course showcase, numbers,
  testimonials, about, call to action), each with its own settings and switch, shown to
  visitors and to logged-in users alike; the navigation bar can go transparent over the
  hero; the hero becomes a carousel of up to five slides, with autoplay, interval and
  image wash settings.
- Course catalogue: category and search pages as course cards or a list, with category
  chips, sorting, paging, enrolment and activity counts, progress for enrolled users and
  the price from fee or PayPal enrolment; the view is remembered per user.
- Enrolment page: a course landing page around the enrolment forms, with a banner, the
  facts, the course outline, the teachers and related courses.
- Course page: a banner with the learner's progress and a resume link, and the course's
  numbers (enrolled, started, completed, activities) for teaching staff; focus mode, which
  strips the course and its activities down to the content and is remembered per user;
  activity page polish.
- Dashboard block polish; a site-wide announcement bar with four tones, dismissible per
  user until the text changes; a quick links menu in the navigation bar.
- Profile page: core's sections as a grid of cards under a cover band.
- Login page: three layouts (card centred over the image, image panel left, image panel
  right) with panel heading and text, text above and below the form, the language menu
  switch and "Create new account" as a button; sign up, forgotten password and MFA share
  the card.
- Header: brand as logo, site name or both; standard or compact navigation bar; sticky or
  scrolling; a recent courses menu; page width standard, narrow or wide.
- Footer: up to four columns, each custom HTML, a menu, the social links or the contact
  details; a footer logo, privacy and terms links.
- Typography: Inter (bundled) or the system font; heading weight.
- Brand: the default preset is now Atrium teal (#0f6e73), with a dark-sidebar twin; the
  1.0 indigo stays as the Indigo and Indigo dark presets. The login image, dark sidebar
  tone and page background follow the new palette.
- Advanced: custom CSS; an optional Google Analytics 4 measurement id, off by default.
- Seven more user preferences (catalogue view, focus mode, dismissed announcement, and
  the four accessibility choices), all declared to the privacy API.

## 1.0.0 (2026-09-14)

First release. Moodle 5.1 and 5.2.

- Left navigation sidebar carrying the primary navigation and the custom menu; expanded
  or collapsed to an icon rail, remembered per user. Boost's course index docks beside it.
- Dashboard hero: greeting, date, and tiles for courses in progress, courses completed,
  items due this week, and unread messages and notifications. Counts are cached for five
  minutes and refreshed at once on completion events.
- Dark mode: per-user switch in the navigation bar and the user menu, site default of
  light, dark or follow the device, applied server-side with no flash. Can be disabled.
- Five colour presets (Atrium, Indigo dark, Emerald, Rose, Slate) with brand colour and
  sidebar tone overrides; every accent passes WCAG AA in both schemes, checked by a test.
- Course page as cards without a template override, so editing mode, drag and drop and
  the activity chooser are Boost's.
- Centred login card over a full-bleed image with a configurable colour wash.
- Footer with up to three content columns, social links, legal line and Moodle credit.
- Inter typeface bundled under the OFL; no external requests.
- Text size and corner radius settings; raw SCSS before and after.
- Privacy provider for the two user preferences.
