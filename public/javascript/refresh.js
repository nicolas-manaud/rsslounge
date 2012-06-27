rsslounge.refresh = {

    /**
     * the feeds for an update run
     */
    feeds: false,
    
    
    /**
     * indicates whether icons was updated in
     * this refresh run
     */
    iconupdate: false,
    
    
    /**
     * set timeout for next feed refresh
     * @param time in ms
     */
    timeout: function(time) {
        window.setTimeout('rsslounge.refresh.run()', time*1000);
    },
    
    
    /**
     * refreshs all feed items
     */
    run: function() {
        // show progressbar
        $('#progress').show();
        
        // refresh feeds
        rsslounge.refresh.feeds = $('.feeds li');
        
        // refresh first feed
        rsslounge.refresh.feed(0);
    },
    
    
    /**
     * refreshs a feed
     */
    feed: function(index) {
        
        // finished
        if(index>=rsslounge.refresh.feeds.length)
            rsslounge.refresh.finish();
        
        // next feed
        else {
            var feed = $(rsslounge.refresh.feeds[index]);
            
            // check whether feed was still refreshed
            if(parseInt(feed.attr('alt')) > parseInt(rsslounge.settings.lastrefresh)) {
                rsslounge.refresh.feed(index+1); // refresh next feed
                return;
            }
            
            // show feed in status div
            $('#progress-feed').html(
                feed.find('.feed').html()
            );
            
            // set progressbarvalue
            $('#progressbar').progressbar('option', 'value', ((index+1)/rsslounge.refresh.feeds.length)*100);

            // send refresh
            var id = feed.attr('id').substr(5);
            $.ajax({
                type: 'GET',
                url: 'update/feed/id/'+id,
                dataType: 'json',
                success: function(response) {
                    // continue fetching?
                    if(typeof response.timeout != 'undefined') {
                        rsslounge.refresh.finish();
                        return;
                    }
                    
                    // icons updated?
                    if(typeof response.icon != 'undefined' && response.icon == true)
                        rsslounge.refresh.iconupdate = true
                    
                    rsslounge.refresh.feed(index+1); // recursion: fetch next feed
                },
                error: function(response) {
                    rsslounge.refresh.feed(index+1); // recursion: fetch next feed
                }
            });
        }
    },
    
    
    /**
     * finish refresh
     */
     finish: function() {
        // hide progress status
        $('#progress').hide();
        
        // send finished to server
        $.ajax({
            type: 'GET',
            url: 'update/finish',
            dataType: 'json',
            success: function(response) {
                // set new timeout
                rsslounge.settings.timeout = response.timeout;
                rsslounge.refresh.timeout(rsslounge.settings.timeout);
                
                // set last refresh
                rsslounge.settings.lastrefresh = response.lastrefresh;
                
                // update feed unread items
                rsslounge.refreshFeeds(response.feeds);
                
                // update category unread items
                rsslounge.refreshCategories(response.categories);
                
                // show waring when icons was updated in this run
                if(rsslounge.refresh.iconupdate) {
                    rsslounge.showError(lang.icons_updated, true);
                }
            }
        });
     
        
        
        
    }
}