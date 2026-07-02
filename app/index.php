<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/home-data.php';

$site = mineacle_config()['site'] ?? [];
$home = mineacle_home_data();

function mineacle_icon(string $name): string
{
    $icons = [
        'logo' => '<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M4 9 16 3l12 6v14l-12 6-12-6V9Zm12 5 7-3.5L16 7l-7 3.5L16 14Zm-8 7 6 3v-7l-6-3v7Zm10 3 6-3v-7l-6 3v7Z"/></svg>',
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11 12 3l9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z"/></svg>',
        'stats' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20V9h4v11H5Zm5 0V4h4v16h-4Zm5 0v-7h4v7h-4Z"/></svg>',
        'store' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h12l-1.2 12H7.2L6 8Zm2-4h8l2 4H6l2-4Zm2 7v5h2v-5h-2Zm4 0v5h2v-5h-2Z"/></svg>',
        'vote' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-5-5 2.2-2.2L9 13.6l8.8-8.8L20 7 9 18Z"/></svg>',
        'staff' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 6.5-6.5-2-2L2 18l2 2ZM14.2 6.1l3.7 3.7 1.4-1.4-3.7-3.7a3 3 0 0 0-4.2 0l-1.8 1.8 2.1 2.1 2.5-2.5ZM12 10.2 9.8 8 8.2 9.6l6.2 6.2L16 14l-2.2-2.2 4.5-4.5-1.6-1.6L12 10.2Z"/></svg>',
        'discord' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.4 7.7c2.4-.7 4.8-.7 7.2 0 .4.7.8 1.6 1 2.6.4 1.6.5 3 .3 4.5-1.3 1-2.5 1.5-3.8 1.8l-.7-1.2c.8-.2 1.5-.6 2.1-1.1-.2.1-.5.2-.7.3-1.2.5-2.4.7-3.8.7s-2.6-.2-3.8-.7c-.2-.1-.5-.2-.7-.3.6.5 1.3.9 2.1 1.1l-.7 1.2c-1.3-.3-2.5-.8-3.8-1.8-.2-1.5-.1-2.9.3-4.5.2-1 .6-1.9 1-2.6ZM9 12.8c.7 0 1.2-.6 1.2-1.3S9.7 10.2 9 10.2s-1.2.6-1.2 1.3.5 1.3 1.2 1.3Zm6 0c.7 0 1.2-.6 1.2-1.3S15.7 10.2 15 10.2s-1.2.6-1.2 1.3.5 1.3 1.2 1.3Z"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4.2l3.2 4.4L16.2 4H20l-5.8 6.7L21 20h-4.2l-4-5.5L8 20H4.2l6.8-7.8L5 4Zm2.5 1.8 10.2 12.4h1L8.5 5.8h-1Z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8.5c.3 2.3.3 4.7 0 7-.2 1.4-1.2 2.4-2.6 2.6-4.2.4-8.6.4-12.8 0-1.4-.2-2.4-1.2-2.6-2.6-.3-2.3-.3-4.7 0-7 .2-1.4 1.2-2.4 2.6-2.6 4.2-.4 8.6-.4 12.8 0 1.4.2 2.4 1.2 2.6 2.6ZM10 15l5-3-5-3v6Z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h3c.2 2 1.4 3.4 3.4 3.8v3.1c-1.3-.1-2.4-.5-3.4-1.2V15a5 5 0 1 1-5-5h.6v3.2c-.2 0-.4-.1-.6-.1a1.9 1.9 0 1 0 1.9 1.9L14 3Z"/></svg>',
    ];

    return $icons[$name] ?? $icons['logo'];
}

$navLinks = [
    ['key' => 'home', 'url' => (string) ($site['home_url'] ?? '/')],
    ['key' => 'stats', 'url' => (string) ($site['stats_url'] ?? '#')],
    ['key' => 'store', 'url' => (string) ($site['store_url'] ?? '#')],
    ['key' => 'vote', 'url' => (string) ($site['vote_url'] ?? '#')],
    ['key' => 'staff', 'url' => (string) ($site['staff_url'] ?? '#')],
];

$tiles = array_slice($home['tiles'], 0, 4);
$worlds = array_slice(array_values($home['worlds']), 0, 3);
$socialLinks = array_slice($home['social_links'], 0, 4);

