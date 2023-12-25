<?php
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/StarterSite.php';

require_once __DIR__ . '/src/ApiConnetionTmdb.php';


Timber\Timber::init();

// Sets the directories (inside your theme) to find .twig files.
Timber::$dirname = [ 'templates', 'views' ];

new StarterSite();


// For admin pages only
if (is_admin()) {
    require_once __DIR__ . '/src/AdminTools.php';
    new AdminTools();
}
