{**
 * plugins/generic/articlestats/templates/article.tpl
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * View and download counters on the article landing page.
 *
 * The markup mirrors the neighbouring sections (section.item > span.value),
 * otherwise the block falls outside the column padding. It is rendered by the
 * core hook Templates::Article::Main (after the abstract) because OJS has no
 * extension point between keywords and abstract; a small script then moves it
 * under the keywords so that journal templates stay untouched. If an article
 * has no keywords the block simply stays where it was rendered.
 *}

<section class="item articlestats">
	<span class="value">
		{translate key="plugins.generic.articlestats.views"}: {$articlestatsViews}
		/ {translate key="plugins.generic.articlestats.downloads"}: {$articlestatsDownloads}
	</span>
</section>

<script>
	(function () {ldelim}
		{literal}
		function moveArticleStats() {
			var block = document.querySelector('.main_entry .item.articlestats');
			var keywords = document.querySelector('.main_entry .item.keywords');
			if (block && keywords && keywords.nextElementSibling !== block) {
				keywords.parentNode.insertBefore(block, keywords.nextElementSibling);
			}
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', moveArticleStats);
		} else {
			moveArticleStats();
		}
		{/literal}
	{rdelim})();
</script>
