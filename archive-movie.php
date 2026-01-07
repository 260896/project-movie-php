<?php get_header(); ?>

<div class="container" style="display: grid; grid-template-columns: 1fr 300px; gap: 40px; margin-top: 30px;">
    <main id="main-content">
        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="font-size: 24px; border-left: 4px solid var(--primary); padding-left: 15px; margin: 0; color: #fff;">
                <?php 
                if ( is_tax() ) {
                    single_term_title();
                } else {
                    echo 'Tất cả Phim';
                }
                ?>
            </h2>
        </div>

        <div class="movie-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    ?>
                    <article class="movie-item">
                        <div class="movie-poster">
                            <div class="movie-label"><?php echo get_post_meta(get_the_ID(), '_movie_quality', true) ?: 'HD'; ?></div>
                             <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo amurhin_get_movie_thumb_url(get_the_ID()); ?>" alt="<?php the_title(); ?>">
                            </a>
                        </div>
                        <div class="movie-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                                <?php echo number_format(get_post_meta(get_the_ID(), '_movie_views', true) ?: 0); ?> lượt xem
                            </div>
                        </div>
                    </article>
                    <?php
                endwhile;
                
                // Pagination
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '&laquo; Trước',
                    'next_text' => 'Tiếp &raquo;',
                ) );

            else :
                echo '<p style="text-align:center; padding: 50px;">Chưa có phim nào trong mục này.</p>';
            endif;
            ?>
        </div>
    </main>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
