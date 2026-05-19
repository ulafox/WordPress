<?php
// functions.php — basic theme setup and asset enqueue

if ( ! function_exists( 'slate_setup' ) ) {
    function slate_setup() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption' ) );
        register_nav_menus( array(
            'primary' => __('Primary Menu', 'slate-for-wp'),
        ) );
    }
}
add_action( 'after_setup_theme', 'slate_setup' );

function slate_enqueue_assets() {
    wp_enqueue_style( 'slate-style', get_stylesheet_uri(), array(), filemtime( get_stylesheet_directory() . '/style.css' ) );
    wp_enqueue_script( 'slate-script', get_template_directory_uri() . '/assets/js/script.js', array('jquery'), null, true );
}
add_action( 'wp_enqueue_scripts', 'slate_enqueue_assets' );

// Basic widgets support
function slate_widgets_init() {
    register_sidebar( array(
        'name' => 'Sidebar',
        'id' => 'sidebar-1',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );
}
add_action( 'widgets_init', 'slate_widgets_init' );
