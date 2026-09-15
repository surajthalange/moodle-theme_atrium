# Changes

## 1.1.0 (2026-09-15)

Every page, not only the shell. Moodle 5.1 and 5.2.

- Front page: seven designed sections (hero, features, course showcase, numbers,
  testimonials, about, call to action), each with its own settings and switch, shown to
  visitors and to logged-in users alike; the navigation bar can go transparent over the
  hero.
- Course catalogue: category and search pages as course cards or a list, with category
  chips, sorting, paging, enrolment counts, progress for enrolled users and the price from
  fee or PayPal enrolment; the view is remembered per user.
- Enrolment page: a course landing page around the enrolment forms, with a banner, the
  facts, the course outline, the teachers and related courses.
- Course page: a banner with the learner's progress and a resume link; focus mode, which
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
  scrolling.
- Footer: up to four columns, each custom HTML, a menu, the social links or the contact
  details; a footer logo, privacy and terms links.
- Typography: Inter (bundled) or the system font; heading weight.
- Advanced: custom CSS; an optional Google Analytics 4 measurement id, off by default.
- Three more user preferences (catalogue view, focus mode, dismissed announcement), all
  declared to the privacy API.

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