mineacle_page_head('Home');
?>
<div class="site-shell">
    <aside class="rail" aria-label="Primary navigation">
        <a class="rail-logo" href="<?php echo h(mineacle_home_link($site['home_url'] ?? '/')); ?>" aria-label="Home">
            <?php echo mineacle_icon('logo'); ?>
        </a>

        <nav class="rail-nav" aria-label="Server links">
            <?php foreach ($navLinks as $link): ?>
                <a class="rail-link" href="<?php echo h(mineacle_home_link($link['url'])); ?>" aria-label="<?php echo h($link['key']); ?>">
                    <?php echo mineacle_icon((string) $link['key']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="rail-social" aria-label="Social links">
            <a class="rail-link" href="<?php echo h(mineacle_home_link($site['discord_url'] ?? '#')); ?>" aria-label="Discord">
                <?php echo mineacle_icon('discord'); ?>
            </a>
            <a class="rail-link" href="<?php echo h(mineacle_home_link($site['x_url'] ?? '#')); ?>" aria-label="X">
                <?php echo mineacle_icon('x'); ?>
            </a>
        </div>
    </aside>

    <main class="home-grid" aria-label="Home layout">
        <section class="top-row">
            <a class="panel hero-panel" href="<?php echo h(mineacle_home_link($home['hero']['link_url'] ?? '#')); ?>"<?php echo mineacle_home_image_style($home['hero']['background_image_url'] ?? ''); ?> aria-label="Hero">
                <span class="panel-media"<?php echo mineacle_home_image_style($home['hero']['image_url'] ?? '', '--media-image'); ?>></span>
                <span class="sr-only">Hero banner</span>
            </a>

            <aside class="panel player-panel" aria-label="Player summary">
                <span class="skin-frame"<?php echo mineacle_home_image_style($home['player']['skin_url'] ?? '', '--skin-image'); ?>></span>
                <span class="stat-bars"<?php echo mineacle_home_ratio_style($home['player']['players_online'] ?? 0, $home['player']['max_players'] ?? 0); ?>>
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </aside>
        </section>

        <section class="tile-row" aria-label="Feature links">
            <?php foreach ($tiles as $index => $tile): ?>
                <a class="panel feature-tile feature-tile-<?php echo h((string) ($index + 1)); ?>" href="<?php echo h(mineacle_home_link($tile['link_url'] ?? '#')); ?>"<?php echo mineacle_home_image_style($tile['image_url'] ?? ''); ?> aria-label="<?php echo h((string) ($tile['tile_key'] ?? 'Feature')); ?>">
                    <span></span>
                </a>
            <?php endforeach; ?>
        </section>

        <section class="world-row" aria-label="World status">
            <?php foreach ($worlds as $world): ?>
                <article class="panel world-card"<?php echo mineacle_home_panel_style($world['image_url'] ?? '', $world['players_online'] ?? 0, $world['max_players'] ?? 0); ?> aria-label="<?php echo h((string) ($world['world_key'] ?? 'world')); ?>">
                    <span></span>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="community-panel"<?php echo mineacle_home_image_style($home['community']['background_image_url'] ?? ''); ?> aria-label="Community">
            <div class="community-art">
                <span class="panel-media"<?php echo mineacle_home_image_style($home['community']['image_url'] ?? '', '--media-image'); ?>></span>
            </div>

            <div class="social-stack" aria-label="Social destinations">
                <?php foreach ($socialLinks as $link): ?>
                    <a class="social-row" href="<?php echo h(mineacle_home_link($link['url'] ?? '#')); ?>" aria-label="<?php echo h((string) ($link['platform_key'] ?? 'Social')); ?>">
                        <?php echo mineacle_icon((string) ($link['platform_key'] ?? 'logo')); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <footer class="footer-panel"<?php echo mineacle_home_image_style($home['footer']['background_image_url'] ?? ''); ?> aria-label="Footer">
            <span class="footer-mark"<?php echo mineacle_home_image_style($home['footer']['image_url'] ?? '', '--media-image'); ?>></span>
        </footer>
    </main>
</div>
<?php mineacle_page_end(); ?>
