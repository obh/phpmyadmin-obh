<?php

require_once 'libraries/common.inc.php';
require_once 'libraries/mysql_charsets.lib.php';

$GLOBALS['js_include'][] = 'functions.js';

require './libraries/db_common.inc.php';
require_once './libraries/sql_query_form.lib.php';

$goto = 'db_sql.php';
$back = 'db_sql.php';
require './libraries/db_info.inc.php';

//    echo "HELLO";
    $sql_query=$GLOBALS['sql_query'] ;
    $routine_name=$_REQUEST['routine_name'];
    $query = substr($sql_query, 6+strlen($routine_name));
    echo '<form action="libraries/proc_execute.php" id="proc_execute" method="post">';
    echo '<table><th>'.__('proc_parameter').'</th><th>'.__('param_name').'</th><th>'.__('Type').'</th><th>'.__('Function').'</th><th>'.__('Value').'</th>';
    PMA_generate_common_hidden_inputs();
    OBH_param_list($query);
    echo '<tr><td>&nbsp;</td><td>&nbsp;</td>';
    echo '<td><input type="submit" name="submit" value="Go" /></form></td><td>&nbsp;</td></tr>';
    echo '</table>';
    echo '</form>';            
/**
* Displays the footer
*/
require './libraries/footer.inc.php';

function OBH_param_list($params){
    $params=substr($params,0,strlen($params)-1);
    if(strlen($params)==0){
        echo '<tr><td>&nbsp</td><td>&nbsp</td>'
            .'<td>'
            .'<span>'. __('No parameters defined') . '</span></td></tr>';
            // direct to action page
        return;
    }
    $param_list = explode(",",$params); 
    foreach ($param_list as $param ){      
        $param = trim ( $param );
        $in_each = explode(" ",$param);
        switch ($in_each[0]){
            case 'IN':
                echo '<tr><td class="data not _null">IN</td>';
                echo '<td> '.$in_each[1].'</td>';
                echo '<td> '.$in_each[2].' </td>';
                echo OBH_function($in_each[2]);
                echo '<td> <input type="text" name="param" /></td>';
                break;
            case 'OUT':
                echo '<tr><td class="data not _null">OUT</td>';
                echo '<td> '.$in_each[1].'</td>';
                echo '<td> '.$in_each[2].' </td>';
                echo OBH_function($in_each[2]);
                echo '<td><input type="text" name="param" /></td>';
                break;
            case 'INOUT':
                echo '<tr><td class="data not _null">INOUT</td>';
                echo '<td>'.$in_each[1].'</td>';
                echo '<td> '.$in_each[2].' </td>';
                echo OBH_function($in_each[2]);
                echo '<td><input type="text" name="param" /></td>';
                break;
        }
        echo '</tr>';
    }
        
}

function OBH_function($field){
      $current_func_type  = $GLOBALS['cfg']['RestrictColumnTypes'][strtoupper($field)];
      $dropdown       = $GLOBALS['cfg']['RestrictFunctions'][$current_func_type];
      $default_function   = $GLOBALS['cfg']['DefaultFunctions'][$current_func_type];
      $ret= '<td><select>';
      $ret.='<option></option>';
      foreach ($dropdown as $each_dropdown){
          $ret.= '<option';
          if ($default_function === $each_dropdown) {
          $ret.= ' selected="selected"';
          }
          $ret.= '>' . $each_dropdown . '</option>' . "\n";
          $dropdown_built[$each_dropdown] = 'TRUE';
          $op_spacing_needed = TRUE;                
      }
      $cnt_functions = count($GLOBALS['cfg']['Functions']);
      for ($j = 0; $j < $cnt_functions; $j++) {
           if (!isset($dropdown_built[$GLOBALS['cfg']['Functions'][$j]]) || $dropdown_built[$GLOBALS['cfg']['Functions'][$j]] != 'TRUE') {
                        // Is current function defined as default?
                        $selected = ($cfg['Functions'][$j] == $cfg['DefaultFunctions']['first_timestamp'])
                                    || ($cfg['Functions'][$j] == $default_function)
                                  ? ' selected="selected"'
                                  : '';                        
                        if ($op_spacing_needed == TRUE) {
                            $ret.= '                ';
                            $ret.= '<option value="">--------</option>' . "\n";
                            $op_spacing_needed = FALSE;
                        }                        
                        $ret.= '<option' . '>' . $GLOBALS['cfg']['Functions'][$j] . '</option>' . "\n";
                    }
                }// end for                
      $ret.= '</select></td>';
      return $ret;
}

?>

