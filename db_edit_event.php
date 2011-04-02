<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package phpMyAdmin
 */
require_once './libraries/common.inc.php';
require_once './libraries/db_links.inc.php';

$GLOBALS['js_include'][] = 'jquery/jquery-ui-1.8.custom.js';
$GLOBALS['js_include'][] = 'jquery/timepicker.js';

require_once './libraries/db_common.inc.php';
require_once './libraries/bookmark.lib.php';

require_once './libraries/mysql_charsets.lib.php';
$db_collation = PMA_getDbCollation($db);

// in a separate file to avoid redeclaration of functions in some code paths
require_once './libraries/db_structure.lib.php';
$titles = PMA_buildActionTitles();
$curr_user=$GLOBALS['cfg']['Server']['user'];
$curr_host=$GLOBALS['cfg']['Server']['host'];
//echo $curr_user.$curr_host;
$db = $_REQUEST['db'];
$event_name = $_REQUEST['event_name'];
// query shall depend on the verion of mysql being used, whether to use INFORMATION_SCHEMA or SHOW CREATE
$event_query='SELECT EVENT_SCHEMA, EVENT_NAME, DEFINER, TIME_ZONE,EVENT_BODY,EVENT_DEFINITION,EVENT_TYPE,EXECUTE_AT,INTERVAL_VALUE,INTERVAL_FIELD,'
        .' STARTS,ENDS,STATUS,ON_COMPLETION,EVENT_COMMENT FROM `INFORMATION_SCHEMA`.`EVENTS` WHERE EVENT_NAME=\''.$event_name.'\';';
$events=PMA_DBI_fetch_result($event_query);
echo '<fieldset>';
echo '<legend>'.__('Edit Event: '.$event_name).'</legend>';
echo '<form name="edit_event" action="edit_event_action.php" method="post">';
foreach ($events as $event){
    echo '<div>';
    echo '<table><tr>';
    echo '<td><span>'.__('Name').'</span></td>';
    echo '<td><input type="text" name="event_name" value="'.$event['EVENT_NAME'].'" />'
        .'<span><a href=# > change database</a></td></tr>';
    echo '<tr><td><span>'.__('Definer').'</span></td>';
    if($is_superuser){
        echo '<td>&nbsp;<select name="definier">';
        $users=array();
        $users=PMA_DBI_fetch_result('SELECT USER,HOST FROM `mysql`.`user`;');
        foreach($users as $user){
            echo '<option value="'.(($user['USER']=='')?'ANY':$user['USER']).$user['HOST'].'" '. (($user['USER']==$curr_user && $user['HOST']==$curr_host) ? 'selected="selected"' : ''). '>'
            .(($user['USER']=='') ? '<span style="color: #FF0000">'.__('ANY') : $user['USER']) . '@'.htmlspecialchars($user['HOST']).'</option>';
        }
        echo '</select></td>';
    }
    else {
        echo '<td><span>'.__($curr_user.'@'.$curr_host).'</span></td>';
    }
    echo '</tr>';
    echo '<tr><td>'.__('Schedule').'</td>';    
    echo '<td>&nbsp;<select id="schedule" onchange="change_schedule()"><option value="at">AT</option><option value="every">EVERY</option></select></tr>';
    echo '<script>function change_schedule(){ $("#schedule").change(function(){ if(this.value=="at"){ $("#schedule_onetime").show();$("#schedule_recursive").hide();} else{ $("#schedule_onetime").hide();$("#schedule_recursive").show(); } });}</script>';
    echo '</table>';
    echo '<table id="schedule_onetime">';
    echo '<tr><th>Name</th><th>Function</th><th>Input</th></tr>';
    echo '<tbody>';
    echo '<tr><td><span>'.__('Time').'</span></td>';
    $dropdown=array();
    $current_func_type= $GLOBALS['cfg']['RestrictColumnTypes']['DATE'];    
    $dropdown= $GLOBALS['cfg']['RestrictFunctions'][$current_func_type];
    $default_function = $GLOBALS['cfg']['DefaultFunctions']['first_timestamp'];    
    echo '<td>&nbsp;<select><option></option>';
    foreach ( $dropdown as $each_dropdown){
        echo '<option>'.$each_dropdown.'</option>';
    }
    echo '</select></td>';
    echo '<td><input id="datepicker" type="text" name="time" /></td></tr>';
    echo '<script>$(function() { $( "#datepicker" ).datepicker();}); </script>';    // add external js
    echo '<tr><td><span><a href=#>Add Interval</a></span></td></tr>';
    echo '</table>';
    echo '<table id="schedule_recursive" style="display:none">';
    echo '<tr><td>Recursive</td>';
    echo '<td>&nbsp;<select><option>Year</option><option>Quarter</option><option>Month</option><option>Day</option><option>Hour</option></select></td>';
    // add more
    echo '<td><span><a href="#">add more interval</a></span></td></tr>';
    echo '<tr><td>Starts</td><td><span><a href=#>add interval</a></span></td></tr>';
    echo '<tr><td>Ends</td><td><span><a href=#>add interval</a></span></td></tr>';
    echo '</table>';
    echo '<table>';    
    echo '<tr><td>'. __('Enabled'). ' &nbsp;&nbsp;<input type="checkbox" value="enabled" checked name="status"></td></tr>';
    echo '<tr><td>'. __('Preserve On Completion'). ' &nbsp;&nbsp;<input type="checkbox" value="preserve" name="on_completion"></td></tr>';
    echo '<tr><td>'.__('Event Comment').'</td><td><input type="text" name="event_comment" value="'.$event['EVENT_COMMENT'].'" /></td></tr>';
    echo '</table>';
    echo '<span><h3>'.__('Event Definition').'</h3></span><br>';
    echo '<textarea cols="60" style="font-size:16px" >'.$event['EVENT_DEFINITION'].'</textarea>';
}
echo '</form>';
echo '</fieldset>';

?>