# Changes

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
