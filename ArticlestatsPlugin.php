<?php

/**
 * @file ArticleStatsPlugin.php
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * @class ArticlestatsPlugin
 * @ingroup plugins_generic_articlestats
 *
 * @brief Shows view and download counts to readers.
 *
 *  - on the article landing page (hook Templates::Article::Main);
 *  - in article lists: issue table of contents, search results, categories
 *    (hook Templates::Issue::Issue::Article).
 *
 * Numbers come from the usage statistics OJS already collects. The plugin never
 * writes anything and does not affect statistics collection. Journal templates
 * are not modified, so an OJS upgrade will not wipe it out.
 */

namespace APP\plugins\generic\articlestats;

// Loaded either by the PSR-4 autoloader (OJS 3.4+) or by index.php (OJS 3.1-3.3);
// in both cases the shared helpers have to be available.
require_once(dirname(__FILE__) . '/ArticleStatsCompat.php');
require_once(dirname(__FILE__) . '/ArticleStatsData.php');

class ArticlestatsPlugin extends \ArticleStatsBasePlugin
{
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (defined('RUNNING_UPGRADE')) {
            return $success;
        }

        if ($success && $this->getEnabled()) {
            \articleStatsAddHook('Templates::Article::Main', [$this, 'showOnArticlePage']);
            \articleStatsAddHook('Templates::Issue::Issue::Article', [$this, 'showInList']);
            $this->addLocaleData();
        }

        return $success;
    }

    public function getName()
    {
        return 'ArticlestatsPlugin';
    }

    public function getDisplayName()
    {
        return __('plugins.generic.articlestats.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.articlestats.description');
    }

    /**
     * Counters on the article landing page.
     *
     * Template hooks ({call_hook}) pass a Smarty template object as the second
     * argument, not a TemplateManager: read template variables from it, but
     * fetch the manager separately for rendering.
     */
    public function showOnArticlePage($hookName, $args)
    {
        $smarty = $args[1];
        $output = &$args[2];

        $article = $smarty->getTemplateVars('article');
        if (!is_object($article) || !method_exists($article, 'getId')) {
            return false;
        }

        $output .= $this->render((int) $article->getId(), 'article.tpl');
        return false;
    }

    /** Compact counters in article lists. */
    public function showInList($hookName, $args)
    {
        $smarty = $args[1];
        $output = &$args[2];

        $article = $smarty->getTemplateVars('article');
        if (!is_object($article) || !method_exists($article, 'getId')) {
            return false;
        }

        // In an issue table of contents every article is known upfront,
        // so their statistics are fetched with a single query.
        $issueArticles = $smarty->getTemplateVars('articles');
        if (is_array($issueArticles) || $issueArticles instanceof \Traversable) {
            $ids = [];
            foreach ($issueArticles as $item) {
                if (is_object($item) && method_exists($item, 'getId')) {
                    $ids[] = (int) $item->getId();
                }
            }
            if ($ids) {
                \ArticleStatsData::preload($ids);
            }
        }

        $output .= $this->render((int) $article->getId(), 'summary.tpl');
        return false;
    }

    private function render($submissionId, $template)
    {
        $stats = \ArticleStatsData::forSubmission($submissionId);
        $templateManager = \articleStatsTemplateManager();

        $templateManager->assign([
            'articlestatsViews' => \ArticleStatsData::format($stats['views']),
            'articlestatsDownloads' => \ArticleStatsData::format($stats['downloads']),
            'articlestatsHasData' => ($stats['views'] + $stats['downloads']) > 0,
        ]);

        return $templateManager->fetch($this->getTemplateResource($template));
    }
}
