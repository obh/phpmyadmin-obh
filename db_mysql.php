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
require_once './libraries/mysql_charsets.lib.php';
//require_once './libraries/config.default.php';
/**
 * Runs common work
 */
$GLOBALS['js_include'][] = 'functions.js';
$GLOBALS['js_include'][] = 'sql.js';

require './libraries/db_common.inc.php';
require_once './libraries/sql_query_form.lib.php';

// After a syntax error, we return to this script
// with the typed query in the textarea.
$goto = 'db_sql.php';
$back = 'db_sql.php';

/**
 * Gets informations about the database and, if it is empty, move to the
 * "db_structure.php" script where table can be created
 */
require './libraries/db_info.inc.php';
if ($num_tables == 0 && empty($db_query_force)) {
    $sub_part   = '';
    $is_info    = TRUE;
    require './db_structure.php';
    exit();
}
/* obh */
// $comments_map = PMA_getComments($db, $table); // get comments for this procedure


/**
 * Query box, bookmark, insert data from textfile
 */
$obh_query = $GLOBALS['sql_query'];
$obh_proc_name = $_REQUEST['routine_name'];
$db = $_REQUEST['db'];
$table = $_REQUEST['table'];
echo '<br/>';
echo __('Edit Procedure: ') .'<strong>'. $obh_proc_name."</strong><br/>";


//PMA_sqlQueryForm(true, false, isset($_REQUEST['delimiter']) ? htmlspecialchars($_REQUEST['delimiter']) : ';');

OBH_edit_proc($obh_proc_name,$db,'proc');

function OBH_edit_proc($proc_name,$db,$table){
    if(PMA_DBI_select_db('mysql')){
    }
 //   echo '<br/>';
    echo '<form id="procedureeditform" target="frame_content" action="libraries/obh_routine_edit.php" method="post">'."\n";
    echo '<input type="hidden" name="old_name" value="'.$proc_name.'" />'."\n";
    echo PMA_generate_common_hidden_inputs('mysql','proc')."\n";
    echo '<input type="hidden" name="goto" value="'.$goto.'" />'."\n";

// get me the parameters
 //   PMA_generate_slider_effect('parameters',__('PARAMETERS'));
 //   echo '<fieldset>' ."\n";
 //   echo '<legend>' . __('Parameters') .'</legend>';
    echo '<table id="proc_details" >'."\n";
    echo '<th>Field Name</th><th>Value</th><th>Type</th><th>&nbsp</th>'."\n";
    OBH_create_form($proc_name,$db,$table);
 //   echo '</table>'."\n";
 //   echo '</div>';  // end parameters
    echo '</form>';
    echo '<br/>';

/*    PMA_generate_slider_effect('general',__('DETAILS'));
    echo '<fieldset>' ."\n";
    echo '<legend>' . __('Details') . '</legend>';
    echo '<table><tr><td>HELLO</td></tr></table>';
    echo '</fieldset>';
    echo '</div>';  */

//    echo '<form>'.OBH_input_all().'</form>';  
    
}
function OBH_create_form($proc_name,$db,$table){
    
    $where_clause= PMA_backquote($table) . '.' . PMA_backquote('name') . ' = \'' . $proc_name . '\' and '
        .PMA_backquote($table) . '.' . PMA_backquote('Db') . ' = \'' . $db .'\' ; ';

    $local_query = 'select * from '. PMA_backquote('proc') . ' where '.$where_clause;

  //  echo $local_query . "\n";     LOCAL QUERY

    $table_fields = PMA_DBI_fetch_result('SHOW FIELDS FROM ' . PMA_backquote($table) . ';',
                null, null, null, PMA_DBI_QUERY_STORE);

  //  echo '<br/>'.count($table_fields).'<br/>';  TOTAL NUMBER OF FIELDS IN TABLE

    $result = PMA_DBI_query($local_query,null,PMA_DBI_QUERY_STORE);
    $rows = PMA_DBI_fetch_row($result);
    $i=0;
    foreach ($rows as $row ) {
        if( $table_fields[$i]['Field']=='name'){
            echo '<tr><td><input type="hidden" name="original_name" value="'.$row.'" /></td></tr>';
            echo '<tr><td class="data not_null odd"> Name  </td>';
            echo '<td><input type="text" name="new_name" value="'.$row.'" /></td>'
                 .'<td>'.__($table_fields[$i]['Type']).' </tr>';
        }
        if( $table_fields[$i]['Field']=='param_list'){
            OBH_param_list($row);
        }
        if( $table_fields[$i]['Field']=='body_utf8') {
            OBH_procedure_body($row);
        }
       // echo   $table_fields[$i]['Field'].'   '.$table_fields[$i]['Type']. '   ' . $row. ' <br/>';       
        $i++;
    }

}

