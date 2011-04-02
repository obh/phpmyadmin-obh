<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package phpMyAdmin
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
$user=$GLOBALS['cfg']['Server']['user'];

$show_query= 'SHOW EVENTS;';
$events=PMA_DBI_fetch_result($show_query);
echo '<fieldset>';
echo '<legend>'.__('Events').'</legend>';
if($events){

    echo '<table>';
    echo '<th>'.__('Name').'</th><th>&nbsp;</th><th>&nbsp;</th><th>'.__('Status').'</th><th>'.__('Type').'</th><th>'.__('Execute At').'</th>';
    $odd=true;
    foreach ( $events as $event ){
        echo '<tr class="'.(($odd) ? 'odd' : 'even'). '">';
        echo '<td>'.$event['Name'].'</td>';
        echo '<td>'.PMA_linkOrButton('db_edit_event.php?' . $url_query . '&amp;event_name='.$event['Name'].'&amp;db_query_force=1&amp;', $titles['Edit']).'</td>';
        echo '<td>'.PMA_linkOrButton('db_event_drop.php?' . $url_query . '&amp;event_name='.$event['Name'].'&amp;db_query_force=1&amp;', $titles['Drop']).'</td>';
        echo '<td>'.$event['Status'].'</td>';
        echo '<td>'.$event['Type'].'</td>';
        echo '<td>'.$event['Execute at'].'</td>';
        echo '</tr>';
    }
    echo '</table>';
}
else {
    echo __('No Events Found for this schema');
}
echo '</fieldset>';
/*
 *  displays the footer
*/

require_once './libraries/footer.inc.php';
?>