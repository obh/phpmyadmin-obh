<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/*
 * @package phpMyAdmin
 */
require_once './libraries/common.inc.php';
require_once './libraries/mysql_charsets.lib.php';

$GLOBALS['js_include'][] = 'jquery/jquery-ui-1.8.custom.js';
$GLOBALS['js_include'][] = 'tbl_structure.js';
/**
 * Runs common work
 */
require_once './libraries/tbl_common.php';
$url_query .= '&amp;goto=tbl_structure.php&amp;back=tbl_structure.php';
$url_params['goto'] = 'tbl_trigg_edit.php';
$url_params['back'] = 'tbl_trigg_edit.php';

require_once './libraries/tbl_info.inc.php';
/**
 * Displays top menu links
 */
require_once './libraries/tbl_links.inc.php';
require_once './libraries/Index.class.php';

$trigger_name=$_REQUEST['trigger_name'];
echo '<fieldset>';
echo '<legend>'.__('Edit Trigger').'</legend>';
echo '<form action="tbl_trigger_action.php" method="post" name="edittrigger">';
echo '<table>';
echo '<tr><td>'.__('Name').'</td>';
echo '<td><input type="text" value="'.$trigger_name.'" name="trigg_name" /></td></tr>';
/*
 * Event associated
 */
$metadata=array();
$metadata=OBH_trigger_metadata($trigger_name);

$event_manipulation=array('INSERT','UPDATE','DELETE');
echo '<tr><td>'.__('Trigger Event').'</td>';
echo '<td>&nbsp<select>';
foreach ($event_manipulation as $event ) {
    echo '<option value="'.$event.'" '
       .(($metadata['EVENT_MANIPULATION'] == "'.$event.'") ? __('selected="selected"') : __('')) . ' >'.__($event).'</option>';
}
echo '</select></td></tr>';
$action_timing=array('BEFORE','AFTER');
echo '<tr><td>'.__('Trigger time').'</td><td>&nbsp<select>';
foreach ($action_timing as $action ) {
    echo '<option value="'.$action.'" '
      .(($metadata['ACTION_TIMING'] == "'.$action.'") ? __('selected="selected"') : __('')) . ' >'.__($action).'</option>';
}
echo '</select></td></tr>';
echo '</table>';
echo '<textarea cols="40">'.$metadata['ACTION_STATEMENT'].'</textarea>';
echo '</form>';
echo '</fieldset>';
//echo (($row['Null'] == '' || $row['Null'] == 'NO') ? __('No') : __('Yes'));
//echo '</select>';



/*
 * footers
 */
require_once './libraries/footer.inc.php';

function OBH_trigger_metadata($trigger_name){
    $query = 'SELECT EVENT_MANIPULATION, ACTION_ORDER, ACTION_STATEMENT, ACTION_ORIENTATION,ACTION_TIMING,ACTION_REFERENCE_OLD_ROW, '
          .'ACTION_REFERENCE_NEW_ROW, SQL_MODE FROM `INFORMATION_SCHEMA`.`TRIGGERS` WHERE TRIGGER_NAME=\''. $trigger_name .'\' ;';
  //  echo $query;
    $result = PMA_DBI_try_query($query);
    $rows = PMA_DBI_fetch_result($result);
    $ans = array();
    foreach($rows as $value ){
        $ans=$value;
    }    
     return $ans;
}


?>