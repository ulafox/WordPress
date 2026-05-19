<article id="post-<?php the_ID(); ?>" <?php post_class('article'); ?>>
    <h2 class="article-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <div class="article-meta"><?php the_date(); ?> — <?php the_author(); ?></div>
    <div class="article-excerpt"><?php the_excerpt(); ?></div>
</article>
