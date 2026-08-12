<?php
/**
 * Cache-busting asset helper.
 *
 * Usage: asset('assets/js/main.js')  →  assets/js/main.js?v=1723478520
 *
 * The version is derived from the file's last-modified timestamp,
 * so updating a file automatically busts its cache — no manual
 * version bumping required.
 */

function asset(string $path): string
{
    $full = dirname(__DIR__) . '/' . ltrim($path, '/');
    $ts   = file_exists($full) ? filemtime($full) : time();
    return $path . '?v=' . $ts;
}
