# Measurements

Numbers published about this theme, how they were taken, and what they do not say.

Taken 1 October 2026 on Moodle 5.2.2+ (Build 20260903), PHP 8.4.24, MySQL 8.4.3, with every
theme at its default settings on the same site.

## Compiled stylesheet

The whole stylesheet each theme serves, fetched from `theme/styles.php` and measured as
sent and after gzip, which is how a browser receives it.

| Theme | Raw | Gzipped | vs Boost |
|---|---|---|---|
| Boost (core) | 1,077,523 | 175,178 | — |
| Classic (core) | 1,083,720 | 176,118 | +0.5% |
| **Atrium** | **1,223,956** | **198,652** | **+13.4%** |
| Moove | 2,029,734 | 317,707 | +81.4% |

Atrium costs about 23 KB gzipped more than core Boost, and is about 37% smaller than Moove.
That is the whole price of the sidebar, the card dashboard, the catalogue, the enrolment
page, the seven presets, the dark scheme, the front page sections and the print sheet.

Boost Union is not in this table. It is not installed on the machine these numbers came
from, and a figure that has not been measured does not belong in a table.

## Requests to other hosts

What a browser is asked to fetch from somewhere that is not your own server, at default
settings.

| Theme | External fetches | Fonts |
|---|---|---|
| Boost (core) | none | system stack, nothing fetched |
| Classic (core) | none | system stack, nothing fetched |
| **Atrium** | **none** | 6 files, served by your site |
| Moove | none by default | 8 files served locally, **and Google Fonts if an administrator chooses a site font other than the default** |

To be fair to Moove: out of the box it fetches nothing. The Google Fonts call appears only
when the `fontsite` setting is changed from its default, at which point the browser is sent
to `fonts.googleapis.com` and `fonts.gstatic.com` on every page.

Atrium has no such path. Both bundled faces, Inter and Atkinson Hyperlegible, are served by
the site, and the only outbound call the theme can ever make is Google Analytics, which is
empty by default and documented in its own setting.

For an institution with a data protection officer this is the difference between a theme
that is a procurement question and one that is not.

## Fetching the next page early

Hovering a link asks the browser for that page before the click lands, with
`<link rel="prefetch">`. The browser fetches at idle priority, obeys the cache, and discards
the result if the guess was wrong.

Verified in a browser on this site:

| Link | Prefetched |
|---|---|
| A course page | yes |
| A link carrying a session key | **no** |
| Sign out | **no** |
| Another site | no |
| The page already open | no |
| 14 links hovered in a row | 8, the per-page budget |

The exclusions matter more than the inclusions. A link carrying a session key performs an
action: this theme's own palette offers "turn editing on" and "sign out" that way, and
fetching one in the background would do it. Prefetching is also skipped for anyone whose
browser reports a metered or slow connection.

## What these numbers do not say

**They do not say this is the fastest Moodle theme.** No page render, paint or
interaction timings were taken. Doing that honestly needs a quiet machine, a warmed cache,
many samples and every theme under the same load, and none of that happened here. A
stylesheet that is 23 KB larger than Boost and 119 KB smaller than Moove is a real fact
about download size and nothing more.

**They are one machine, one site, one dataset.** A site with different plugins, blocks and
content will produce different figures. The method is written down above so the numbers can
be taken again rather than believed.

## Taking them again

```
# Stylesheet size for each theme, at the current theme revision.
for t in atrium boost classic moove; do
  curl -s "http://yoursite/theme/styles.php/$t/<themerev>/all" -o /tmp/$t.css
  echo "$t raw=$(wc -c < /tmp/$t.css) gzip=$(gzip -c /tmp/$t.css | wc -c)"
done

# Anything fetched from another host, in the compiled CSS and in the theme's own source.
grep -ohE "url\(['\"]?https?://[^)]+" /tmp/$t.css
grep -rohE "https?://(fonts\.googleapis\.com|fonts\.gstatic\.com)" theme/<name>
```

`themerev` comes from `php admin/cli/cfg.php --name=themerev`. Run
`theme_reset_all_caches()` first: purging Moodle's caches alone does not always rebuild the
compiled stylesheet, which will otherwise hand you the previous theme's numbers.
