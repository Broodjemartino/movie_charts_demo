<?php

use Timber\Site;

/**
 * Class StarterSite
 */
class StarterSite extends Site
{
    public function __construct()
    {
        add_action('after_setup_theme', array( $this, 'theme_supports' ));
        add_action('init', array( $this, 'register_post_types' ));
        add_action('init', array( $this, 'register_taxonomies' ));

        add_filter('timber/context', array( $this, 'add_to_context' ));
        add_filter('timber/twig', array( $this, 'add_to_twig' ));
        add_filter('timber/twig/environment/options', [ $this, 'update_twig_environment_options' ]);

        parent::__construct();
    }

    /**
     * This is where you can register custom post types.
     */
    public function register_post_types()
    {
        // Movie Type
        $labels = array(
            'name'                  => _x('Timbers', 'Post Type General Name', 'mcd_theme'),
            'singular_name'         => _x('Movie', 'Post Type Singular Name', 'mcd_theme'),
            'menu_name'             => __('Movies', 'mcd_theme'),
            'name_admin_bar'        => __('Movie', 'mcd_theme'),
            'archives'              => __('Movie Archives', 'mcd_theme'),
            'attributes'            => __('Movie Attributes', 'mcd_theme'),
            'parent_item_colon'     => __('Parent Item:', 'mcd_theme'),
            'all_items'             => __('All Movies', 'mcd_theme'),
            'add_new_item'          => __('Add New Movie', 'mcd_theme'),
            'add_new'               => __('Add New Movie', 'mcd_theme'),
            'new_item'              => __('New Movie', 'mcd_theme'),
            'edit_item'             => __('Edit Movie', 'mcd_theme'),
            'update_item'           => __('Update Movie', 'mcd_theme'),
            'view_item'             => __('View Movie', 'mcd_theme'),
            'view_items'            => __('View Movies', 'mcd_theme'),
            'search_items'          => __('Search Movie', 'mcd_theme'),
            'not_found'             => __('Movie Not found', 'mcd_theme'),
            'not_found_in_trash'    => __('Movie Not found in Trash', 'mcd_theme'),
            'featured_image'        => __('Featured Image', 'mcd_theme'),
            'set_featured_image'    => __('Set featured image', 'mcd_theme'),
            'remove_featured_image' => __('Remove featured image', 'mcd_theme'),
            'use_featured_image'    => __('Use as featured image', 'mcd_theme'),
            'insert_into_item'      => __('Insert into Movie', 'mcd_theme'),
            'uploaded_to_this_item' => __('Uploaded to this Movie', 'mcd_theme'),
            'items_list'            => __('Movies list', 'mcd_theme'),
            'items_list_navigation' => __('Movies list navigation', 'mcd_theme'),
            'filter_items_list'     => __('Filter Movies list', 'mcd_theme'),
        );

        $args = array(
            'label'                 => __('Movie', 'mcd_theme'),
            'description'           => __('Top 50 Movie', 'mcd_theme'),
            'labels'                => $labels,
            'supports'              => array( 'title', 'excerpt'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-video-alt3',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'has_archive'           => 'movies',
            'rewrite'               => array(
                                            'slug' => 'movies',
                                            'with_front' => false
                                       ),
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );

        register_post_type('movie_type', $args);
    }

    /**
     * This is where you can register custom taxonomies.
     */
    public function register_taxonomies()
    {

         // Genre
         $args = array(
                'hierarchical'          => true,
                'labels'                => array(
                    'name'              => __('Genres', 'mcd_theme'),
                    'singular_name'     => __('Genre', 'mcd_theme'),
                    'search_items'      => __('Search Genre', 'mcd_theme'),
                    'all_items'         => __('All Genres', 'mcd_theme'),
                    'parent_item'       => __('Parent Genre', 'mcd_theme'),
                    'parent_item_colon' => __('Parent Genre:', 'mcd_theme'),
                    'edit_item'         => __('Edit Genre', 'mcd_theme'),
                    'update_item'       => __('Update Genre', 'mcd_theme'),
                    'add_new_item'      => __('Add New Genre', 'mcd_theme'),
                    'new_item_name'     => __('New Genre Name', 'mcd_theme')
                ),
                'show_admin_column'     => true,
                'show_ui'               => true,
                'query_var'             => true,
                'show_in_rest'          => true,
                'rewrite'               => array(
                                            'slug' => 'genres'
                                            )
            );

         register_taxonomy('genre_cat', array('movie_type'), $args);
    }

    /**
     * This is where you add some context
     *
     * @param string $context context['this'] Being the Twig's {{ this }}.
     */
    public function add_to_context($context)
    {
        $context['foo']   = 'bar';
        $context['stuff'] = 'I am a value set in your functions.php file';
        $context['notes'] = 'These values are available everytime you call Timber::context();';
        $context['menu']  = Timber::get_menu();
        $context['site']  = $this;

        return $context;
    }

    public function theme_supports()
    {
        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            array(
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
            )
        );

        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support(
            'post-formats',
            array(
                'aside',
                'image',
                'video',
                'quote',
                'link',
                'gallery',
                'audio',
            )
        );

        add_theme_support('menus');

        load_theme_textdomain('mcd-theme', get_template_directory() . '/assets/translations');
    }

    /**
     * his would return 'foo bar!'.
     *
     * @param string $text being 'foo', then returned 'foo bar!'.
     */
    public function myfoo($text)
    {
        $text .= ' bar!';
        return $text;
    }

    /**
     * This is where you can add your own functions to twig.
     *
     * @param Twig\Environment $twig get extension.
     */
    public function add_to_twig($twig)
    {
        /**
         * Required when you want to use Twig’s template_from_string.
         * @link https://twig.symfony.com/doc/3.x/functions/template_from_string.html
         */
        // $twig->addExtension( new Twig\Extension\StringLoaderExtension() );

        $twig->addFilter(new Twig\TwigFilter('myfoo', [ $this, 'myfoo' ]));

        return $twig;
    }

    /**
     * Updates Twig environment options.
     *
     * @link https://twig.symfony.com/doc/2.x/api.html#environment-options
     *
     * \@param array $options An array of environment options.
     *
     * @return array
     */
    function update_twig_environment_options($options)
    {
        // $options['autoescape'] = true;

        return $options;
    }
}
