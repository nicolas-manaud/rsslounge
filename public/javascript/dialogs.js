rsslounge.dialogs = {

    /**
     * mutex for send
     * prevent double post
     */
    sending: false,

    
    //
    // add and edit feeds
    //
    
    
    /**
     * opens and initialize add and edit feed dialog
     * @param id optional the id of a feed for editing it
     */
    addEditFeed: function(newfeed, id) {
    
        // load dialog
        $.ajax({
           type: 'GET',
           url: 'feed/' + (typeof id!='undefined' ? 'edit/id/'+id.substr(5) : 'add'),
           dataType: 'html',
           data: { 'newfeed': newfeed },
           success: function(text) {
                rsslounge.dialogs.showAddEditFeed(text, id);
           }
        }); 
        
    },
    
    
    /**
     * opens and initialize add and edit feed dialog
     * @param text the html text for add/edit dialog
     * @param id optional the id of a feed for editing it
     */
    showAddEditFeed: function(text, id) {
        // set buttons
        var buttons = new Object;
        buttons[lang.ok] = 1;
        buttons[lang.cancel] = 2;
        if(typeof id!='undefined')
            buttons[lang.remove] = 3;
        
        // show prompt window
        $.prompt(text, { 
                buttons: buttons,
                submit: rsslounge.dialogs.submitAddEdit,
                loaded: rsslounge.dialogs.initializeDialogAddEdit
            });
    },
    
    
    /**
     * initializes the elements in the add/edit dialog
     */
    initializeDialogAddEdit: function() {
        // reset sending mutex
        rsslounge.dialogs.sending = false;
        
        // make feed type selection as accordion
        $('#feed-source').accordion({autoHeight:false});

        // set add and sub buttons
        $('#feed-data li .prio-add').click(function() {
            if(isNaN(parseInt($('#priority').val())))
                return;
            $('#priority').val(parseInt($('#priority').val())+1);
        });
        
        $('#feed-data li .prio-sub').click(function() {
            if(isNaN(parseInt($('#priority').val())))
                return;
            $('#priority').val(parseInt($('#priority').val())-1);
        });
        
        // register handler for type selection
        $('#feed-source li').click(function() {
            $('#feed-source li').removeClass('active');
            $(this).addClass('active');
            $('#feed-data #source').val($(this).attr('id'));
            $('#feed-data .description').html(" " + $(this).attr('title'));
            $('#feed-data .description').prepend($(this).children('img').clone());
            
            // show url title
            var url = $(this).find('span').html();
            if( url.length>0 ) {
                $('#url').parent().show();
                $('#url').prev().show().html(url);
            } else {
                $('#url').parent().hide();
            }
                
        });
        
        // select current source
        if($('#feed-data #source').val()!=-1) {
            // select source
            $('#'+$('#feed-data #source').val()).click();
            
            // select source caption in accordion
            $('#feed-source').accordion('activate', $('#'+$('#feed-data #source').val()).parent('ul').prev('h3'));
        }
        
        // activate tipsy tooltip
        $('#feed-data #filter, #feed-data #favicon, #feed-data #name, #feed-data #url, #feed-data #priority').tipsy({fade: true, gravity: 'w'});
    },
    
    
    /**
     * submit new or edited feed
     *
     * @param button id which button was clicked (ok = true)
     */
    submitAddEdit: function(button){
        
        // get sending mutex or abort
        if(rsslounge.dialogs.sending==true)
            return false;
        else
            rsslounge.dialogs.sending = true;
        
        //
        // add edit
        //
        if(button==1) {
        
            $('.jqibuttons').addClass('loading');
            
            // parse form data
            var data = rsslounge.getValues('#add');
            
            // save new data via ajax
            $.post(
                'feed/save', 
                data,
                function(response, status) {
                    
                    // success
                    if(response.success==true) {
                        // append/edit feed
                        rsslounge.updateFeed(response.feed);
                        
                        // set stats at bottom
                        rsslounge.refreshStats({
                            all: response.all,
                            feeds: response.feeds
                            // unread will be updated via refreshCategories
                        });
                        
                        // refresh categories (unread items)
                        rsslounge.refreshCategories(response.categories);
                        
                        // update settings (new slider max and min)
                        rsslounge.updateSettings(response.settings);
                        
                        // disable icon caching
                        rsslounge.settings.iconcache = 'disabled';
                        
                        // refresh items
                        rsslounge.refreshList();
                        
                        // reinitialize slider
                        rsslounge.events.header();
                        
                        // set visible feeds
                        rsslounge.setFeedVisibility();
                        
                        // close window
                        $('.tipsy').remove();
                        $.prompt.close();
                    }
                    
                    // errors: show errors
                    else {
                        $('.jqibuttons').removeClass('loading');
                        rsslounge.dialogs.sending = false;
                        rsslounge.showErrors($('#feed-data'), response.errors);
                    }
                    
                },
                'json'
            );
            
            return false;
        } else 
        
        //
        // delete
        //
        if(button==3) {
            
            if(confirm(lang.really_delete_this_feed)==false) {
                rsslounge.dialogs.sending = false;
                return false;
            }
            
            $('.jqibuttons').addClass('loading');
            
            var id = $('#id').val();
            
            $.post(
                'feed/delete', 
                { 'id': id },
                function(response, status) {
                    
                    // errors: show errors
                    if(typeof response.error != 'undefined') {
                        rsslounge.showError(response.error);
                        $('.jqibuttons').removeClass('loading');
                        rsslounge.dialogs.sending = false;
                    // success
                    } else {
                    
                        // remove feed
                        $('#feed_'+id).hide('slow', function() {
                            $('#feed_'+id).remove();
                        });
                        
                        // set stats at bottom
                        rsslounge.refreshStats({
                            all: response.all,
                            feeds: response.feeds
                            // unread will be updated via refreshCategories
                        });
                        
                        // update categories
                        rsslounge.refreshCategories(response.categories);
                    
                        // update settings (new slider max and min)
                        rsslounge.updateSettings(response.settings);
                        
                        // disable icon caching
                        rsslounge.settings.iconcache = 'disabled';
                        
                        // refresh items
                        rsslounge.refreshList();
                        
                        // reinitialize slider
                        rsslounge.events.header();
                        
                        // close window
                        $('.tipsy').remove();
                        $.prompt.close();
                        
                        // refresh list
                        if(rsslounge.settings.selected=='feed_'+id)
                            $('#cat_0').click();
                    }
                    
                },
                'json'
            );
            
            return false;
        }
        
        // cancel
        $('.tipsy').remove();
        return true;
    },
    
    
    
    
    
    //
    // edit categories
    //
    
    
    
    
    /**
     * loads and opens category edit dialog
     */
    editCategories: function() {
        // load dialog
        $.ajax({
           type: 'GET',
           url: 'category',
           dataType: 'html',
           success: rsslounge.dialogs.showEditCategories
        }); 
    },
    
    
    /**
     * opens category edit dialog
     *
     * @param html the current servers html text
     */
    showEditCategories: function(html) {
        
        // set buttons
        var buttons = new Object;
        buttons[lang.ok] = true;
        buttons[lang.cancel] = false;
        
        $.prompt(html, 
        { 
            buttons: buttons,
            
            // initialize dialog
            loaded: rsslounge.dialogs.initializeDialogCategories,
            
            // submit clicked
            submit: rsslounge.dialogs.submitCategories
        });
    },
    
    
    /**
     * initializes the elements in the categories edit dialog
     */
    initializeDialogCategories: function() {
        // make list sortable
        $('#categories-list').sortable({ items: 'li:not(.add)', axis: 'y' }).disableSelection().addTouch();
        
        // edit category
        var editEvent = function() {
            var cat = $(this).prev();
            
            // add input field
            cat.before('<input type="text" value="' + cat.html() + '" />');
            cat.before('<a class="accept"><span>'+lang.accept+'</span></a>');
            
            // set accept action
            var parent = $(this).parent('li');
            var input = parent.children('input');
            var acceptLink = parent.children('.accept');
            
            var accept = function() {
                parent.children('.edit').show();
                parent.children('span:first').html(input.val()).show();
                input.remove();
                acceptLink.remove();
            };
            
            parent.children('.accept').click(accept);
            parent.children('.accept').bind('touchend',accept);
            
            parent.children('input').keypress(function(e) {
                if(e.which==13)
                    accept();
            });
            
            var cat = $(this).prev().hide();
            $(this).hide();
        };
        
        // delete category
        var deleteEvent = function() {
            if(confirm(lang.really_delete_this_category))
                $(this).parent('li').remove();
        };
        
        $("#categories-list .edit").click(editEvent);
        $("#categories-list .delete").click(deleteEvent);
        
        $("#categories-list .edit").bind('touchend', editEvent);
        $("#categories-list .delete").bind('touchend', deleteEvent);
        
        // add category
        var addEvent = function() {
            $(this).before('<li><span>'+lang.new_category+'</span> <a class="edit"><span>'+lang.edit+'</span></a> <a class="delete"><span>'+lang.remove+'</span></a> <span class="error"></span></li>');
            var parent = $(this).prev();
            parent.children('.edit').click(editEvent);
            parent.children('.delete').click(deleteEvent);
            parent.children('.edit').bind('touchend', editEvent);
            parent.children('.delete').bind('touchend', deleteEvent);
            parent.children('.edit').click();
        };
        
        $("#categories-list .add").click(addEvent);
        $("#categories-list .add").bind('touchend', addEvent);
    },
    
    
    /**
     * submit changed categories
     *
     * @param button id which button was clicked (ok = true)
     */
    submitCategories: function(button){
        // save only on ok click
        if(button) {
            // show loading gif
            $('.jqibuttons').addClass('loading');
            
            // set accept for all unaccepted cats
            $('#categories-list .accept').click();
        
            // collect new data
            var data = new Array();
            $('#categories-list li:not(.add)').each(function(item) {
                data[data.length] = $(this).attr('id');
                data[data.length] = $(this).children('span').html();
            });
            
            // save new data via ajax
            $.post(
                'category/save', 
                { 'categories': data },
                function(response, status) {
                    // success
                    if(response==true) {
                        // reload page
                        location.reload();
                    }
                    
                    // errors: show errors
                    else {
                        $('.jqibuttons').removeClass('loading');
                    
                        // delete old errors
                        $('#categories-list li .span').html('');
                        
                        var cats = $('#categories-list li');
                        
                        for(var i = 0; i < response.length; i++)
                            if(response[i].length>0)
                                $(cats[i]).children('.error').html(response[i][0]);
                    }
                    
                },
                'json'
            );
            
            // always suppress window close 
            // (will be closed after ajax without errors)
            return false;
        }
        
        // cancel button pressed
        return true;
    },
    
    
    
    
    
    //
    // edit settings
    //
    
    
    /**
     * loads and opens settings dialog
     */
    editSettings: function() {
        $.ajax({
           type: 'GET',
           url: 'settings',
           dataType: 'html',
           success: function(text) {
                   // set buttons
                var buttons = new Object;
                buttons[lang.ok] = true;
                buttons[lang.cancel] = false;
                
                // show prompt window
                $.prompt(text, { 
                    buttons: buttons,
                    loaded: function() {
                        $('#settings-data .bookmark a').attr('href', 'javascript:document.location="'+document.location+'?url="+escape(document.location)');
                        
                        // only show scroll buttons on ipad
                        if($('body').hasClass('ipad'))
                            $('#settings-nav').show();
                        
                        // enable scroll down and up
                        var scrollUpEvent = function() {
                            $('#settings-data').animate({ scrollTop: 0}, 500);
                            $('#settings-nav .settings-nav-up').hide();
                            $('#settings-nav .settings-nav-down').show();
                        }
                        //$('#settings-nav .settings-nav-up').click(scrollUpEvent);
                        $('#settings-nav .settings-nav-up').bind('touchend',scrollUpEvent);
                        
                        var scrollDownEvent = function() {
                            $('#settings-data').animate({ scrollTop: 300}, 500);
                            $('#settings-nav .settings-nav-up').show();
                            $('#settings-nav .settings-nav-down').hide();
                        }
                        //$('#settings-nav .settings-nav-down').click(scrollDownEvent);
                        $('#settings-nav .settings-nav-down').bind('touchend',scrollDownEvent);
                        
                        
                        // enable/disable login fields
                        $('#activate_login').click(function() {
                            var disabled = true;
                            if($(this).is(':checked'))
                                disabled = false;
                            $('#username, #password, #password_again').attr('disabled', disabled);
                        });
                        
                        // activate tipsy
                        $('#deleteItems').tipsy({fade: true, gravity: 'w'});
                    },
                    submit: rsslounge.dialogs.submitSettings
                });
           }
        }); 
    },
    
    
    /**
     * save new settings
     */
    submitSettings: function(button) {
        if(button) {
            $('.jqibuttons').addClass('loading');
            
            var settings = rsslounge.getValues('#settings');
            
            // save new data via ajax
            $.ajax( {
                type: 'POST',
                url: 'settings/save', 
                data: settings,
                dataType: 'json',
                success: function(response, status) {
                    // success
                    if(response==true) {
                        // reload page
                        location.reload();
                    }
                    
                    // errors: show errors
                    else {
                        $('.jqibuttons').removeClass('loading');
                        rsslounge.showErrors($('#settings'), response);
                    }
                    
                }
            });
            
            return false;
        }
        
        // cancel button pressed
        $('.tipsy').remove();        
        return true;
    },

    
    
    
    
    //
    // errormessages
    //
    
    
    /**
     * loads and opens error messages dialog
     */
    showErrors: function() {
        $.ajax({
           type: 'GET',
           url: 'errormessages',
           dataType: 'html',
           success: function(text) {
                   // set buttons
                var buttons = new Object;
                buttons[lang.close] = true;
                
                // show prompt window
                $.prompt(text, { 
                    buttons: buttons,
                    loaded: rsslounge.dialogs.showErrorsMore
                });
           }
        }); 
    },
    
    
    /**
     * show more errormessages more link
     */
    showErrorsMore: function() {
        $('#errormessages a').click(function() {
            // add loading animation
            $('.jqibuttons').addClass('loading');
            
            var offset = $('#errormessages > li').length;
            var insert = $(this).parent();
            var link = $(this);
            
            $.ajax({
               type: 'GET',
               url: 'errormessages',
               data: { 'offset': offset},
               dataType: 'html',
               success: function(response) {
                    // insert new messages
                    if($.trim(response).length!=0)
                        insert.before(response);
                    else
                        link.hide();
                    
                    // remove loading animation
                    $('.jqibuttons').removeClass('loading');
               }
            }); 
        });
    },
    
    
    //
    // about
    //
    /**
     * loads and opens error messages dialog
     */
    showAbout: function() {
        $.ajax({
           type: 'GET',
           url: 'index/about',
           dataType: 'html',
           success: function(text) {
                   // set buttons
                var buttons = new Object;
                buttons[lang.close] = true;
                
                // show prompt window
                $.prompt(text, { 
                    buttons: buttons,
                    loaded: rsslounge.dialogs.showErrorsMore
                });
           }
        }); 
    }
};