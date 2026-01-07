<?php get_header(); ?>

<main class="container">
    <?php
    while ( have_posts() ) : the_post();
        // Dispatcher: Kiểm tra mode dựa trên URL parameter và rewrite tags
        $is_watch = (isset($_GET['watch']) && $_GET['watch'] == '1') || get_query_var('sv') || get_query_var('tap');

        if ($is_watch) {
            get_template_part('templates/single-watch');
        } else {
            get_template_part('templates/single-info');
        }
    endwhile;
    ?>
</main>

<?php get_footer(); ?>
