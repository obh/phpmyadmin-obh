/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * @fileoverview    functions used in server privilege pages
 * @name            Server Privileges
 *
 * @requires    jQuery
 * @requires    jQueryUI
 * @requires    js/functions.js
 *
 */

/**
 * Validates the password field in a form
 *
 * @see     PMA_messages['strPasswordEmpty']
 * @see     PMA_messages['strPasswordNotSame']
 * @param   object   the form
 * @return  boolean  whether the field value is valid or not
 */
function checkPassword(the_form)
{
    // Did the user select 'no password'?
    if (typeof(the_form.elements['nopass']) != 'undefined'
     && the_form.elements['nopass'][0].checked) {
        return true;
    } else if (typeof(the_form.elements['pred_password']) != 'undefined'
     && (the_form.elements['pred_password'].value == 'none'
      || the_form.elements['pred_password'].value == 'keep')) {
        return true;
    }

    var password = the_form.elements['pma_pw'];
    var password_repeat = the_form.elements['pma_pw2'];
    var alert_msg = false;

    if (password.value == '') {
        alert_msg = PMA_messages['strPasswordEmpty'];
    } else if (password.value != password_repeat.value) {
        alert_msg = PMA_messages['strPasswordNotSame'];
    }

    if (alert_msg) {
        alert(alert_msg);
        password.value  = '';
        password_repeat.value = '';
        password.focus();
        return false;
    }

    return true;
} // end of the 'checkPassword()' function


/**
 * Validates the "add a user" form
 *
 * @return  boolean  whether the form is validated or not
 */
function checkAddUser(the_form)
{
    if (the_form.elements['pred_hostname'].value == 'userdefined' && the_form.elements['hostname'].value == '') {
        alert(PMA_messages['strHostEmpty']);
        the_form.elements['hostname'].focus();
        return false;
    }

    if (the_form.elements['pred_username'].value == 'userdefined' && the_form.elements['username'].value == '') {
        alert(PMA_messages['strUserEmpty']);
        the_form.elements['username'].focus();
        return false;
    }

    return checkPassword(the_form);
} // end of the 'checkAddUser()' function

/**
 * When a new user is created and retrieved over Ajax, append the user's row to
 * the user's table
 *
 * @param   new_user_string         the html for the new user's row
 * @param   new_user_initial        the first alphabet of the user's name
 * @param   new_user_initial_string html to replace the initial for pagination
 */
function appendNewUser(new_user_string, new_user_initial, new_user_initial_string) {
    //Append the newly retrived user to the table now

    //Calculate the index for the new row
    var $curr_last_row = $("#usersForm").find('tbody').find('tr:last');
    var $curr_first_row = $("#usersForm").find('tbody').find('tr:first');
    var first_row_initial = $curr_first_row.find('label').html().substr(0, 1).toUpperCase();
    var curr_shown_initial = $curr_last_row.find('label').html().substr(0, 1).toUpperCase();
    var curr_last_row_index_string = $curr_last_row.find('input:checkbox').attr('id').match(/\d+/)[0];
    var curr_last_row_index = parseFloat(curr_last_row_index_string);
    var new_last_row_index = curr_last_row_index + 1;
    var new_last_row_id = 'checkbox_sel_users_' + new_last_row_index;
    var is_show_all = (first_row_initial != curr_shown_initial) ? true : false;

    //Append to the table and set the id/names correctly
    if((curr_shown_initial == new_user_initial) || is_show_all) {
        $(new_user_string)
        .insertAfter($curr_last_row)
        .find('input:checkbox')
        .attr('id', new_last_row_id)
        .val(function() {
            //the insert messes up the &amp;27; part. let's fix it
            return $(this).val().replace(/&/,'&amp;');
        })
        .end()
        .find('label')
        .attr('for', new_last_row_id)
        .end();
    }

    //Let us sort the table alphabetically
    $("#usersForm").find('tbody').PMA_sort_table('label');

    $("#initials_table").find('td:contains('+new_user_initial+')')
    .html(new_user_initial_string);
};

/**#@+
 * @namespace   jQuery
 */

/**
 * AJAX scripts for server_privileges page.
 *
 * Actions ajaxified here:
 * Add a new user
 * Revoke a user
 * Edit privileges
 * Export privileges
 * Paginate table of users
 * Flush privileges
 *
 * @memberOf    jQuery
 * @name        document.ready
 */

