<?php

/**
 * @file index.php
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * @brief Legacy loader for OJS 3.1-3.3, which do not autoload plugin classes.
 *        OJS 3.4 and 3.5 pick the class up through PSR-4 and never read this file.
 */

require_once(dirname(__FILE__) . '/ArticlestatsPlugin.php');

return new APP\plugins\generic\articlestats\ArticlestatsPlugin();
