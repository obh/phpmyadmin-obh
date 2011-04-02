<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package phpMyAdmin
 */

/**
 *
 */
require_once './libraries/common.inc.php';
require_once './libraries/db_links.inc.php';

$GLOBALS['js_include'][] = 'jquery/jquery-ui-1.8.custom.js';
$GLOBALS['js_include'][] = 'db_procedure.js';
$GLOBALS['js_include'][] = 'functions.js';

require_once './libraries/db_common.inc.php';
require_once './libraries/bookmark.lib.php';

require_once './libraries/mysql_charsets.lib.php';
$db_collation = PMA_getDbCollation($db);

// in a separate file to avoid redeclaration of functions in some code paths
require_once './libraries/db_structure.lib.php';
$titles = PMA_buildActionTitles();

require './libraries/db_routines.inc.php';

/*
   displays the footer

*/

require_once './libraries/footer.inc.php';

?>


