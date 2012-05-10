rsslounge.events = {

    /**
     * additional amount of pixel for scrolling up or down
     */
    SCROLL_TOLERANCE: 20,
    
    /**
     * element with current focus
     */
    focus: null,
 

    /**
     * initialize all events
     */
    init: function() {
        rsslounge.events.header();
        rsslounge.events.feedlist();
        rsslounge.events.settings();
        rsslounge.events.images();
        rsslounge.events.messages();
    },
    
    
    /**
     * initialize the events of the feedlist
     */
    feedlist: function() {
       
        //
        // submenue top
        //
        
        // add new feed
        $('#feedsmenue .add').unbind('click').click(function() {
            rsslounge.dialogs.addEditFeed('');
        });
        
        // date filter
        $('#feedsmenue .date').unbind('click').click(function () {
            // create calendar
            rsslounge.calendar();
        
            $(this).toggleClass('active');
            $('#calendar').toggle();
            
            if($(this).hasClass('active'))
                rsslounge.settings.dateFilter = 1;
            else
                rsslounge.settings.dateFilter = 0;
            
            if(rsslounge.settings.dateEnd.length!=0)
                rsslounge.refreshList();
        });
        
        // show all feeds
        $('#feedsmenue .show').unbind('click').click(function() {
            $('#feeds-list h3 a').addClass('up');
            $('#feeds-list ul').slideDown('fast');

            // save opened categories
            rsslounge.saveOpenCategories();
        });
        
        // hide all feeds
        $('#feedsmenue .hide').unbind('click').click(function() {
            $('#feeds-list h3 a').removeClass('up');
            $('#feeds-list ul').slideUp('fast');
            
            // save opened categories
            rsslounge.saveOpenCategories();
            
        });
        
        
        //
        // categories
        //
        
        var selectCategoryEvent = function () {
            // prevent click after dragdrop
            if(rsslounge.dragged) {
                rsslounge.dragged = false;
                return;
            }
        
            // activate this category (exception: add)
            $('#feeds-list ul li').removeClass('active');
            $('#feeds-list h3').removeClass('active');
            $(this).addClass('active');
            
            // show starred
            if($(this).hasClass('starred')) {
                rsslounge.settings.selected = '';
                rsslounge.settings.starred = 1;
                $('#unread').selectmenu('value',0);
                rsslounge.settings.unread = 0;
                rsslounge.refreshList();
            }
                
            // show all
            else if($(this).hasClass('all')) {
                rsslounge.settings.selected = '';
                rsslounge.settings.starred = 0;
                rsslounge.refreshList();
            
            // show category
            } else {
                rsslounge.settings.selected = $(this).attr('id');
                rsslounge.settings.starred = 0;
                rsslounge.refreshList();
            }
        };
        
        // category click
        $('#feeds-list h3').unbind('touchend').bind('touchend', selectCategoryEvent);
        $('#feeds-list h3').unbind('click').click(selectCategoryEvent);
        
        // category mousemove: show dropdown button
        $('#feeds-list h3').unbind('mouseenter').mouseenter(function () {
            $(this).children('a').show();
        });
        
        $('#feeds-list h3').unbind('mouseleave').mouseleave(function () {
            $(this).children('a').hide();
        });
        
        // category dropdown click
        $('#feeds-list h3 a').unbind('click').click(function () {
            // prevent category click
            rsslounge.dragged = true;
            
            // hide list
            if($(this).hasClass('up')) {
                $(this).removeClass('up');
                $(this).parent('h3').next('ul').slideUp('fast');
            } else { // show list
                $(this).addClass('up');
                $(this).parent('h3').next('ul').slideDown('fast');
            }
            
            // save opened categories
            rsslounge.saveOpenCategories();
        });
        
        
        
        
        //
        // feeds
        //
        
        var selectFeedEvent = function () {
            // prevent click after dragdrop
            if(rsslounge.dragged) {
                rsslounge.dragged = false;
                return;
            }
            
            $('#feeds-list h3').removeClass('active');
            $('#feeds-list ul li').removeClass('active');
            $(this).parent('li').addClass('active');
            
            // show edit on ipad
            if($('body').hasClass('ipad') && rsslounge.settings.authenticated==true) {
                $('#feeds-list ul li .edit').hide();
                $('#feeds-list ul li .prio').show();
                $(this).parent('li').children('.edit').show();
                $(this).parent('li').children('.prio').hide();
            }
            
            rsslounge.settings.selected = $(this).parent('li').attr('id');
            rsslounge.settings.starred = 0;
            rsslounge.refreshList();
        };
        
        // feed click
        $('#feeds-list .feed').unbind('touchend').bind('touchend', selectFeedEvent);
        
        $('#feeds-list .feed').unbind('click').click(selectFeedEvent);
        
        // feed mousover
        if(rsslounge.settings.authenticated==true) {
            $('#feeds-list ul li').unbind('mouseenter').mouseenter(function () {
                $(this).children('.edit').show();
                $(this).children('.prio').hide();
            });
            
            $('#feeds-list ul li').unbind('mouseleave').mouseleave(function () {
                $(this).children('.edit').hide();
                $(this).children('.prio').show();
            });
            
            // feed edit
            $('#feeds-list .edit').unbind('click').click(function () {
                rsslounge.dialogs.addEditFeed('',$(this).parent('li').attr('id'));
            });
            
            $('#feeds-list .edit').unbind('touchend').bind('touchend',function () {
                rsslounge.dialogs.addEditFeed('',$(this).parent('li').attr('id'));
            });
        }
        
        
        
        //
        // drag n drop of feeds
        //
        if(rsslounge.settings.authenticated==true) {
            var event = function(event) {
                        rsslounge.dragged = true;
                        
                        // send new order
                        $.ajax({
                           type: 'GET',
                           url: 'feed/sort?cat='+$(this).prev('h3').attr('id')+'&'+$(this).sortable('serialize'),
                           dataType: 'json',
                           success: function(response) {
                                rsslounge.refreshCategories(response);
                           }
                        });
                    };
            
            // sortable lists
            $("ul.feeds:not(.ui-sortable)").sortable({
                'connectWith': '.feeds',
                'stop': event,
                'receive': event
            }).addTouch();
            
            // dropable categories
            $("#feeds h3:not(.add,.starred,.all)").droppable({
                drop: function(event, ui) {
                    $list = $(this).next("ul");
                    
                    ui.draggable.hide('slow', function() {
                        $(this).appendTo($list).show('slow',function() {
                            // send new order
                            $.ajax({
                               type: 'GET',
                               url: 'feed/sort?cat='+$(this).parent('ul').prev('h3').attr('id')+'&'+$(this).parent('ul').sortable('serialize'),
                               dataType: 'json',
                               success: function(response) {
                                    rsslounge.refreshCategories(response);
                               }
                            });
                        });
                    });
                },
                hoverClass: 'dropphover'
            });
        }
        
        
        //
        // search
        //
        
        
        $('#search').unbind('keydown').keydown(function(e) {
            if(e.which==13)
                $('#feeds-list .search a').click();
        });
        
        $('#feeds-list .search a').unbind('click').click(function() {
            // set search remove button
            $('div.search .search-term').html($('#search').val());
            $('div.search').show();
            
            // execute search
            rsslounge.settings.search = $('#search').val();
            rsslounge.refreshList();
        });
        
        $('div.search a').unbind('click').click(function() {
            $('div.search').hide();
            rsslounge.settings.search = '';
            rsslounge.refreshList();
        });
        
        $('#search').unbind('focusin').focusin(function(e) {
            rsslounge.events.focus = $(this);
        });
        
        $('#search').unbind('focusout').focusout(function(e) {
            rsslounge.events.focus = null;
        });        
        
        //
        // progressbar
        //
        
        $("#progressbar").progressbar({
            value: 0
        });

    },
    
    
    /**
     * initialize the events and widgets of the header
     */
    header: function() {
        
        //
        // slider
        //
        
        // remove slider (on reinitialize)
        $("#slider").remove();
        $("#prio").append('<div id="slider"></div>');
        
        // initialize slider
        if(parseInt(rsslounge.settings.priorityStart)!=parseInt(rsslounge.settings.priorityEnd)) {
            $("#prio").show();
            $("#slider").slider({
                'range': true,
                'min': parseInt(rsslounge.settings.priorityStart),
                'max': parseInt(rsslounge.settings.priorityEnd),
                'step': 1,
                'animate': false,
                'values': [parseInt(rsslounge.settings.currentPriorityStart),  parseInt(rsslounge.settings.currentPriorityEnd)],
                'change': function(event, ui) {
                    // set new priorities
                    rsslounge.settings.currentPriorityStart = ui.values[0];
                    rsslounge.settings.currentPriorityEnd = ui.values[1];
                    
                    // set feed visibility
                    rsslounge.setFeedVisibility();
                    
                    // refresh items
                    rsslounge.refreshList();
                },
                'slide': function(event, ui) {
                    $('#prio label span').html(ui.values[0] + ' - ' + ui.values[1]);
                }
            });
            
            // make slider touchable
            $('.ui-slider-handle').addTouch();
        } else
            $("#prio").hide();
        
        $('#prio label span').html(rsslounge.settings.currentPriorityStart + ' - ' + rsslounge.settings.currentPriorityEnd);
        
        // toggle main menue (on top right side)
        $('#menue-button').unbind('click').click(function () {
            if($('#menue').is(':visible'))
                $('#menue').slideUp('medium');
            else
                $('#menue').slideDown('medium');
        });
        $(document).bind('mouseup',function(event){
            $('#menue').slideUp('medium');
        });
        
        //
        // main menue options
        //
        
        // edit categories
        $('#menue .categories').unbind('click').click(function() {
            $('#menue').slideToggle('medium');
            rsslounge.dialogs.editCategories();
        });
        
        // opml import
        if($('#opml-import').length!=0)
            new AjaxUpload('#opml-import', {
                action: 'opml/import',
                responseType: 'json',
                onSubmit: function(file, extension) {
                    rsslounge.showError(lang.opml_wait, true);
                },
                onComplete: function(file, response) {
                    // error
                    if(typeof response.error != 'undefined')
                        rsslounge.showError(response.error);
                    else
                        // success: reload
                        location.reload();
                }
            });
        
        // opml export
        $('#opml-export').unbind('click').click(function() {
            window.open('opml/export');
        });
        
        // settings
        $('#menue .settings').unbind('click').click(function() {
            rsslounge.dialogs.editSettings();
        });
        
        // errors
        $('#errormessages').unbind('click').click(function() {
            rsslounge.dialogs.showErrors();
        });
        
        // about
        $('#menue .about').unbind('click').click(function() {
            rsslounge.dialogs.showAbout();
        });
        
        // logout
        $('#menue .logout').attr('href',document.location+'?logout=1');
        
        // logout
        var url = document.location+'?login=1';
        if(document.location.href.indexOf('?')!=-1)
            url = document.location+'&login=1';
        $('#menue .login').attr('href',url);
    },
    

    /**
     * initialize the events and widgets of the top bar
     */
    settings: function() {    
        // mark all button
        $('#markall').click(function () {
            $.ajax({
                type: "POST",
                url: "item/markall",
                data: { 'items': rsslounge.getVisibleItems({mark:true}) },
                dataType: 'json',
                success: function(response){
                    // unfocus element
                    $('#markall').blur();
                    
                    // load next category or feed if no more unread items in selection
                    if(rsslounge.settings.unread==1) {
                        // no more unread items: select all
                        if(response.next == 0) {
                            rsslounge.showAllItems();
                            
                        // next unread category or feed
                        } else {
                            // next unread cat
                            if(response.next.substr(0,4)=='cat_') {
                                $('#'+response.next).click();
                            
                            // next unread feed
                            } else {
                                $('#'+response.next+' .feed').click();
                            }
                            
                        }
                        
                        return;
                    }
                    
                    rsslounge.refreshList();
                }
            });
            
        });
        
        // unstarr all button
        $('#unstarrall').click(function () {
            $.ajax({
                type: "POST",
                url: "item/unstarrall",
                data: { 'items': rsslounge.getVisibleItems({unstarr:true}) },
                dataType: 'json',
                success: function(response){
                    // unfocus element
                    $('#unstarrall').blur();
                    
                    // refresh only on starred filter
                    if(rsslounge.settings.starred==1)
                        rsslounge.refreshList();
                }
            });
        });
        
        // sort field
        $('#sort').selectmenu({width:150, menuWidth:150});
        $('#unread').selectmenu({width:130, menuWidth:130});
        $('#view').selectmenu({width:170, menuWidth:170});
        
        $(window).bind("resize", function() {
            $('.ui-selectmenu-menu').css({'left':0});
        });
        
        // select field events
        
        // sort
        $('#sort').change(function () {
            rsslounge.settings.sort = $(this).val();
            rsslounge.refreshList();
        });
        
        // unread
        $('#unread').change(function () {
            rsslounge.settings.unread = $(this).val();
            rsslounge.refreshList();
        });
        
        // view
        $('#view').change(function () {
            rsslounge.settings.view = $(this).val();
            rsslounge.setFeedVisibility();
            rsslounge.refreshList();
        });
    },
    
    
    /**
     * initialize the events for the image list
     */
    images: function() {
        // select image on click
        $('#images div').unbind('mouseenter').mouseenter(function() {
            $('#images div.selected, #messages li.selected').removeClass('selected');
            $(this).addClass('selected');
        });
    
        // mark image as read
        $('.mark-image').unbind('click').click(function () {
            // clone settings
            var settings = jQuery.extend(true, {}, rsslounge.settings);
            settings.view = 'multimedia';
            settings.id = $(this).parent('div').attr('id').substr(5);
            settings.items = $('#images').children().length;
            
            var img = $(this);
                            
            // mark image as read
            $.ajax({
            type: "POST",
            url: "item/mark",
            data: settings,
            dataType: 'json',
            success: function(response){
                    
                    // error
                    if(typeof response.error != 'undefined')
                        rsslounge.showError(response.error);
                    
                    // success
                    else {
                    
                        // hide and show on unread filter
                        if(rsslounge.settings.unread==1) {

                            // select next item
                            if(img.parent('div').hasClass('selected'))
                                rsslounge.events.shortcuts_next({
                                    'open_next': false,
                                    'close_current': false,
                                    'down': true
                                });
                        
                            // remove marked image
                            img.parent('div').remove();
                            if($('#images').children().length == 1)
                                $('#images').remove();
                
                            // insert given image
                            if(typeof response.multimedia != 'undefined') {
                                var more = $('#images div.more');
                                if(more.length>0) // more visible or not
                                    more.before(response.multimedia);
                                else
                                    $('#images hr').before(response.multimedia);
                            }
                            
                            // reset events
                            rsslounge.events.images();
                        }
                        
                        // update feed unread items
                        rsslounge.refreshFeeds(response.feeds);
                    
                        // update category unread items
                        rsslounge.refreshCategories(response.categories);
                        
                        // refresh starred items
                        $('#feeds-list h3.starred').find('.items').html(response.starred);
                    
                        // check no more items available
                        rsslounge.checkNoItems();
                    }
                }
            });
            
            $(this).toggleClass('active');
            
        });
        
        
        // starr image
        $('.starr-image').unbind('click').click(rsslounge.starItem);
        
        
        // more button on images
        $('#images div.more').unbind('click').click(function () {
            $(this).addClass('loading');
            
            // increment offset
            if(typeof rsslounge.settings.offset == 'undefined')
                rsslounge.settings.offset = rsslounge.settings.itemsperpage;
            else
                rsslounge.settings.offset = parseInt(rsslounge.settings.offset) + parseInt(rsslounge.settings.itemsperpage);
            
            // set view (only images)
            var settings = jQuery.extend(true, {}, rsslounge.settings); // clone
            settings.view = 'multimedia';
            
            // load additional entries
            $.ajax({
                type: "POST",
                url: "item/listmore",
                data: settings,
                dataType: 'json',
                success: function(response){
                    if(typeof response.error != 'undefined')
                        $('#images div.more').before(response.error);
                    else {
                        $('#images div.more').before(response.multimedia);
                        if(response.more==false)
                            $('#images div.more').hide();
                            
                        rsslounge.events.images();
                    }
                    $('#images div.more').removeClass('loading');
                }
            });
        });
        
        // set target="_blank" for open links in new window
        rsslounge.prepareUrls('images');
    },
    
    
    /**
     * initialize the events for the message list
     */
    messages: function() {    
        // hide and show item content
        $('#messages h2:not(.opened)').unbind('click').click(function () {
            var content = $(this).parent('li').children(".content");
            content.slideToggle('medium');
            rsslounge.showImages(content);
        });
        
        $('#messages li').unbind('click').click(function () {
            $('#images div.selected, #messages li.selected').removeClass('selected');
            $(this).addClass('selected');
        });
        
        // select message on link click
        $('#messages li .link').unbind('click').click(function() {
            $('#images div.selected, #messages li.selected').removeClass('selected');
            $(this).parent('li').addClass('selected');
        });
        
        // mark a single message
        $('.mark-message').unbind('click').click(function () {
        
            // clone settings
            var settings = jQuery.extend(true, {}, rsslounge.settings);
            settings.view = 'messages';
            settings.id = $(this).parent('li').attr('id').substr(5);
            settings.items = $('#messages').children().length - 1;
            
            var message = $(this);
                
            // mark message as read
            $.ajax({
            type: "POST",
            url: "item/mark",
            data: settings,
            dataType: 'json',
            success: function(response){
                    // error
                    if(typeof response.error != 'undefined')
                        rsslounge.showError(response.error);
                    
                    // success
                    else {
                        // hide and show on unread filter
                        if(rsslounge.settings.unread==1) {
                        
                            // select next item
                            if(message.parent('li').hasClass('selected'))
                                rsslounge.events.shortcuts_next({
                                    'open_next': message.parent('li').children('.content:visible').length > 0,
                                    'close_current': false,
                                    'down': true
                                });
                        
                            // remove marked message
                            message.parent('li').remove();
                            
                            // check whether any items available
                            rsslounge.checkNoItems();
                            
                            // insert given message
                            if(typeof response.messages != 'undefined')
                                $('#messages .more').before(response.messages);
                            
                            // reset events
                            rsslounge.events.messages();
                        }
                        
                        // update feed unread items
                        rsslounge.refreshFeeds(response.feeds);
                    
                        // update category unread items
                        rsslounge.refreshCategories(response.categories);
                        
                        // refresh starred items
                        $('#feeds-list h3.starred').find('.items').html(response.starred);
                    }
                }
            });
            
            $(this).toggleClass('active');
            $(this).parent('li').toggleClass('unread');
            
        });
        
        
        // starr a single message
        $('.starr-message').unbind('click').click(rsslounge.starItem);
        
        
        // more button on bottom (messages)
        $('#messages li.more').unbind('click').click(function () {
            $(this).addClass('loading');
            
            // increment offset
            if(typeof rsslounge.settings.offset == 'undefined')
                rsslounge.settings.offset = rsslounge.settings.itemsperpage;
            else
                rsslounge.settings.offset = parseInt(rsslounge.settings.offset) + parseInt(rsslounge.settings.itemsperpage);
            
            // set view (only messages)
            var settings = jQuery.extend(true, {}, rsslounge.settings); // clone
            settings.view = 'messages';
            
            // load additional entries
            $.ajax({
                type: "POST",
                url: "item/listmore",
                data: settings,
                dataType: 'json',
                success: function(response){
                    if(typeof response.error != 'undefined')
                        rsslounge.showError(response.error);
                    else {
                        $('#messages li.more').before(response.messages);
                        if(response.more==false)
                            $('#messages li.more').hide();
                            
                        rsslounge.events.messages();
                    }
                    $('#messages li.more').removeClass('loading');
                }
            });
        });
        
        // set target="_blank" for open links in new window
        rsslounge.prepareUrls('messages');
    },
    
    
    /**
     * register shortcuts
     */
    shortcuts: function() {    
        var options = {"disable_in_input": true};
        
        // switch and open next
        shortcut.add('Space', function() {
            if(rsslounge.events.shortcuts_enabled()) {
                rsslounge.events.shortcuts_next({
                    'open_next': true,
                    'close_current': true,
                    'down': true
                });
                return false;
            }
        }, options);
        
        // switch and open prev
        shortcut.add('Shift+Space', function() {
            if(rsslounge.events.shortcuts_enabled())
                rsslounge.events.shortcuts_next({
                    'open_next': true,
                    'close_current': true,
                    'down': false
                });
        }, options);
        
        
        // switch next
        shortcut.add('n', function() {
            if(rsslounge.events.shortcuts_enabled())
                rsslounge.events.shortcuts_next({
                    'open_next': false,
                    'close_current': true,
                    'down': true
                });
        }, options);
        
        // switch prev
        shortcut.add('p', function() {
            if(rsslounge.events.shortcuts_enabled())
                rsslounge.events.shortcuts_next({
                    'open_next': false,
                    'close_current': true,
                    'down': false
                });
        }, options);
        
        // switch and open next
        shortcut.add('j', function() {
            if(rsslounge.events.shortcuts_enabled())
                rsslounge.events.shortcuts_next({
                    'open_next': true,
                    'close_current': true,
                    'down': true
                });
        }, options);
        
        // switch and open prev
        shortcut.add('k', function() {
            if(rsslounge.events.shortcuts_enabled())
                rsslounge.events.shortcuts_next({
                    'open_next': true,
                    'close_current': true,
                    'down': false
                });
        }, options);
        
        // open/close article
        openclose = function() {
            var current = $('#messages li.selected');
            if(rsslounge.events.shortcuts_enabled() && current.length!=0)
                current.children('.content').slideToggle('medium');
        };
        shortcut.add('Return', openclose, options);
        shortcut.add('o', openclose, options);
        
        // mark/unmark
        shortcut.add('m', function() {
            var current = $('#messages li.selected .mark-message, #images div.selected .mark-image');
            
            if(rsslounge.events.shortcuts_enabled())
                current.click();
        }, options);
        
        // star/unstar
        shortcut.add('s', function() {
            if(rsslounge.events.shortcuts_enabled())
                $('#messages li.selected .starr-message, #images div.selected .starr-image').click();
        }, options);
        
        // open target
        shortcut.add('v', function() {
            if(rsslounge.events.shortcuts_enabled()) {
                if(rsslounge.settings.newWindow==true)
                    window.open($('#messages li.selected .link, #images div.selected .link').attr('href'));
                else
                    document.location = $('#messages li.selected .link, #images div.selected .link').attr('href');
            }
        }, options);
        
        // mark all as read
        shortcut.add('Ctrl+m', function() {
            if(rsslounge.events.shortcuts_enabled())
                $('#markall').click();
        }, options);
        
        // unstarr all
        shortcut.add('Ctrl+s', function() {
            if(rsslounge.events.shortcuts_enabled()) {
                $('#unstarrall').click();
                return false;
            }
        }, options);
        
        // new feed
        shortcut.add('Ctrl+n', function() {
            if(rsslounge.events.shortcuts_enabled()) {
                $('.add').click();
                return false;
            }
        }, options);
    },
    
    
    /**
     * get next or previous item
     * @param params options for the selection (open_next, close_current, down)
     */
    shortcuts_next: function(params) { 
        var current = $('#images div.selected, #messages li.selected');
        
        // select next item
        if(params.down)
            var next = current.next();
        else
            var next = current.prev();
        
        if(next.length!=0) {
            // if image: only select visible images
            if(next.parent('#images').length!=0) {
                if( (next.position().top >= $('#images').height()) == true ) {
                    next = $('#messages li:first');
                }
            }
        }
        
        // last message item reached? Stop here
        if(current.parent('#messages').length!=0 && next.length==0 && params.down)
            return;
            
        // first message item reached? Switch to last image
        if(current.parent('#messages').length!=0 && next.length==0 && !params.down) {
            var image = $('#images div:last');
            var height = $('#images').length!=0 ? $('#images').height() : 0;
            while(image.length!=0 && image.position().top >= height)
                image = image.prev();
            if(image.length!=0)
                next = image;
        }
        
        // no next item? take first one
        if(next.length==0)
            var next = $('#images div:first');
        if(next.length==0)
            var next = $('#messages li:first');
        
        // more
        if(next.hasClass('more')) {
            if(!next.hasClass('loading'))
                next.click();
            return;
        }
        
        // close current item
        if(current.length!=0) {
            if(params.close_current) {
                current.removeClass('selected');
                current.children(".content").hide();
            }
        } 
        
        // select next
        next.addClass('selected');
        
        // open next
        var content = next.children('.content');
        if(params.open_next && content.length!=0) {
            rsslounge.showImages(next.children('.content'));
            content.show(0, rsslounge.events.shortcuts_autoscroll);
        } else
            rsslounge.events.shortcuts_autoscroll();
        
    },
    
    
    /**
     * autoscroll
     */
    shortcuts_autoscroll: function() {
        var next = $('#images div.selected, #messages li.selected');
        
        // scroll: get content size
        var contentsize = next.height()+rsslounge.events.SCROLL_TOLERANCE;
        
        var css = new Array(
            'padding-top',
            'padding-bottom',
            'border-top',
            'border-bottom',
            'margin-top',
            'margin-bottom'
        );
        
        $(css).each(function(i, item) {
            var val = parseInt(next.css(item));
            contentsize = isNaN(val) == false ? contentsize+val : contentsize;
        });
        
        // scroll down
        var fold = $(window).height() + $(window).scrollTop();
        if(fold <= next.offset().top+contentsize)
            if(contentsize>$(window).height())
                $(window).scrollTop(next.offset().top);
            else
                $(window).scrollTop($(window).scrollTop()+contentsize);
        
        // scroll up
        var top = $(window).scrollTop();
        if(top >= next.offset().top)
            $(window).scrollTop(next.offset().top);

    },
    
    
    /**
     * returns whether shortcuts are active or not
     */
    shortcuts_enabled: function() {
        return $.prompt.getCurrentState().length==0 && rsslounge.events.focus==null;
    }
};