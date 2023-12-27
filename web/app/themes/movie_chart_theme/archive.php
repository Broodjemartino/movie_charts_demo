<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

$templates = array('archive.twig', 'base.twig');

$context = Timber::context();

// Get all posts
if (is_post_type_archive('movie_type')) {
    $context['posts'] = Timber::get_posts([
        'post_type' => 'movie_type',
        'posts_per_page' => -1,
        'paged' => 1,
    ]);
}

// Make json object for client side filtering
$filter_data = [];
foreach ($context['posts'] as $movie_post) {
    $filter_data[$movie_post->ID] = $movie_post->post_title;
}

$context['filterdata'] = json_encode($filter_data);

// Get all terms of genre_cat for the genre menu
$context['genre_terms'] = Timber::get_terms([
    'taxonomy' => 'genre_cat'
]);


Timber::render($templates, $context);
