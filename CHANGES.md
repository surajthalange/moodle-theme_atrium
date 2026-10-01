# Changes

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
