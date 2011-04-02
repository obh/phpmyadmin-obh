<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * dumps a database
 *
 * @uses    libraries/db_common.inc.php
 * @uses    libraries/db_info.inc.php
 * @uses    libraries/display_export.lib.php
 * @uses    $tables     from libraries/db_info.inc.php
 * @package phpMyAdmin
 */

/**
 * Gets some core libraries
 */
require_once './libraries/common.inc.php';

$GLOBALS['js_include'][] = 'export.js';

// $sub_part is also used in db_info.inc.php to see if we are coming from
// db_export.php, in which case we don't obey $cfg['MaxTableList']
$sub_part  = '_export';
require_once './libraries/db_common.inc.php';
$url_query .= '&amp;goto=db_proc_export.php';
require_once './libraries/db_info.inc.php';


/**
 * Displays the form
 */
$routine=$_REQUEST['routine_name'];
$export_page_title = __('View dump (schema) of Procedure '.$routine);
// check if $procedure exists or not
$isroutine=PMA_DBI_fetch_result('SHOW CREATE PROCEDURE '. PMA_backquote($db). '.' . PMA_backquote($routine).';');
//echo 'SHOW CREATE PROCEDURE '.PMA_backquote($db).'.'.PMA_backquote($routine).';';
// exit if no tables in db found
if (!$isroutine) {
    PMA_Message::error(__('Procedure '.$routine.' not found in database.'))->display();
    require './libraries/footer.inc.php';
    exit;
} // end if
$procedure_list=array();
$procedure_list=OBH_getProcedures($db);
function OBH_getProcedures($db){    
    $ret=array();
    $sql='show procedure status where Db=\''.$db.'\' ;';    
    $result = PMA_DBI_fetch_result($sql);    
    return $result;
}

$checkall_url = 'db_proc_export.php?'
              . PMA_generate_common_url($db)
              . '&amp;goto=db_export.php';

$multi_values = '<div>';
$multi_values .= '<a href="' . $checkall_url . '" onclick="setSelectOptions(\'dump\', \'table_select[]\', true); return false;">' . __('Select All') . '</a>
        /
        <a href="' . $checkall_url . '&amp;unselectall=1" onclick="setSelectOptions(\'dump\', \'table_select[]\', false); return false;">' . __('Unselect All') . '</a><br />';

$multi_values .= '<select name="procedure_select[]" id="procedure_select" size="10" multiple="multiple">';
$multi_values .= "\n";

if (!empty($selected_tbl) && empty($table_select)) {
    $table_select = $selected_tbl;
}

// Check if the selected tables are defined in $_GET (from clicking Back button on export.php)
if(isset($_GET['table_select'])) {
    $_GET['table_select'] = urldecode($_GET['table_select']);
    $_GET['table_select'] = explode(",", $_GET['table_select']);
}

foreach ($procedure_list as $each_procedure) {
    // OBH - should be 'selected' for all
    if(isset($_GET['table_select'])) {
        if(in_array($each_procedure['Name'], $_GET['table_select'])) {
            $is_selected = ' selected="selected"';
        } else {
            $is_selected = '';
        }
    } elseif (! empty($unselectall)
            || (! empty($table_select) && !in_array($each_procedure['Name'], $table_select))) {
        $is_selected = '';
    } else {
        $is_selected = ' selected="selected"';
    }
    $procedure_html   = htmlspecialchars($each_procedure['Name']);
    $multi_values .= '                <option value="' . $procedure_html . '"'
        . $is_selected . '>'
        . str_replace(' ', '&nbsp;', $procedure_html) . '</option>' . "\n";
} // end for

$multi_values .= "\n";
$multi_values .= '</select></div>';
$export_type = 'procedure';
require_once './libraries/OBH_display_export.lib.php';

/**
 * Displays the footer
 */
require './libraries/footer.inc.php';
?>
