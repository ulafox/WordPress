<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('article'); ?>>
            <h2 class="article-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <div class="article-meta"><?php the_date(); ?> — <?php the_author(); ?></div>
            <div class="article-excerpt"><?php the_excerpt(); ?></div>
        </article>
    <?php endwhile; ?>
    <div class="pagination"><?php posts_nav_link(); ?></div>
<?php else : ?>
    <p><?php esc_html_e( 'No posts found.', 'slate-for-wp' ); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
