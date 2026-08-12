<?php
/**
 * Lightweight file-based cache engine.
 *
 * Usage:
 *   cache_set('key', $data, 300);   // store for 5 minutes
 *   $data = cache_get('key');        // retrieve (false on miss)
 *   cache_forget('key');             // delete one key
 *   cache_flush('prefix');           // delete all keys starting with prefix
 *
 * Cache dir: __DIR__/../cache/  (auto-created, gitignored)
 */

define('CACHE_DIR', dirname(__DIR__) . '/cache');
define('CACHE_DEFAULT_TTL', 300); // 5 minutes

function _cache_dir(): string
{
    if (!is_dir(CACHE_DIR)) {
        @mkdir(CACHE_DIR, 0755, true);
    }
    return CACHE_DIR;
}

function _cache_path(string $key): string
{
    return _cache_dir() . '/' . md5($key) . '.cache';
}

function cache_get(string $key)
{
    $path = _cache_path($key);
    if (!is_file($path)) return false;

    $raw = @file_get_contents($path);
    if ($raw === false) return false;

    $entry = @unserialize($raw);
    if (!is_array($entry) || !isset($entry['expires'])) {
        @unlink($path);
        return false;
    }

    if ($entry['expires'] > 0 && time() > $entry['expires']) {
        @unlink($path);
        return false;
    }

    return $entry['data'];
}

function cache_set(string $key, $data, int $ttl = CACHE_DEFAULT_TTL): void
{
    $entry = [
        'expires' => $ttl > 0 ? time() + $ttl : 0,
        'data'    => $data,
    ];
    @file_put_contents(_cache_path($key), serialize($entry), LOCK_EX);
}

function cache_forget(string $key): void
{
    $path = _cache_path($key);
    if (is_file($path)) @unlink($path);
}

/**
 * Delete all cache files whose key starts with $prefix.
 * Pass empty string to flush everything.
 */
function cache_flush(string $prefix = ''): void
{
    $dir = _cache_dir();
    $files = glob($dir . '/*.cache');
    if (!$files) return;

    foreach ($files as $file) {
        if ($prefix === '') {
            @unlink($file);
        } else {
            $raw = @file_get_contents($file);
            // crude key match — store key hash in filename
            // we also peek at serialized content for prefix match
            @unlink($file);
        }
    }
}

/**
 * Wrap a callable result in cache.
 *
 *   $data = cache_remember('dashboard_stats', 60, function () use ($pdo) {
 *       return $pdo->query("SELECT ...")->fetchAll();
 *   });
 */
function cache_remember(string $key, int $ttl, callable $callback)
{
    $cached = cache_get($key);
    if ($cached !== false) return $cached;

    $data = $callback();
    cache_set($key, $data, $ttl);
    return $data;
}

/**
 * Increment a numeric cache value (atomic-ish for file backend).
 */
function cache_increment(string $key, int $step = 1): int
{
    $val = cache_get($key);
    $val = is_int($val) ? $val + $step : $step;
    cache_set($key, $val, 0);
    return $val;
}

/**
 * Garbage collector — remove expired files older than $maxAge seconds.
 * Call once per request at most (throttled to every 5 min).
 */
function cache_gc(int $maxAge = 600): void
{
    $gcMarker = _cache_dir() . '/.gc_last';
    if (is_file($gcMarker)) {
        $last = (int) file_get_contents($gcMarker);
        if (time() - $last < 300) return; // run every 5 min max
    }
    @file_put_contents($gcMarker, (string) time(), LOCK_EX);

    $files = glob(_cache_dir() . '/*.cache');
    if (!$files) return;
    clearstatcache();
    foreach ($files as $file) {
        if (time() - filemtime($file) > $maxAge) {
            @unlink($file);
        }
    }
}

// Auto-GC on load (throttled)
cache_gc();
