<?php
/**
 * includes/showtime-metabox.php
 * Quản lý lịch chiếu phim riêng biệt.
 */

function amurhin_showtime_metaboxes() {
    add_meta_box(
        'amurhin_showtime_details',
        '🗓️ Chi tiết lịch chiếu',
        'amurhin_showtime_details_callback',
        'showtime',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'amurhin_showtime_metaboxes' );

function amurhin_showtime_details_callback( $post ) {
    wp_nonce_field( 'amurhin_save_showtime_details', 'amurhin_showtime_nonce' );

    $movie_id = get_post_meta( $post->ID, '_showtime_movie_id', true );
    $day = get_post_meta( $post->ID, '_showtime_day', true );
    $time = get_post_meta( $post->ID, '_showtime_time', true );

    $movies = get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));

    $days = array(
        'mon' => 'Thứ 2 (Mon)',
        'tue' => 'Thứ 3 (Tue)',
        'wed' => 'Thứ 4 (Wed)',
        'thu' => 'Thứ 5 (Thu)',
        'fri' => 'Thứ 6 (Fri)',
        'sat' => 'Thứ 7 (Sat)',
        'sun' => 'Chủ Nhật (Sun)'
    );

    ?>
    <style>
        .showtime-meta-field { margin-bottom: 15px; }
        .showtime-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
        .showtime-meta-field input, .showtime-meta-field select { width: 100%; padding: 8px; }
    </style>
    <div class="showtime-meta-grid">
        <div class="showtime-meta-field">
            <label>Phim chiếu:</label>
            <select name="showtime_movie_id">
                <option value="">-- Chọn phim --</option>
                <?php foreach ($movies as $movie) : ?>
                    <option value="<?php echo $movie->ID; ?>" <?php selected($movie_id, $movie->ID); ?>>
                        <?php echo esc_html($movie->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="showtime-meta-field">
            <label>Ngày chiếu:</label>
            <select name="showtime_day">
                <?php foreach ($days as $val => $label) : ?>
                    <option value="<?php echo $val; ?>" <?php selected($day, $val); ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="showtime-meta-field">
            <label>Giờ chiếu:</label>
            <input type="time" name="showtime_time" value="<?php echo esc_attr($time); ?>">
        </div>
        <div class="showtime-meta-field">
            <label>Ngày chiếu (YYYY-MM-DD):</label>
            <input type="date" name="showtime_date" value="<?php echo esc_attr(get_post_meta($post->ID, '_showtime_date', true)); ?>">
        </div>
    </div>
    <?php
}

function amurhin_save_showtime_meta( $post_id ) {
    if ( ! isset( $_POST['amurhin_showtime_nonce'] ) || ! wp_verify_nonce( $_POST['amurhin_showtime_nonce'], 'amurhin_save_showtime_details' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    if ( isset( $_POST['showtime_movie_id'] ) ) update_post_meta( $post_id, '_showtime_movie_id', sanitize_text_field($_POST['showtime_movie_id']) );
    if ( isset( $_POST['showtime_day'] ) ) update_post_meta( $post_id, '_showtime_day', sanitize_text_field($_POST['showtime_day']) );
    if ( isset( $_POST['showtime_time'] ) ) update_post_meta( $post_id, '_showtime_time', sanitize_text_field($_POST['showtime_time']) );
    if ( isset( $_POST['showtime_date'] ) ) update_post_meta( $post_id, '_showtime_date', sanitize_text_field($_POST['showtime_date']) );
}
add_action( 'save_post_showtime', 'amurhin_save_showtime_meta' );

// Admin columns
function amurhin_showtime_columns($columns) {
    return array(
        'cb' => $columns['cb'],
        'title' => 'Tên lịch',
        'movie' => 'Chiếu phim',
        'day' => 'Ngày chiếu',
        'time' => 'Giờ chiếu',
        'date' => $columns['date']
    );
}
add_filter('manage_showtime_posts_columns', 'amurhin_showtime_columns');

function amurhin_showtime_column_content($column, $post_id) {
    switch ($column) {
        case 'movie':
            $m_id = get_post_meta($post_id, '_showtime_movie_id', true);
            echo $m_id ? '<a href="'.get_edit_post_link($m_id).'">'.get_the_title($m_id).'</a>' : '---';
            break;
        case 'day':
            $days = array('mon'=>'Thứ 2', 'tue'=>'Thứ 3', 'wed'=>'Thứ 4', 'thu'=>'Thứ 5', 'fri'=>'Thứ 6', 'sat'=>'Thứ 7', 'sun'=>'Chủ Nhật');
            $val = get_post_meta($post_id, '_showtime_day', true);
            echo isset($days[$val]) ? $days[$val] : $val;
            break;
        case 'time':
            echo esc_html(get_post_meta($post_id, '_showtime_time', true));
            break;
    }
}
add_action('manage_showtime_posts_custom_column', 'amurhin_showtime_column_content', 10, 2);
