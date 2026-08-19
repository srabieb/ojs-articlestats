<?php

/**
 * @file index.php
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * @brief Wrapper that loads the plugin. Required by OJS 3.3 and used by 3.4 as
 *        the fallback loader for plugins whose classes are not PSR-4 namespaced.
 */

require_once(dirname(__FILE__) . '/ArticleStatsCompat.php');
require_once(dirname(__FILE__) . '/ArticleStatsData.php');
require_once(dirname(__FILE__) . '/ArticleStatsPlugin.php');

return new ArticleStatsPlugin();
