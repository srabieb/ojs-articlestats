# Article Stats — view and download counters for OJS

Shows how many times an article was viewed and its files downloaded — on the
article landing page and in article lists (issue table of contents, search
results, categories).

```
Views: 1 122 / PDF downloads: 274
```

- **Author:** Beibarys Sultan
- **License:** [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0.html)
- **Compatible with:** OJS 3.1.2 – 3.5 from a single codebase

| OJS | Status | Notes |
|---|---|---|
| 3.5.x | verified against sources, untested live | same hooks, namespaces and `metrics_submission` table as 3.4 |
| 3.4.x | **tested** — 3.4.0-8, PHP 8.2 | statistics in `metrics_submission`, `.po` locales (`en`) |
| 3.3.x | **tested** — 3.3.0-9, PHP 7.4 | statistics in `metrics`, `.po` locales (`en_US`) |
| 3.2.x | expected to work, untested | same plugin API and `.po` locales as 3.3 |
| 3.1.2+ | expected to work, untested | `.xml` locales are bundled for this branch |
| 3.0.x–3.1.1 | not supported | the required template hooks are missing |

Both required hooks — `Templates::Article::Main` and
`Templates::Issue::Issue::Article` — were verified to exist in the 3.1.2, 3.2.1,
3.3.0, 3.4.0 and 3.5.0 sources.

The plugin class lives in `APP\plugins\generic\articlestats`, so OJS 3.4 and
3.5 load it through the autoloader. `index.php` is only a fallback for 3.1–3.3,
which have no plugin autoloading; OJS deprecated that loader in 3.4 and the
plugin no longer relies on it. Code is written against PHP 7.0 syntax so it also
runs on the oldest supported branch.

## Why

OJS collects usage statistics but shows readers very little of it: the default
theme only draws a monthly downloads chart, and article landing page views are
not surfaced at all. This plugin puts both numbers where readers look.

## Where the numbers come from

The plugin reads the statistics OJS has already processed — the same data that
feeds the built-in reports:

| OJS branch | Table |
|---|---|
| 3.4.x, 3.5.x | `metrics_submission` |
| 3.1.x – 3.3.x | `metrics` |

Counted rows are `assoc_type = 1048585` (article landing page views) and
`assoc_type = 515` (galley file downloads). Values match the built-in
`publicationStats` service exactly.

Statistics are updated **once a day**, when the `UsageStatsLoader` scheduled
task processes the previous day's usage log. Numbers therefore lag by about a
day — this is how OJS works, not a plugin limitation. Figures cover the entire
period of collected statistics.

## Installation

1. Put the plugin into `plugins/generic/articlestats` of your OJS instance:

   ```bash
   cd plugins/generic
   git clone https://github.com/srabieb/ojs-articlestats.git articlestats
   ```

   The target directory **must** be named `articlestats` — OJS derives the
   plugin class name from it. Alternatively download a release archive from the
   [Releases page](https://github.com/srabieb/ojs-articlestats/releases) and
   unpack it there.
2. Make the files readable by the web server user:
   `chown -R www-data:www-data plugins/generic/articlestats`
3. Enable it: **Settings → Website → Plugins → Article Stats**.

No database changes are made. Disabling the plugin removes the counters and
leaves no traces behind.

## How it hooks in

| Where | Mechanism |
|---|---|
| Article landing page | core hook `Templates::Article::Main` |
| Article lists | core hook `Templates::Issue::Issue::Article` |
| Position under the keywords | small script shipped with the plugin's template |

Journal templates are **not** modified, so an OJS point release will not wipe
the plugin out. The only non-standard part is the script that moves the block
under the keywords: OJS has no extension point between the keywords and the
abstract, and patching the journal template would be lost on upgrade.

## Performance

For an issue table of contents the statistics of every listed article are loaded
with a single grouped query — not one query per row — and cached for the
duration of the request.

## Compatibility layer

`ArticleStatsCompat.php` resolves everything that differs between branches:
namespaced versus global classes, `Hook::add()` versus `HookRegistry::register()`,
the statistics table name, and database access (OJS 3.4 provides a working
Illuminate DB facade, 3.3 does not — there queries go through the DAO layer).

## Translations

Bundled locales: `en`, `ru`, `kk` — in both the short form used by OJS 3.4 and
the `xx_XX` form used by 3.3. To add a language, copy `locale/en/locale.po` and
translate the four strings.
