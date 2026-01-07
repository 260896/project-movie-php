<?php
/**
 * includes/episode-metabox.php
 * Quản lý thông tin chi tiết cho từng tập phim.
 */

function amurhin_episode_metaboxes() {
    add_meta_box(
        'amurhin_episode_details',
        '🔗 Chi tiết tập phim',
        'amurhin_episode_details_callback',
        'episode',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'amurhin_episode_metaboxes' );

function amurhin_episode_details_callback( $post ) {
    wp_nonce_field( 'amurhin_save_episode_details', 'amurhin_episode_nonce' );

    $movie_id = get_post_meta( $post->ID, '_episode_movie_id', true );
    $server_name = get_post_meta( $post->ID, '_episode_server', true ) ?: 'Server #1';
    $link = get_post_meta( $post->ID, '_episode_link', true );
    $type = get_post_meta( $post->ID, '_episode_type', true ) ?: 'link';

    // Lấy danh sách phim để chọn
    $movies = get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));

    ?>
    <style>
        .episode-meta-field { margin-bottom: 15px; }
        .episode-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
        .episode-meta-field input[type="text"], .episode-meta-field select { width: 100%; padding: 8px; }
    </style>
    <div class="episode-meta-grid">
        <div class="episode-meta-field">
            <label>Thuộc phim:</label>
            <select name="episode_movie_id">
                <option value="">-- Chọn phim --</option>
                <?php foreach ($movies as $movie) : ?>
                    <option value="<?php echo $movie->ID; ?>" <?php selected($movie_id, $movie->ID); ?>>
                        <?php echo esc_html($movie->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="episode-meta-field">
            <label>Tên Server:</label>
            <input type="text" name="episode_server" value="<?php echo esc_attr($server_name); ?>" placeholder="Ví dụ: Server #1, Vietsub...">
        </div>
        <div class="episode-meta-field">
            <label>Link phim:</label>
            <input type="text" name="episode_link" value="<?php echo esc_attr($link); ?>" placeholder="Link .m3u8 hoặc Iframe embed">
        </div>
        <div class="episode-meta-field">
            <label>Loại link:</label>
            <select name="episode_type">
                <option value="link" <?php selected($type, 'link'); ?>>Link trực tiếp (.m3u8)</option>
                <option value="embed" <?php selected($type, 'embed'); ?>>Embed (Iframe)</option>
            </select>
        </div>
    </div>
    <?php
}

function amurhin_save_episode_meta( $post_id ) {
    if ( ! isset( $_POST['amurhin_episode_nonce'] ) || ! wp_verify_nonce( $_POST['amurhin_episode_nonce'], 'amurhin_save_episode_details' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    if ( isset( $_POST['episode_movie_id'] ) ) update_post_meta( $post_id, '_episode_movie_id', sanitize_text_field($_POST['episode_movie_id']) );
    if ( isset( $_POST['episode_server'] ) ) update_post_meta( $post_id, '_episode_server', sanitize_text_field($_POST['episode_server']) );
    if ( isset( $_POST['episode_link'] ) ) update_post_meta( $post_id, '_episode_link', sanitize_text_field($_POST['episode_link']) );
    if ( isset( $_POST['episode_type'] ) ) update_post_meta( $post_id, '_episode_type', sanitize_text_field($_POST['episode_type']) );
}
add_action( 'save_post_episode', 'amurhin_save_episode_meta' );

// Thêm cột vào danh sách tập phim trong admin
function amurhin_episode_columns($columns) {
    $new_columns = array(
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'movie' => 'Thuộc phim',
        'server' => 'Server',
        'date' => $columns['date']
    );
    return $new_columns;
}
add_filter('manage_episode_posts_columns', 'amurhin_episode_columns');

function amurhin_episode_column_content($column, $post_id) {
    switch ($column) {
        case 'movie':
            $movie_id = get_post_meta($post_id, '_episode_movie_id', true);
            if ($movie_id) {
                echo '<a href="'.get_edit_post_link($movie_id).'">'.get_the_title($movie_id).'</a>';
            } else {
                echo '<span style="color:red;">Chưa chọn phim</span>';
            }
            break;
        case 'server':
            echo esc_html(get_post_meta($post_id, '_episode_server', true));
            break;
    }
}
add_action('manage_episode_posts_custom_column', 'amurhin_episode_column_content', 10, 2);
