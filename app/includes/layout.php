<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function mineacle_page_head(string $title, string $page = 'bans'): void {
    mineacle_security_headers(false);

    $assetVersion = $page === 'home' ? 'home1.0.0' : 'fold1.0.0';
    $stylesheet = $page === 'home' ? 'home-page.css' : 'bans-page.css';
    $description = $page === 'home'
        ? 'Mineacle Network home hub'
        : 'Mineacle active bans search';

    echo '<!doctype html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Mineacle | ' . h($title) . '</title>';
    echo '<meta name="description" content="' . h($description) . '">';
    echo '<link rel="icon" type="image/png" href="assets/fav.png?v=' . h($assetVersion) . '">';
    mineacle_critical_styles();
    echo '<link rel="stylesheet" href="assets/' . h($stylesheet) . '?v=' . h($assetVersion) . '">';
    echo '</head>';
    echo '<body>';
}

function mineacle_critical_styles(): void {
    echo <<<'HTML'
<style>
:root{--page:#181b20;--text:#f6f4f8}*{box-sizing:border-box}html{min-height:100%;background:#181b20}body{min-height:100vh;margin:0;background:#181b20;color:var(--text);font-family:Inter,"Open Sans",system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}a{color:inherit}.sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
</style>
HTML;
}

function mineacle_header(string $active = 'bans'): void {
    unset($active);
}

function mineacle_page_end(string $page = 'bans'): void {
    $assetVersion = $page === 'home' ? 'home1.0.0' : 'fold1.0.0';
    $script = $page === 'home' ? 'home-page.js' : 'bans-page.js';

    echo '<script src="assets/' . h($script) . '?v=' . h($assetVersion) . '"></script>';
    echo '</body></html>';
}

function mineacle_footer(): void {
    mineacle_page_end();
}
