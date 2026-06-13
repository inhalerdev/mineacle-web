<?php
declare(strict_types=1);

/*
 * Root route.
 *
 * This renders bans.php directly at:
 * https://bans.mineacle.net/
 *
 * Do not redirect to bans.php, because that exposes the filename in the URL.
 */
define('MINEACLE_INTERNAL_RENDER', true);
require __DIR__ . '/bans.php';
