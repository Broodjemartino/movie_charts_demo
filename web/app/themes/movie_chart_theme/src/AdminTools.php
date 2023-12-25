<?php

use src\ApiConnetionTmdb;

class AdminTools
{

    public function __construct()
    {

        add_action('admin_menu', array($this, 'addSyncToSubmenu'));

        if (isset($_POST['mcd_update_submit'])) {
            add_action('init', array( $this, 'triggerMovieUpdate' ));
        }
    }


    /**
     * Add menu item for the update function
     */
    public function addSyncToSubmenu()
    {
        add_submenu_page(
            'edit.php?post_type=movie_type', // Parent menu slug (use the appropriate post type's slug)
            'Update movies',            // Page title
            'Update movies',            // Menu title
            'manage_options',           // Capability required to access
            'update-movies',           // Menu slug
            array($this, 'updateMoviePage')         // Callback function to display the content
        );
    }

    /**
     * Create simple form to trigger update function
     */
    public function updateMoviePage()
    {
        echo '<div class="wrap">';
        echo '<h1>Update movies</h1>';

        echo '<p>Click the button below to update the movies. (This also happens periodically)</p>';
        echo '<form method="post" action="">';

        // Add a nonce for safety
        echo '<input type="hidden" name="mcd_update_nonce" value="' . wp_create_nonce('mcd_update_nonce') . '" />';
        echo '<input type="submit" name="mcd_update_submit" class="button button-primary" value="Update" />';
        echo '</form>';


        echo '</div>';
    }

    /**
     * Handle movie update form submission
     */
    public function triggerMovieUpdate()
    {
        // Check nonce
        if (wp_verify_nonce($_POST['mcd_update_nonce'], 'mcd_update_nonce')) {
            $connection = new ApiConnetionTmdb();
            $connection->updateMovies();

            add_action('admin_notices', array($this, 'movieUpdateAdminNoticeSucces'));
        } else {
            add_action('admin_notices', array($this, 'movieUpdateAdminNoticeError'));
        }
    }

    /**
     * Add success message to admin notices
     */
    public function movieUpdateAdminNoticeSucces()
    {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>The movies are updated with the 50 most popular movies from TMDB!</p>';
            echo '</div>';
    }

    /**
     * Add error message to admin notices
     */
    public function movieUpdateAdminNoticeError()
    {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>The movies are updated with the 50 most popular movies from TMDB!</p>';
            echo '</div>';
    }
}
