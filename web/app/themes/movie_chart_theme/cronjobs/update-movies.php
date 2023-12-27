<?php

use src\ApiConnetionTmdb;

if (! wp_next_scheduled('mcd_update_movies_every_hour')) {
    //cronjob sync every hour
    wp_schedule_event(time(), 'hourly', 'mcd_update_movies_every_hour');
}

add_filter('mcd_update_movies_every_hour', 'mcd_get_movies_from_api');
function mcd_get_movies_from_api()
{
    $connection = new ApiConnetionTmdb();
    $connection->updateMovies();
}
