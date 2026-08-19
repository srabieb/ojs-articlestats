<?php

/**
 * @file ArticleStatsData.php
 *
 * @copyright (c) 2026 Beibarys Sultan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 * @brief Reads accumulated article usage from the OJS statistics tables.
 *
 * The table differs between branches (`metrics` in 3.3, `metrics_submission`
 * in 3.4), but the columns used here — submission_id, assoc_type, metric —
 * are the same. This class only reads; it never writes and does not interfere
 * with statistics collection.
 */

class ArticleStatsData
{
    /** Per-request cache, so a table of contents hits the database once per article. */
    private static $cache = [];

    /**
     * Views and downloads for a single article.
     *
     * @return array{views: int, downloads: int}
     */
    public static function forSubmission($submissionId)
    {
        $submissionId = (int) $submissionId;
        if (isset(self::$cache[$submissionId])) {
            return self::$cache[$submissionId];
        }

        self::load([$submissionId]);

        return self::$cache[$submissionId];
    }

    /**
     * Loads several articles with one query. Used for issue tables of contents
     * and search results, to avoid one query per listed article.
     */
    public static function preload(array $submissionIds)
    {
        $missing = [];
        foreach ($submissionIds as $id) {
            $id = (int) $id;
            if (!isset(self::$cache[$id])) {
                $missing[] = $id;
            }
        }

        if ($missing) {
            self::load($missing);
        }
    }

    /** Fetches the given articles and fills the cache. */
    private static function load(array $submissionIds)
    {
        $table = articleStatsMetricsTable();
        $placeholders = implode(',', array_fill(0, count($submissionIds), '?'));
        $params = array_merge(
            [articleStatsViewAssocType(), articleStatsDownloadAssocType()],
            $submissionIds
        );

        $sql = "SELECT submission_id,"
            . " SUM(CASE WHEN assoc_type = ? THEN metric ELSE 0 END) AS views,"
            . " SUM(CASE WHEN assoc_type = ? THEN metric ELSE 0 END) AS downloads"
            . " FROM {$table} WHERE submission_id IN ({$placeholders})"
            . ' GROUP BY submission_id';

        try {
            foreach (articleStatsSelect($sql, $params) as $row) {
                self::$cache[(int) $row->submission_id] = [
                    'views' => (int) $row->views,
                    'downloads' => (int) $row->downloads,
                ];
            }
        } catch (Throwable $e) {
            error_log('articlestats: ' . $e->getMessage());
        }

        // Articles without usage are cached too, otherwise we would query them again.
        foreach ($submissionIds as $id) {
            if (!isset(self::$cache[$id])) {
                self::$cache[$id] = ['views' => 0, 'downloads' => 0];
            }
        }
    }

    /** Thousands separator: 12 345 reads better than 12345. */
    public static function format($number)
    {
        return number_format((int) $number, 0, ',', ' ');
    }
}
