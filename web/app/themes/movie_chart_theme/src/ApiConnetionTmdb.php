<?php

namespace src;

class ApiConnetionTmdb
{

    /**
     * Get and update movies from TMDB API
     */
    public function updateMovies()
    {
        $movies = $this->getMovies();

        if ($movies) {
            $this->replaceMoviePosts($movies);
        }
    }

    /**
     * Get movies with cURL from TMDB API
     */
    private function getMovies($movie_amount = 50)
    {

        $api_url_base = 'https://api.themoviedb.org/3';
        $api_endpoint = '/movie/popular';

        $headers = [
            "Authorization: Bearer " . getenv('TMDB_API_KEY'),
            "Content-Type: application/json"
        ];

        // Calculate needed pages. In the API result every page has 20 results
        $page_count = ceil($movie_amount / 20);

        // Make a request for every page
        $handles = [];
        $current_page = $page_count;
        while ($current_page) {
            $param_string = '?page=' . $current_page;

            $handles[$current_page] = curl_init();
            curl_setopt($handles[$current_page], CURLOPT_URL, $api_url_base . $api_endpoint . $param_string);
            curl_setopt($handles[$current_page], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($handles[$current_page], CURLOPT_HTTPHEADER, $headers);

            $current_page--;
        }

        // Combine handles in multi handle
        $multi_handle = curl_multi_init();
        foreach ($handles as $request) {
            curl_multi_add_handle($multi_handle, $request);
        }

        // Execute multi handle curl and wait for completion
        do {
            $status = curl_multi_exec($multi_handle, $active);

            if ($active) {
                curl_multi_select($multi_handle);
            }
        } while ($active && $status == CURLM_OK);


        // Place results in an array with page as key
        $results_per_page = array();
        foreach ($handles as $key => $handle) {
            $result = json_decode(curl_multi_getcontent($handle), true);

            // Check the result is valid
            if ($result && !empty($result['results'])) {
                $results_per_page[$key] = $result['results'];
            } else {
                curl_multi_close($multi_handle);
                return false;
            }

            curl_multi_remove_handle($multi_handle, $handle);
        }

        // Close multi handle
        curl_multi_close($multi_handle);

        // Order pages
        ksort($results_per_page);

        // Merge pages
        $all_movies = [];
        foreach ($results_per_page as $page) {
            $all_movies = array_merge_recursive($all_movies, $page);
        }

        // Return first ($movie_count) results
        return array_slice($all_movies, 0, $movie_amount);
    }

    /**
    * Delete, create or update posts with posttype movie_type
    */
    private function replaceMoviePosts($movies)
    {
        // Img url's need additional path info
        $img_path_pre = $this->getTmdbImagePathPre();

        // Prefix the metakeys to avoid conflicts
        $prefix = '_mcd_movie_';

        // Keep track of current post ids so other old posts can be deleted afterwards
        $current_posts = [];

        foreach ($movies as $movie) {
            // First collect and sanitize all data

            // ******* NORMAL POST DATA *******

            $args = array();

            // Some defaults
            $args['post_status'] = 'publish';
            $args['post_type']   = 'movie_type';

            // Title
            if (!empty($movie['title'])) {
                $args['post_title'] = sanitize_text_field($movie['title']);
            } else {
                // Title is required
                continue;
            }

            // Slug
            if (!empty($movie['title'])) {
                $args['post_name'] = sanitize_title($movie['title']);
            }

            // Content
            if (!empty($movie['overview'])) {
                $args['post_content'] = sanitize_textarea_field($movie['overview']);
            }


            // ******* GENRES ******
            $terms_to_add = [];

            if (!empty($movie['genre_ids'])) {
                foreach ($movie['genre_ids'] as $external_genre_id) {
                    $terms_to_add[] = $this->getGenreCatId($external_genre_id);
                }
            }


            // ******* METADATA ******

            $metadata_to_insert = [
                $prefix.'original_title'    => '',
                $prefix.'original_language' => '',
                $prefix.'popularity'        => '',
                $prefix.'release_date'      => '',
                $prefix.'vote_average'      => '',
                $prefix.'backdrop_path'     => '',
                $prefix.'poster_path'       => '',
            ];

            // External ID
            if (!empty($movie['id'])) {
                $metadata_to_insert[$prefix . 'external_id'] = filter_var($movie['id'], FILTER_SANITIZE_NUMBER_INT);
            } else {
                // External id is needed to check for existing post so its required
                continue;
            }

            // Original title
            if (!empty($movie['original_title'])) {
                $metadata_to_insert[$prefix . 'original_title'] = sanitize_text_field($movie['original_title']);
            }

            // Original language
            if (!empty($movie['original_language'])) {
                $metadata_to_insert[$prefix . 'original_language'] = sanitize_text_field($movie['original_language']);
            }

            // Popularity
            if (!empty($movie['popularity'])) {
                $metadata_to_insert[$prefix . 'popularity'] = filter_var($movie['popularity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }

            // Release_date
            if (!empty($movie['release_date'])) {
                $metadata_to_insert[$prefix . 'release_date'] = strtotime($movie['release_date']);
            }

            // Vote average
            if (!empty($movie['vote_average'])) {
                $metadata_to_insert[$prefix . 'vote_average'] = filter_var($movie['vote_average'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }

            // Vote_count
            if (!empty($movie['vote_count'])) {
                $metadata_to_insert[$prefix . 'vote_count'] = filter_var($movie['vote_count'], FILTER_SANITIZE_NUMBER_INT);
            }

            // Backdrop
            if (!empty($movie['backdrop_path'])) {
                $metadata_to_insert[$prefix . 'backdrop_path'] = $img_path_pre . sanitize_url($movie['backdrop_path']);
            }

            // Poster
            if (!empty($movie['poster_path'])) {
                $metadata_to_insert[$prefix . 'poster_path'] = $img_path_pre . sanitize_url($movie['poster_path']);
            }


            // Create or update the post with collected data
            $existing_post = $this->getMoviePostByExternalId($metadata_to_insert[$prefix . 'external_id']);
            if (!$existing_post) {
                // Create new post
                $post_id = wp_insert_post($args);
            } else {
                // Update the existing post
                $args['ID'] = $post_id = $existing_post->ID;
                wp_update_post($args);
            }
            $current_posts[] = $post_id;


            // Set meta data
            foreach ($metadata_to_insert as $key => $value) {
                update_post_meta($post_id, $key, $value); //note: in case ACF is used this can be changed to update_field() function
            }

            // Set genre_cat
            if (!empty($terms_to_add)) {
                wp_set_object_terms($post_id, $terms_to_add, 'genre_cat', false);
            }
        }

        // Remove old posts
        $args = array(
            'post_type'   => 'movie_type',
            'numberposts' => -1,
            'post__not_in' => $current_posts
        );

        $old_posts = get_posts($args);
        foreach ($old_posts as $old_post) {
            wp_delete_post($old_post->ID, true);
        }

        //TODO Optional: Log the update
    }

    /**
     * Helper function to complete paths of api images values
     */
    private function getTmdbImagePathPre()
    {
        // Todo make a api request to collect the right path data
        /* You'll need 3 pieces of data. Those pieces are a base_url, a file_size and a file_path.
        The first two pieces can be retrieved by calling the /configuration API and the third is the file path you're wishing to grab on a particular media object. */

        return 'https://image.tmdb.org/t/p/w500';
    }

    /**
     * Helper function to match genres with TMDB genre id's
     * - optionally creates a new term if no is found
     */
    private function getGenreCatId($external_genre_id, $create_if_unknown = true)
    {

        $args = [
            'taxonomy' => 'genre_cat',
            'hide_empty' => false,
            'meta_key' => '_genre_cat_external_id',
            'meta_value' => $external_genre_id
        ];

        $terms = get_terms($args);

        if (!empty($terms)) {
            return $terms[0]->term_id;
        } elseif ($create_if_unknown) {
            $name = $this->getTmdbGenreName($external_genre_id);

            if (!$name) {
                return false;
            }

            // create new term
            $term_data = wp_insert_term($name, 'genre_cat');
            if ($term_data) {
                add_term_meta($term_data['term_id'], '_genre_cat_external_id', $external_genre_id);
                return $term_data['term_id'];
            }
        }

        return false;
    }

    /**
     * Helper function to match genres with TMDB genre id's
     */
    private function getTmdbGenreName($id)
    {
        $api_url_base = 'https://api.themoviedb.org/3';
        $api_endpoint = '/genre/movie/list';

        $headers = [
            "Authorization: Bearer " . getenv('TMDB_API_KEY'),
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url_base . $api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = json_decode(curl_exec($ch), true);

        if (!empty($result['genres'])) {
            $key = array_search($id, array_column($result['genres'], 'id'));

            if ($key) {
                return $result['genres'][$key]['name'];
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Helper function to find a post by external_id metavalue
     */
    private function getMoviePostByExternalId($external_id)
    {
        $args = array(
            'post_type'   => 'movie_type',
            'numberposts' => -1,
            'meta_key' => '_mcd_movie_external_id',
            'meta_value' => $external_id
        );

        $movie_posts = get_posts($args);

        if (! empty($movie_posts)) {
            return $movie_posts[0];
        } else {
            return false;
        }
    }
}
