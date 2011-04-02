<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Displays table structure infos like fields/columns, indexes, size, rows
 * and allows manipulation of indexes and columns/fields
 * @package phpMyAdmin
 */
require_once './libraries/common.inc.php';
require_once './libraries/mysql_charsets.lib.php';

$GLOBALS['js_include'][] = 'jquery/jquery-ui-1.8.custom.js';
$GLOBALS['js_include'][] = 'tbl_structure.js';
$titles=array();
$titles = PMA_buildActionTitles();
/**
 * Runs common work
 */
require_once './libraries/tbl_common.php';
$url_query .= '&amp;goto=tbl_structure.php&amp;back=tbl_structure.php';
$url_params['goto'] = 'tbl_triggers.php';
$url_params['back'] = 'tbl_triggers.php';
/**
 * Prepares the table structure display
 */
/**
 * Gets tables informations
 */
require_once './libraries/tbl_info.inc.php';

/**
 * Displays top menu links
 */
require_once './libraries/tbl_links.inc.php';
require_once './libraries/Index.class.php';

// 2. Gets table keys and retains them
// @todo should be: $server->db($db)->table($table)->primary
$primary = PMA_Index::getPrimary($table, $db);
$columns_with_unique_index = array();
foreach (PMA_Index::getFromTable($table, $db) as $index) {
    if ($index->isUnique() && $index->getChoice() == 'UNIQUE') {
        $columns = $index->getColumns();
        foreach ($columns as $column_name => $dummy) {
            $columns_with_unique_index[$column_name] = 1;
        }
    }
}
unset($index, $columns, $column_name, $dummy);

// 3. Get fields
//$fields_rs   = PMA_DBI_query('SHOW CREATE TRIGGERS FROM ' . PMA_backquote($table) . ';', null, PMA_DBI_QUERY_STORE);
// will have to use SHOW CREATE for versions which do not have information_schema was
// will also have to check as if which USER created the TRIGGER
$trigger_query='SELECT TRIGGER_NAME, ACTION_ORDER, ACTION_CONDITION,CREATED,ACTION_TIMING,ACTION_REFERENCE_OLD_ROW,EVENT_MANIPULATION FROM'
         .' information_schema.triggers WHERE EVENT_OBJECT_TABLE=\'' . $table . '\';';
$triggers   = PMA_DBI_fetch_result($trigger_query);
 if ($GLOBALS['cfg']['AjaxEnable']) {
        $conditional_class = 'class="drop_trigger_anchor"';
    } else {
        $conditional_class = '';
    }
?>
<br/>
<a href="tbl_trigg_edit.php?url_query="<?php echo $url_query;?> "&amp;sql_query="<?php echo $create_trigger_query ?>"> Create New Trigger</a>
<fieldset>
    <legend> Triggers </legend>
<form action="tbl_triggers.php" method="post" name="triggersForm" id="triggersForm">
    <?php echo PMA_generate_common_hidden_inputs($db,$table); ?>
    <table name="triggersList" id="triggersList"class="data">
        <th><?php echo __('Name');?></th><th>&nbsp;</th><th>&nbsp;</th><th><?php echo __('Event Manipulation') ?></th>
        <?php
        $odd_row=true;
        foreach ($triggers as $trigger){
      ?>
                <tr class="<?php echo $odd_row ? 'odd' : 'even'; $odd_row = !$odd_row ?>" >
        <?php
            // 'definition' contains the query to edit the routine
          $definition="dfdfd";
          $trigg_name=$trigger['TRIGGER_NAME'];
          $sqlDropTrigger = 'DROP TRIGGER ' . PMA_backquote($trigg_name);
          $sqlEditTrigger = !empty($definition) ? PMA_linkOrButton('tbl_trigg_edit.php?' . $url_query . '&amp;trigger_name='.$trigg_name.'&amp;sql_query=' . urlencode($definition) . '&amp;show_query=1&amp;db_query_force=1&amp;delimiter=' . urlencode($delimiter), $titles['Edit']) : '&nbsp</td>;';
          echo '<td>' . __($trigg_name) . '</td>';          
          echo '<td>'. $sqlEditTrigger . '</td>';
          echo '<td><a '.$conditional_class.'  href="tbl_trigg_edit.php?'.$url_query.'&amp;sql_query'.urlencode($sqlDropTrigger).'" >'. $titles['Drop'].'</a></td>';
         echo '<td align="center">'  . __($trigger['EVENT_MANIPULATION']) . '</td>';
          echo '</tr>';
        }

     ?>
    </table>
</form>
</fieldset>
<?php


/*
 * Display Footers
 */
require_once './libraries/footer.inc.php';

?>