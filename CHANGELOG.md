# Changelog

All notable changes to this plugin are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.0.0] — 2026-08-19

First release.

### Added
- View and download counters on the article landing page.
- Compact counters in article lists: issue table of contents, search results,
  categories.
- Support for OJS 3.1.2 through 3.5 from a single codebase: PSR-4 class for
  3.4/3.5, index.php loader for 3.1-3.3, and a compatibility layer for hooks,
  database access and the statistics table name.
- Locales: English, Russian, Kazakh (both `xx` and `xx_XX` directory forms).
- Batch loading of statistics for article lists — one query per page instead of
  one per article.
