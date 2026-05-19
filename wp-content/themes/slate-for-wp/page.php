<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1 class="article-title"><?php the_title(); ?></h1>
        <div class="article-content"><?php the_content(); ?></div>
    </article>
<?php endwhile; endif; ?>

<?php get_footer(); ?>
