<?php
/**
 * AmurHin functions and definitions
 */

if ( ! function_exists( 'amurhin_setup' ) ) {
    function amurhin_setup() {
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'title-tag' );
        
        register_nav_menus( array(
            'primary' => __( 'Primary Menu', 'amurhin' ),
        ) );

        // Register Movie Sidebar
        register_sidebar( array(
            'name'          => __( 'Movie Sidebar', 'amurhin' ),
            'id'            => 'movie-sidebar',
            'description'   => __( 'Sidebar hiển thị trên trang chủ và trang phim.', 'amurhin' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }
}
add_action( 'after_setup_theme', 'amurhin_setup' );

// Enqueue styles and scripts
function amurhin_scripts() {
    wp_enqueue_style( 'amurhin-style', get_stylesheet_uri() );
    
    // Core jQuery
    wp_enqueue_script( 'jquery' );

    // Hls.js for M3U8 playback (Load in header)
    wp_enqueue_script( 'hls-js', 'https://cdn.jsdelivr.net/npm/hls.js@latest', array('jquery'), null, false );

    // Theme Player Logic
    wp_enqueue_script( 'amurhin-player', get_template_directory_uri() . '/js/player.js', array('jquery', 'hls-js'), '1.1.0', true );
    wp_localize_script( 'amurhin-player', 'themePlayer', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    // Countdown Logic
    wp_enqueue_script( 'amurhin-countdown', get_template_directory_uri() . '/js/countdown.js', array('jquery'), '1.1.0', true );
    
    // Rating Logic
    wp_enqueue_script( 'amurhin-rating', get_template_directory_uri() . '/js/rating.js', array('jquery'), '1.0.0', true );
    
    // Watchlist Logic
    wp_enqueue_script( 'amurhin-watchlist', get_template_directory_uri() . '/js/watchlist.js', array('jquery'), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'amurhin_scripts' );

// Register Movie Custom Post Type
function amurhin_register_movie_cpt() {
    $labels = array(
        'name'               => _x( 'Phim', 'post type general name', 'amurhin' ),
        'singular_name'      => _x( 'Phim', 'post type singular name', 'amurhin' ),
        'menu_name'          => _x( 'Phim', 'admin menu', 'amurhin' ),
        'add_new'            => _x( 'Thêm phim mới', 'movie', 'amurhin' ),
        'add_new_item'       => __( 'Thêm Phim Mới', 'amurhin' ),
        'edit_item'          => __( 'Chỉnh sửa Phim', 'amurhin' ),
        'new_item'           => __( 'Phim Mới', 'amurhin' ),
        'all_items'          => __( 'Tất cả Phim', 'amurhin' ),
        'view_item'          => __( 'Xem Phim', 'amurhin' ),
        'search_items'       => __( 'Tìm kiếm Phim', 'amurhin' ),
        'not_found'          => __( 'Không tìm thấy phim nào', 'amurhin' ),
        'not_found_in_trash' => __( 'Không có phim nào trong thùng rác', 'amurhin' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'phim' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-video-alt2',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'movie', $args );

    // Register Episode Custom Post Type
    $episode_labels = array(
        'name'               => _x( 'Tập phim', 'post type general name', 'amurhin' ),
        'singular_name'      => _x( 'Tập phim', 'post type singular name', 'amurhin' ),
        'menu_name'          => _x( 'Tập phim', 'admin menu', 'amurhin' ),
        'add_new'            => _x( 'Thêm tập mới', 'episode', 'amurhin' ),
        'add_new_item'       => __( 'Thêm Tập Phim Mới', 'amurhin' ),
        'edit_item'          => __( 'Chỉnh sửa Tập Phim', 'amurhin' ),
        'all_items'          => __( 'Tất cả Tập Phim', 'amurhin' ),
    );

    $episode_args = array(
        'labels'             => $episode_labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=movie', // Put under Movie menu
        'capability_type'    => 'post',
        'has_archive'        => false,
        'rewrite'            => array( 'slug' => 'tap-phim' ),
        'supports'           => array( 'title' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'episode', $episode_args );

    // Register Showtime Custom Post Type
    $showtime_labels = array(
        'name'               => _x( 'Lịch chiếu', 'post type general name', 'amurhin' ),
        'singular_name'      => _x( 'Lịch chiếu', 'post type singular name', 'amurhin' ),
        'menu_name'          => _x( 'Lịch chiếu', 'admin menu', 'amurhin' ),
        'add_new'            => _x( 'Thêm lịch mới', 'showtime', 'amurhin' ),
        'add_new_item'       => __( 'Thêm Lịch Chiếu Mới', 'amurhin' ),
        'edit_item'          => __( 'Chỉnh sửa Lịch Chiếu', 'amurhin' ),
        'all_items'          => __( 'Tất cả Lịch Chiếu', 'amurhin' ),
    );

    $showtime_args = array(
        'labels'             => $showtime_labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=movie', // Put under Movie menu
        'capability_type'    => 'post',
        'has_archive'        => false,
        'rewrite'            => array( 'slug' => 'lich-chieu' ),
        'supports'           => array( 'title' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'showtime', $showtime_args );
}
add_action( 'init', 'amurhin_register_movie_cpt' );

// Include modular logic
require_once get_template_directory() . '/includes/movie-metabox.php';
require_once get_template_directory() . '/includes/episode-metabox.php';
require_once get_template_directory() . '/includes/showtime-metabox.php';
require_once get_template_directory() . '/includes/theme-options.php';
require_once get_template_directory() . '/includes/ajax-player.php';
// Crawler core moved to plugin, ensure plugin is active.

// Register Taxonomies
function amurhin_register_taxonomies() {
    // Movie Type (Phim bộ, Phim lẻ, ...)
    register_taxonomy( 'movie_type', 'movie', array(
        'label'        => __( 'Loại phim', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'loai-phim' ),
        'hierarchical' => true,
        'show_in_rest' => true,
    ) );

    // Genres (Thể loại)
    register_taxonomy( 'genre', 'movie', array(
        'label'        => __( 'Thể loại', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'the-loai' ),
        'hierarchical' => true,
        'show_in_rest' => true,
    ) );

    // Tags (Thẻ từ khóa)
    register_taxonomy( 'movie_tag', 'movie', array(
        'label'        => __( 'Từ khóa', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'tu-khoa' ),
        'hierarchical' => false,
        'show_in_rest' => true,
    ) );

    // Years (Năm phát hành)
    register_taxonomy( 'release_year', 'movie', array(
        'label'        => __( 'Năm phát hành', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'nam-phat-hanh' ),
        'hierarchical' => false,
        'show_in_rest' => true,
    ) );

    // Countries
    register_taxonomy( 'country', 'movie', array(
        'label'        => __( 'Quốc gia', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'quoc-gia' ),
        'hierarchical' => false,
        'show_in_rest' => true,
    ) );

    // Actors
    register_taxonomy( 'actor', 'movie', array(
        'label'        => __( 'Diễn viên', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'dien-vien' ),
        'hierarchical' => false,
        'show_in_rest' => true,
    ) );

    // Directors
    register_taxonomy( 'director', 'movie', array(
        'label'        => __( 'Đạo diễn', 'amurhin' ),
        'rewrite'      => array( 'slug' => 'dao-dien' ),
        'hierarchical' => false,
        'show_in_rest' => true,
    ) );
}
add_action( 'init', 'amurhin_register_taxonomies' );

// Add default terms for Movie Type
function amurhin_add_default_movie_types() {
    $types = array(
        'Phim bộ',
        'Phim lẻ',
        'Hoạt hình',
        'Phim chiếu rạp',
        'TV show'
    );
    foreach ( $types as $type ) {
        if ( ! term_exists( $type, 'movie_type' ) ) {
            wp_insert_term( $type, 'movie_type' );
        }
    }
}
add_action( 'admin_init', 'amurhin_add_default_movie_types' );

// Helper function to get movies by showtime day
function amurhin_get_movies_by_showtime_day($day, $count = 8) {
    $showtime_posts = get_posts(array(
        'post_type' => 'showtime',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_showtime_day',
                'value' => $day,
                'compare' => '='
            )
        )
    ));

    $movie_ids = array();
    foreach ($showtime_posts as $sp) {
        if ($m_id = get_post_meta($sp->ID, '_showtime_movie_id', true)) {
            $movie_ids[] = $m_id;
        }
    }

    if (empty($movie_ids)) {
        return new WP_Query(array('post_type' => 'movie', 'post__in' => array(0)));
    }

    return new WP_Query(array(
        'post_type' => 'movie',
        'post__in' => $movie_ids,
        'posts_per_page' => $count,
        'orderby' => 'post__in'
    ));
}

// Helper function to get the next showtime timestamp
function amurhin_get_next_showtime_timestamp($post_id) {
    // Find the showtime post for this movie
    $showtimes = get_posts(array(
        'post_type' => 'showtime',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_showtime_movie_id',
                'value' => $post_id
            )
        )
    ));

    if (empty($showtimes)) return 0;

    $st_post = $showtimes[0];
    $hour = get_post_meta($st_post->ID, '_showtime_time', true) ?: '00:00';
    $date = get_post_meta($st_post->ID, '_showtime_date', true);
    if (!$date) return 0;
    // Combine date and time into a datetime string
    $datetime = $date . ' ' . $hour;
    $target_time = strtotime($datetime);
    return $target_time;
}

function amurhin_get_movies_by_type($type_slug, $count = 10) {
    return new WP_Query(array(
        'post_type' => 'movie',
        'posts_per_page' => $count,
        'tax_query' => array(
            array(
                'taxonomy' => 'movie_type',
                'field'    => 'slug',
                'terms'    => $type_slug,
            ),
        ),
    ));
}

// Helper function to get movies by country
function amurhin_get_movies_by_country($country_slug, $count = 10) {
    return new WP_Query(array(
        'post_type' => 'movie',
        'posts_per_page' => $count,
        'tax_query' => array(
            array(
                'taxonomy' => 'country',
                'field'    => 'slug',
                'terms'    => $country_slug,
            ),
        ),
    ));
}

// Helper function to get recommended movies


/**
 * Advanced Rating & Views Logic
 */

// Get Total Views (Manual + Real)
function amurhin_get_views($post_id) {
    $manual_views = (int) get_post_meta($post_id, '_movie_views', true);
    $real_views = (int) get_post_meta($post_id, '_movie_views_real', true);
    return $manual_views + $real_views;
}

// Get Weighted Rating
function amurhin_get_rating($post_id) {
    $manual_score = (float) get_post_meta($post_id, '_movie_rating', true) ?: 5; // Default 5
    $manual_count = (int) get_post_meta($post_id, '_movie_rating_count', true) ?: 1; // Default 1 (to avoid div by zero)
    
    $real_score_sum = (int) get_post_meta($post_id, '_movie_rating_real_sum', true);
    $real_vote_count = (int) get_post_meta($post_id, '_movie_rating_real_count', true);
    
    $total_score = ($manual_score * $manual_count) + $real_score_sum;
    $total_count = $manual_count + $real_vote_count;
    
    if ($total_count == 0) return 5;
    
    return round($total_score / $total_count, 2);
}

// Get Total Vote Count
function amurhin_get_vote_count($post_id) {
    $manual_count = (int) get_post_meta($post_id, '_movie_rating_count', true) ?: 1;
    $real_vote_count = (int) get_post_meta($post_id, '_movie_rating_real_count', true);
    return $manual_count + $real_vote_count;
}

// AJAX: Increment Real View
function amurhin_increment_view_ajax() {
    $post_id = intval($_POST['post_id']);
    if ($post_id > 0) {
        $current_real = (int) get_post_meta($post_id, '_movie_views_real', true);
        update_post_meta($post_id, '_movie_views_real', $current_real + 1);
        echo json_encode(['success' => true, 'views' => amurhin_format_views(amurhin_get_views($post_id))]);
    }
    wp_die();
}
add_action('wp_ajax_amurhin_inc_view', 'amurhin_increment_view_ajax');
add_action('wp_ajax_nopriv_amurhin_inc_view', 'amurhin_increment_view_ajax');

// AJAX: Process Emoji Rating
function amurhin_process_rating_ajax() {
    $post_id = intval($_POST['post_id']);
    $score = intval($_POST['score']); // 1 to 5
    
    if ($post_id > 0 && $score >= 1 && $score <= 5) {
        $current_sum = (int) get_post_meta($post_id, '_movie_rating_real_sum', true);
        $current_count = (int) get_post_meta($post_id, '_movie_rating_real_count', true);
        
        update_post_meta($post_id, '_movie_rating_real_sum', $current_sum + $score);
        update_post_meta($post_id, '_movie_rating_real_count', $current_count + 1);
        
        echo json_encode([
            'success' => true,
            'rating' => amurhin_get_rating($post_id),
            'count' => amurhin_get_vote_count($post_id)
        ]);
    }
    wp_die();
}
add_action('wp_ajax_amurhin_rate_movie', 'amurhin_process_rating_ajax');
add_action('wp_ajax_nopriv_amurhin_rate_movie', 'amurhin_process_rating_ajax');

function amurhin_format_views($views) {
    if ($views >= 1000000) return round($views / 1000000, 1) . 'M';
    if ($views >= 1000) return round($views / 1000, 1) . 'K';
    return $views;
}

// Helper function to get recommended movies
function amurhin_get_recommended_movies($count = 5) {
    return new WP_Query(array(
        'post_type' => 'movie',
        'posts_per_page' => $count,
        'meta_query' => array(
            array(
                'key' => '_movie_recommended',
                'value' => '1',
                'compare' => '='
            )
        ),
        'orderby' => 'meta_value_num',
        'meta_key' => '_movie_views',
        'order' => 'DESC'
    ));
}

/**
 * Get Movie Thumbnail URL with fallbacks
 */
function amurhin_get_movie_thumb_url($post_id) {
    $thumb_url = get_post_meta($post_id, '_movie_thumb', true);
    if (!$thumb_url) {
        $thumb_url = get_post_meta($post_id, '_movie_poster', true);
    }
    if (!$thumb_url) {
        $thumb_url = get_the_post_thumbnail_url($post_id, 'medium');
    }
    if (!$thumb_url) {
        $thumb_url = "https://via.placeholder.com/200x300?text=No+Poster";
    }
    return $thumb_url;
}

/**
 * Get Movie Banner URL with fallbacks
 */
function amurhin_get_movie_banner_url($post_id) {
    $banner_url = get_post_meta($post_id, '_movie_banner', true);
    if (!$banner_url) {
        $banner_url = get_post_meta($post_id, '_movie_poster', true);
    }
    if (!$banner_url) {
        $banner_url = get_the_post_thumbnail_url($post_id, 'full');
    }
    if (!$banner_url) {
        $banner_url = "https://via.placeholder.com/1300x500?text=Banner";
    }
    return $banner_url;
}

/**
 * Add custom columns to Movie admin list
 */
function amurhin_movie_columns($columns) {
    $new_columns = array();
    foreach($columns as $key => $title) {
        if ($key == 'title') {
            $new_columns['movie_thumb'] = 'Hình ảnh';
            $new_columns[$key] = $title;
        } else if ($key == 'date') {
            $new_columns['movie_genre'] = 'Thể loại';
            $new_columns['movie_country'] = 'Quốc gia';
            $new_columns['movie_count_ep'] = 'Số tập';
            $new_columns['movie_type'] = 'Loại phim';

            $new_columns[$key] = $title;
        } else {
            $new_columns[$key] = $title;
        }
    }
    return $new_columns;
}
add_filter('manage_movie_posts_columns', 'amurhin_movie_columns');

/**
 * Populate custom Movie columns
 */
function amurhin_movie_custom_column($column, $post_id) {
    switch ($column) {
        case 'movie_thumb':
            $thumb = amurhin_get_movie_thumb_url($post_id);
            echo '<img src="' . esc_url($thumb) . '" style="width: 60px; height: 80px; object-fit: cover; border-radius: 4px;">';
            break;
        case 'movie_genre':
            echo get_the_term_list($post_id, 'genre', '', ', ');
            break;
        case 'movie_country':
            echo get_the_term_list($post_id, 'country', '', ', ');
            break;
        case 'movie_type':
            echo get_the_term_list($post_id, 'movie_type', '', ', ');
            break;    
        case 'movie_count_ep':
            $ep_query = new WP_Query(array(
                'post_type' => 'episode',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array('key' => '_episode_movie_id', 'value' => $post_id)
                ),
                'fields' => 'ids'
            ));
            echo $ep_query->found_posts;
            break;
    }
}
add_action('manage_movie_posts_custom_column', 'amurhin_movie_custom_column', 10, 2);
/**
 * Delete associated episodes and showtimes when a movie is deleted
 */
function amurhin_delete_associated_data($post_id) {
    $post = get_post($post_id);

    if ($post->post_type !== 'movie') {
        return;
    }

    // Delete Episodes
    $episodes = get_posts(array(
        'post_type'      => 'episode',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => '_episode_movie_id',
                'value' => $post_id,
            ),
        ),
        'fields'         => 'ids',
    ));

    if (!empty($episodes)) {
        foreach ($episodes as $ep_id) {
            wp_delete_post($ep_id, true); // Set true to bypass trash and force delete episodes
        }
    }

    // Delete Showtimes
    $showtimes = get_posts(array(
        'post_type'      => 'showtime',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => '_showtime_movie_id',
                'value' => $post_id,
            ),
        ),
        'fields'         => 'ids',
    ));

    if (!empty($showtimes)) {
        foreach ($showtimes as $st_id) {
            wp_delete_post($st_id, true);
        }
    }
}
add_action('before_delete_post', 'amurhin_delete_associated_data');

/**
 * Handle Episode Rewrite Rules
 */
function amurhin_episode_rewrites() {
    add_rewrite_rule(
        'phim/([^/]+)/sv([0-9]+)-tap([0-9]+)\.html/?$',
        'index.php?movie=$matches[1]&sv=$matches[2]&tap=$matches[3]',
        'top'
    );
}
add_action('init', 'amurhin_episode_rewrites');

function amurhin_query_vars($vars) {
    $vars[] = 'sv';
    $vars[] = 'tap';
    return $vars;
}
add_filter('query_vars', 'amurhin_query_vars');

// Flush rewrite rules on theme switch
function amurhin_flush_rewrites() {
    amurhin_episode_rewrites();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'amurhin_flush_rewrites');
