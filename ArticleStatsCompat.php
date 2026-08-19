<?php

/**
 * @file ArticleStatsCompat.php
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * @brief OJS 3.3 / 3.4 compatibility layer.
 *
 * OJS 3.4 moved plugin classes into namespaces (PKP\plugins\Hook,
 * APP\template\TemplateManager, …) while 3.3 uses global classes loaded with
 * import(). Usage statistics also live in different tables: `metrics` in 3.3,
 * `metrics_submission` in 3.4. Everything version specific is resolved here so
 * that the plugin itself stays version agnostic.
 */

if (!function_exists('articleStatsClass')) {

    /** Resolves a class name: namespaced first (3.4), then the legacy global one (3.3). */
    function articleStatsClass(string $namespaced, string $legacy, ?string $import = null): string
    {
        if (class_exists($namespaced)) {
            return $namespaced;
        }
        if ($import !== null && function_exists('import')) {
            import($import);
        }
        return $legacy;
    }

    /** Registers a hook through whichever API this OJS provides. */
    function articleStatsAddHook(string $hookName, callable $callback): void
    {
        if (class_exists('\PKP\plugins\Hook')) {
            \PKP\plugins\Hook::add($hookName, $callback);
            return;
        }
        if (class_exists('HookRegistry')) {
            HookRegistry::register($hookName, $callback);
        }
    }

    /** Current TemplateManager instance. */
    function articleStatsTemplateManager()
    {
        $application = articleStatsClass('\APP\core\Application', 'Application', 'classes.core.Application');
        $templateManager = articleStatsClass('\APP\template\TemplateManager', 'TemplateManager', 'classes.template.TemplateManager');

        return $templateManager::getManager($application::get()->getRequest());
    }

    /** Association type of an article landing page view. */
    function articleStatsViewAssocType(): int
    {
        if (defined('ASSOC_TYPE_SUBMISSION')) {
            return (int) constant('ASSOC_TYPE_SUBMISSION');
        }
        $application = articleStatsClass('\APP\core\Application', 'Application');

        return (int) $application::ASSOC_TYPE_SUBMISSION;
    }

    /** Association type of a galley file download. */
    function articleStatsDownloadAssocType(): int
    {
        if (defined('ASSOC_TYPE_SUBMISSION_FILE')) {
            return (int) constant('ASSOC_TYPE_SUBMISSION_FILE');
        }
        $application = articleStatsClass('\APP\core\Application', 'Application');

        return (int) $application::ASSOC_TYPE_SUBMISSION_FILE;
    }

    /**
     * Runs a SELECT and returns rows as objects.
     *
     * OJS 3.4 exposes a working Illuminate DB facade; in 3.3 the facade root is
     * not set, so queries have to go through the DAO layer.
     */
    function articleStatsSelect(string $sql, array $params = []): array
    {
        if (class_exists('\Illuminate\Support\Facades\DB')) {
            try {
                return \Illuminate\Support\Facades\DB::select($sql, $params);
            } catch (\Throwable $e) {
                // 3.3: facade root has not been set — fall through to the DAO layer
            }
        }

        $registry = articleStatsClass('\PKP\db\DAORegistry', 'DAORegistry', 'lib.pkp.classes.db.DAORegistry');
        $dao = $registry::getDAO('SubmissionDAO');

        $rows = [];
        foreach ($dao->retrieve($sql, $params) as $row) {
            $rows[] = (object) (array) $row;
        }

        return $rows;
    }

    /**
     * Name of the table holding processed usage statistics.
     * OJS 3.4 splits metrics per object type; 3.3 keeps a single `metrics` table.
     */
    function articleStatsMetricsTable(): string
    {
        static $table = null;
        if ($table !== null) {
            return $table;
        }

        $found = articleStatsSelect("SHOW TABLES LIKE 'metrics_submission'");

        return $table = $found ? 'metrics_submission' : 'metrics';
    }
}

// A single base class name for both branches, so the plugin can simply extend it.
if (!class_exists('ArticleStatsBasePlugin')) {
    if (class_exists('\PKP\plugins\GenericPlugin')) {
        class_alias('\PKP\plugins\GenericPlugin', 'ArticleStatsBasePlugin');
    } else {
        if (function_exists('import')) {
            import('lib.pkp.classes.plugins.GenericPlugin');
        }
        class_alias('GenericPlugin', 'ArticleStatsBasePlugin');
    }
}
