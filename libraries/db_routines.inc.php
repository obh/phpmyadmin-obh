<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @todo Support seeing the "results" of the called procedure or
 *       function. This needs further reseach because a procedure
 *       does not necessarily contain a SELECT statement that
 *       produces something to see. But it seems we could at least
 *       get the number of rows affected. We would have to
 *       use the CLIENT_MULTI_RESULTS flag to get the result set
 *       and also the call status. All this does not fit well with
 *       our current sql.php.
 *       Of course the interface would need a way to pass calling parameters.
 *       Also, support DEFINER (like we do in export).
 * @package phpMyAdmin
 */
if (! defined('PHPMYADMIN')) {
    exit;
}

$routines = PMA_DBI_fetch_result('SELECT SPECIFIC_NAME,ROUTINE_NAME,ROUTINE_TYPE,DTD_IDENTIFIER,CREATED,ROUTINE_COMMENT,LAST_ALTERED FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA= \'' . PMA_sqlAddslashes($db,true) . '\';');

if ($routines) {
    PMA_generate_slider_effect('routines', __('Routines'));
    echo '<fieldset>' . "\n";
    echo ' <legend>' . __('Routines') . '</legend>' . "\n";
    echo '<table border="0">';
    echo sprintf('<tr>
                      <th>%s</th>
                      <th>&nbsp;</th>
                      <th>&nbsp;</th>
                      <th>&nbsp;</th>
                      <th>&nbsp;</th>
                      <th>%s</th>
                      <th>%s</th>
                      <th>%s</th>
                      <th>%s</th>
                </tr>',
          __('Name'),
          __('Type'),
          __('Return type'),
          __('Created'),
          __('Last Modified'));
    $ct=0;
    $delimiter = '//';
    if ($GLOBALS['cfg']['AjaxEnable']) {
        $conditional_class = 'class="drop_procedure_anchor"';
    } else {
        $conditional_class = '';
    }
   
   // echo $_REQUEST['server'];
    foreach ($routines as $routine) {

        // information_schema (at least in MySQL 5.0.45)
        // does not return the routine parameters
        // so we rely on PMA_DBI_get_definition() which
        // uses SHOW CREATE

        $definition = 'DROP ' . $routine['ROUTINE_TYPE'] . ' ' . PMA_backquote($routine['SPECIFIC_NAME']) . $delimiter . "\n"
            .  PMA_DBI_get_definition($db, $routine['ROUTINE_TYPE'], $routine['SPECIFIC_NAME'])
            . "\n";
        // use SHOW CREATE PROCEDURE for this purpose rather than using this...
        $exec = PMA_DBI_fetch_result("SELECT proc.param_list  FROM  mysql.proc WHERE proc.name='" . $routine['SPECIFIC_NAME'] . "' AND proc.db = '" . PMA_sqlAddslashes($db,true) . "' ;");
        foreach ($exec as $obh) {
            $exec_call = 'CALL ' . $routine['SPECIFIC_NAME'] . '('. $obh . ')';
        }
// $exec= PMA_DBI_get_definition($db, $routine['ROUTINE_TYPE'], $routine['SPECIFIC_NAME']);

        //if ($routine['ROUTINE_TYPE'] == 'PROCEDURE') {
        //    $sqlUseProc  = 'CALL ' . $routine['SPECIFIC_NAME'] . '()';
        //} else {
        //    $sqlUseProc = 'SELECT ' . $routine['SPECIFIC_NAME'] . '()';
            /* this won't get us far: to really use the function
               i'd need to know how many parameters the function needs and then create
               something to ask for them. As i don't see this directly in
               the table i am afraid that requires parsing the ROUTINE_DEFINITION
               and i don't really need that now so i simply don't offer
               a method for running the function*/
        //}
        if ($routine['ROUTINE_TYPE'] == 'PROCEDURE') {
            $sqlDropProc = 'DROP PROCEDURE ' . PMA_backquote($routine['SPECIFIC_NAME']);
        } else {
            $sqlDropProc = 'DROP FUNCTION ' . PMA_backquote($routine['SPECIFIC_NAME']);
        }

        echo sprintf('<tr class="%s">
                          <td><input type="hidden" class="drop_procedure_sql" value="%s" /><strong>%s<span class="tblcomment">%s</span></strong></td>
                          <td>%s</td>
                          <td>%s</td>
                          <td>%s</td>
                          <td>%s</td>
                          <td>%s</td>
                          <td>%s</td>
                          <td>%s</td>
                     </tr>',
                     ($ct%2 == 0) ? 'even' : 'odd',
                     $sqlDropProc,
                     $routine['ROUTINE_NAME'],
                     $routine['ROUTINE_COMMENT'],
                     ! empty($definition) ? PMA_linkOrButton('db_mysql.php?' . $url_query . '&amp;routine_name='.$routine['ROUTINE_NAME'].'&amp;sql_query=' . urlencode($definition) . '&amp;show_query=1&amp;db_query_force=1&amp;delimiter=' . urlencode($delimiter), $titles['Edit']) : '&nbsp;',
                     '<a ' . $conditional_class . ' href="sql.php?' . $url_query . '&amp;sql_query=' . urlencode($sqlDropProc) . '" >' . $titles['Drop'] . '</a>',
                     ! empty($definition) ? PMA_linkOrButton('db_proc_execute.php?' . $url_query . '&amp;routine_name='.$routine['ROUTINE_NAME'].'&amp;sql_query=' . urlencode($exec_call)  . '&amp;show_query=1&amp;db_query_force=1&amp;delimiter=' . urlencode($delimiter), $titles['Execute']) : '&nbsp;',
                     ! empty($definition) ? PMA_linkOrButton('db_proc_export.php?' . $url_query . '&amp;routine_name='.$routine['ROUTINE_NAME'], $titles['Export']) : '&nbsp;',
                   //  'empty for now',
                     $routine['ROUTINE_TYPE'],
                     $routine['DTD_IDENTIFIER'],
                     $routine['CREATED'],
                     $routine['LAST_ALTERED']);
        $ct++;
    }
    echo '</table>';
    echo '</fieldset>' ."\n";
    echo '</div>' . "\n";
}
?>
