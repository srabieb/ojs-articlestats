{**
 * plugins/generic/articlestats/templates/summary.tpl
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * Counters in article lists: issue table of contents, search results, categories.
 * Styled like the other journals on this server — a plain line, no box:
 * "Views: 104 / PDF downloads: 82".
 *
 * Articles without any recorded usage show nothing, to avoid rows of zeros.
 *}

{if $articlestatsHasData}
	<div class="articlestats-summary" style="margin-top: 5px">
		{translate key="plugins.generic.articlestats.views"}: {$articlestatsViews}
		/ {translate key="plugins.generic.articlestats.downloads"}: {$articlestatsDownloads}
	</div>
{/if}