function OBH_param_list($params){

    $param_list = explode(",",$params); 
    foreach ($param_list as $param ){
        $param = trim ( $param );
        $in_each = explode(" ",$param);
        switch ($in_each[0]){
            case 'IN':
                echo '<tr><td class="data not _null"><select><option value="in"> IN </option>'
                     .'<option value="out"> OUT </option>'
                     .'<option value="inout">INOUT</option></select></td>';
                echo '<td> <input type="text" value="'.$in_each[1].'" /> </td>';
                echo '<td> '.OBH_input_all($in_each[2]).' </td>';
                break;
            case 'OUT':
                echo '<tr><td class="data not _null"><select><option value="in"> IN </option>'
                     .'<option value="out"> OUT </option>'
                     .'<option value="inout">INOUT</option></select></td>';
                echo '<td> <input type="text" value="'.$in_each[1].'" /> </td>';
                echo '<td> '.OBH_input_all($in_each[2]).' </td>';
                break;
            case 'INOUT':
                echo '<tr><td class="data not _null"><select><option value="in"> IN </option>'
                     .'<option value="out"> OUT </option>'
                     .'<option value="inout">INOUT</option></select></td>';
                echo '<td> <input type="text" value="'.$in_each[1].'" /> </td>';
                echo '<td> '.OBH_input_all($in_each[2]).' </td>';
                break;
        }
        echo '<td><span>'
            .'<img height="16" width="16" class="icon" alt="Delete" title="Delete" src="./themes/pmahomme/img/b_drop.png">'
            .'<a id="drop_param" href="#procedureeditform">Delete</a></span></td>';
        echo '</tr>';
    }
        echo '<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>'
            .'<td>'
            .'<span id="anchor_status_displayoptions">+ </span>'
            .'<a id="add_param" href="#procedureeditform" >'. __('Add another parameter') . '</td></tr>';
}

function OBH_input_all($type_upper){

    $i=0;
    $ci=0;
    $select_id='field_0_2';
    $content_cells = array();
    $content_cells[$i][$ci] = '<select class="column_type" name="field_type[' . $i . ']"'
                .' id="' . $select_id . '">';
    foreach ( $GLOBALS['cfg']['ColumnTypes'] as $col_goup => $column_type) {
        if (is_array($column_type)) {
            $content_cells[$i][$ci] .= '<optgroup label="' . htmlspecialchars($col_goup) . '">';
            foreach ($column_type as $col_group_type) {
                $content_cells[$i][$ci] .= '<option value="'. $col_group_type . '"';
                if ($type_upper == strtoupper($col_group_type)) {
                    $content_cells[$i][$ci] .= ' selected="selected"';
                }
                $content_cells[$i][$ci] .= '>' . $col_group_type . '</option>';
            }
            $content_cells[$i][$ci] .= '</optgroup>';
            continue;
        }

        $content_cells[$i][$ci] .= '<option value="'. $column_type . '"';
        if ($type_upper == strtoupper($column_type)) {
            $content_cells[$i][$ci] .= ' selected="selected"';
        }
        $content_cells[$i][$ci] .= '>' . $column_type . '</option>';
    } // end for
    $content_cells[$i][$ci] .='    </select>';
    return $content_cells[$i][$ci];   
}

function OBH_procedure_body($body){
    echo '<tr><td>';
    echo '<textarea id="proc_body" >'.$body
        .'</textarea>';
        //</fieldset>';
    echo '</td></tr>';
}

/**
 * Displays the footer
 */
require './libraries/footer.inc.php';
?>