$(document).ready(function() {
    /** @lends jQuery */

    /**
     * Set a parameter for all Ajax queries made on this page.  Some queries
     * are affected by cache settings on the server side, and hence, show stale
     * data.  Don't let the web server serve cached pages
     */
    $.ajaxSetup({
        cache: 'false'
    });

    /**
     * AJAX event handler for 'Add a New User'
     *
     * @see         PMA_ajaxShowMessage()
     * @see         appendNewUser()
     * @see         $cfg['AjaxEnable'] 
     * @memberOf    jQuery
     * @name        add_user_click
     *
     */
    $("#fieldset_add_user a.ajax").live("click", function(event) {
        /** @lends jQuery */
        event.preventDefault();

        PMA_ajaxShowMessage();

        /**
         * @var button_options  Object containing options for jQueryUI dialog buttons
         */
        var button_options = {};
        button_options[PMA_messages['strCreateUser']] = function() {

            /**
             * @var $form    stores reference to current form
             */
<<<<<<< Updated upstream
            var $form = $(this).find("form[name=usersForm]").last();

=======
            var $form = $(this).find("#addUsersForm");            
>>>>>>> Stashed changes
            if( ! checkAddUser($form.get(0)) ) {
                PMA_ajaxShowMessage(PMA_messages['strFormEmpty']);
                return false;
            }

            //We also need to post the value of the submit button in order to get this to work correctly
            $.post($form.attr('action'), $form.serialize() + "&adduser_submit=" + $(this).find("input[name=adduser_submit]").attr('value'), function(data) {
                if(data.success == true) {
                    $("#add_user_dialog").dialog("close").remove();
                    PMA_ajaxShowMessage(data.message);
                    $("#topmenucontainer")
                     .next('div')
                     .remove()
                     .end()
                     .after(data.sql_query);
                                                                
                     //Remove the empty notice div generated due to a NULL query passed to PMA_showMessage()
                     var $notice_class = $("#topmenucontainer").next("div").find('.notice');
                     if($notice_class.text() == '') {
                        $notice_class.remove();
                     }

                     appendNewUser(data.new_user_string, data.new_user_initial, data.new_user_initial_string);
                } else {
                     PMA_ajaxShowMessage(PMA_messages['strErrorProcessingRequest'] + " : "+data.error, "7000");
                }
            })
        };
        button_options[PMA_messages['strCancel']] = function() {$(this).dialog("close").remove();}

        $.get($(this).attr("href"), {'ajax_request':true}, function(data) {
            $('<div id="add_user_dialog"></div>')
            .prepend(data)
            .find("#fieldset_add_user_footer").hide() //showing the "Go" and "Create User" buttons together will confuse the user
            .end()
            .find("form[name=usersForm]").append('<input type="hidden" name="ajax_request" value="true" />')
            .end()
            .dialog({
                title: PMA_messages['strAddNewUser'],
                width: 800,
                // height is a workaround for this Chrome problem:
                // http://bugs.jqueryui.com/ticket/4671
                // also it's interesting to be able to scroll this window
                height: 600,
                modal: true,
                buttons: button_options
            }); //dialog options end
            displayPasswordGenerateButton();
        }); // end $.get()

    });//end of Add New User AJAX event handler


    /**
     * Ajax event handler for 'Reload Privileges' anchor
     *
     * @see         PMA_ajaxShowMessage()
     * @see         $cfg['AjaxEnable'] 
     * @memberOf    jQuery
     * @name        reload_privileges_click
     */
    $("#reload_privileges_anchor.ajax").live("click", function(event) {
        event.preventDefault();

        PMA_ajaxShowMessage(PMA_messages['strReloadingPrivileges']);

        $.get($(this).attr("href"), {'ajax_request': true}, function(data) {
            if(data.success == true) {
                PMA_ajaxShowMessage(data.message);
            }
            else {
                PMA_ajaxShowMessage(data.error);
            }
        }); //end $.get()

    }); //end of Reload Privileges Ajax event handler

    /**
     * AJAX handler for 'Revoke User'
     *
     * @see         PMA_ajaxShowMessage()
     * @see         $cfg['AjaxEnable'] 
     * @memberOf    jQuery
     * @name        revoke_user_click
     */
    $("#fieldset_delete_user_footer #buttonGo.ajax").live('click', function(event) {
        event.preventDefault();

        PMA_ajaxShowMessage(PMA_messages['strRemovingSelectedUsers']);

        $form = $("#usersForm");
        
        $.post($form.attr('action'), $form.serialize() + "&delete=" + $(this).attr('value') + "&ajax_request=true", function(data) {
            if(data.success == true) {
                PMA_ajaxShowMessage(data.message);

                //Remove the revoked user from the users list
                $form.find("input:checkbox:checked").parents("tr").slideUp("medium", function() {
                    var this_user_initial = $(this).find('input:checkbox').val().charAt(0).toUpperCase();
                    $(this).remove();

                    //If this is the last user with this_user_initial, remove the link from #initials_table
                    if($("#tableuserrights").find('input:checkbox[value^=' + this_user_initial + ']').length == 0) {
                        $("#initials_table").find('td > a:contains(' + this_user_initial + ')').parent('td').html(this_user_initial);
                    }

                    //Re-check the classes of each row
                    $form
                    .find('tbody').find('tr:odd')
                    .removeClass('even').addClass('odd')
                    .end()
                    .find('tr:even')
                    .removeClass('odd').addClass('even');
                })
            }
            else {
                PMA_ajaxShowMessage(data.error);
            }
        }) // end $.post()
    }) // end Revoke User

    /**
     * AJAX handler for 'Edit User'
     *
     * @see         PMA_ajaxShowMessage()
     *
     */

    /**
     * Step 1: Load Edit User Dialog
     * @memberOf    jQuery
     * @name        edit_user_click
     * @see         $cfg['AjaxEnable'] 
     */
    $(".edit_user_anchor.ajax").live('click', function(event) {
        /** @lends jQuery */
        event.preventDefault();

        PMA_ajaxShowMessage();

        $(this).parents('tr').addClass('current_row');

        /**
         * @var button_options  Object containing options for jQueryUI dialog buttons
         */
        var button_options = {};
        button_options[PMA_messages['strCancel']] = function() {$(this).dialog("close").remove();}

        $.get($(this).attr('href'), {'ajax_request':true, 'edit_user_dialog': true}, function(data) {
            $('<div id="edit_user_dialog"></div>')
            .append(data)
            .dialog({
                width: 900,
                height: 600,
                buttons: button_options
            }); //dialog options end
            displayPasswordGenerateButton();
        }) // end $.get()
    })

    /**
     * Step 2: Submit the Edit User Dialog
     * 
     * @see         PMA_ajaxShowMessage()
     * @see         $cfg['AjaxEnable'] 
     * @memberOf    jQuery
     * @name        edit_user_submit
     */
    $("#edit_user_dialog").find("form").live('submit', function(event) {
        /** @lends jQuery */
        event.preventDefault();

        PMA_ajaxShowMessage(PMA_messages['strProcessingRequest']);

        $(this).append('<input type="hidden" name="ajax_request" value="true" />');

        /**
         * @var curr_submit_name    name of the current button being submitted
         */
        var curr_submit_name = $(this).find('.tblFooters').find('input:submit').attr('name');

        /**
         * @var curr_submit_value    value of the current button being submitted
         */
        var curr_submit_value = $(this).find('.tblFooters').find('input:submit').val();

        $.post($(this).attr('action'), $(this).serialize() + '&' + curr_submit_name + '=' + curr_submit_value, function(data) {
            if(data.success == true) {

                PMA_ajaxShowMessage(data.message);
                
                //Close the jQueryUI dialog
                $("#edit_user_dialog").dialog("close").remove();

                if(data.sql_query) {
                    $("#topmenucontainer")
                    .next('div')
                    .remove()
                    .end()
                    .after(data.sql_query);
                    var notice_class = $("#topmenucontainer").next("div").find('.notice');
                    if($(notice_class).text() == '') {
                        $(notice_class).remove();
                    }
                } //Show SQL Query that was executed

                //Append new user if necessary
                if(data.new_user_string) {
                    appendNewUser(data.new_user_string, data.new_user_initial, data.new_user_initial_string);
                }

                //Change privileges if they were edited
                if(data.new_privileges) {
                    $("#usersForm")
                    .find('.current_row')
                    .find('tt')
                    .html(data.new_privileges);
                }

                $("#usersForm")
                .find('.current_row')
                .removeClass('current_row');
            }
            else {
                PMA_ajaxShowMessage(data.error);
            }
        });
    })
    //end Edit user

    /**
     * AJAX handler for 'Export Privileges'
     *
     * @see         PMA_ajaxShowMessage()
     * @see         $cfg['AjaxEnable'] 
     * @memberOf    jQuery
     * @name        export_user_click
     */
    $(".export_user_anchor.ajax").live('click', function(event) {
        /** @lends jQuery */
        event.preventDefault();

        PMA_ajaxShowMessage();

        /**
         * @var button_options  Object containing options for jQueryUI dialog buttons
         */
        var button_options = {};
        button_options[PMA_messages['strClose']] = function() {$(this).dialog("close").remove();}

        $.get($(this).attr('href'), {'ajax_request': true}, function(data) {
            $('<div id="export_dialog"></div>')
            .prepend(data)
            .dialog({
                width : 500,
                buttons: button_options
            });
        }) //end $.get
    }) //end export privileges

    /**
     * AJAX handler to Paginate the Users Table
     *
     * @see         PMA_ajaxShowMessage()
     * @see         $cfg['AjaxEnable'] 
     * @name        paginate_users_table_click
     * @memberOf    jQuery
     */
    $("#initials_table.ajax").find("a").live('click', function(event) {
        event.preventDefault();

        PMA_ajaxShowMessage();

        $.get($(this).attr('href'), {'ajax_request' : true}, function(data) {
            // This form is not on screen when first entering Privileges
            // if there are more than 50 users
            $("#usersForm").hide("medium").remove();
            $("#fieldset_add_user").hide("medium").remove();
            $("#initials_table")
             .after(data).show("medium")
             .siblings("h2").not(":first").remove();
        }) // end $.get
    })// end of the paginate users table

    /*
     * Additional confirmation dialog after clicking 
     * 'Drop the databases...'
     */
    $('#checkbox_drop_users_db').click(function() {
        $this_checkbox = $(this);
        if ($this_checkbox.is(':checked')) {
            var is_confirmed = confirm(PMA_messages['strDropDatabaseStrongWarning'] + '\n' + PMA_messages['strDoYouReally'] + ' :\nDROP DATABASE');
            if (! is_confirmed) {
                $this_checkbox.attr('checked', false);
            }
        }
    });

}, 'top.frame_content'); //end $(document).ready()

/**#@- */
