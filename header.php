<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ( $favicon = amurhin_get_option('favicon') ) : ?>
        <link rel="icon" type="image/x-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header>
    <div class="container">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
            <?php if ( $logo = amurhin_get_option('logo') ) : ?>
                <img src="<?php echo esc_url($logo); ?>" alt="<?php bloginfo('name'); ?>">
            <?php else : ?>
                <?php bloginfo('name'); ?>
            <?php endif; ?>
        </a>
        <nav class="main-nav">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'nav-list',
                'fallback_cb'    => false,
            ) );
            ?>
        </nav>
        <div class="header-actions">
            <div class="watchlist-container">
                <button id="watchlist-toggle" class="btn-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    <span class="watchlist-count">0</span>
                </button>
                <div class="watchlist-dropdown" style="display:none;">
                    <div class="watchlist-header">
                        <span>Phim đã theo dõi</span>
                    </div>
                    <ul id="watchlist-items">
                        <!-- Items rendered via JS -->
                        <li class="empty-message">Chưa có phim nào!</li>
                    </ul>
                    <div class="watchlist-footer" style="padding:10px; border-top:1px solid rgba(255,255,255,0.05); text-align:center;">
                        <button id="clear-watchlist" style="background:transparent; border:none; color:#e74c3c; cursor:pointer; font-size:12px; font-weight:600;">Xóa tất cả</button>
                    </div>
                </div>
            </div>
        </div>    </div>
</header>
