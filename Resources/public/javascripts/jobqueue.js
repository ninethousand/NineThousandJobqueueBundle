jQuery(function($) {

    var attachHandler = function () {
        $.each(AjaxControl, function(key, value) {
            if (value['handl'] instanceof Array) {
                $(key).unbind(value['type']);
                $.each(value['handl'], function(i, handl) {
                    $(key).bind(value['type'], handl);
                });
            } else {
                $(key).unbind(value['type']).bind(value['type'], value['handl']);
            }
        });
    };

    var rightPanelLink = function() {
        // get url to load from rel
        var loadUrl = $(this).attr('href');
        var loadContainer = 'div#rightPanel';
        if(loadUrl) {
            if ($(this).hasClass('newpage'))
            {
                window.open(loadUrl);
                return false;
            } else {
                $(loadContainer).load(loadUrl, attachHandler);
                return false;
            }
        }
        return false;
    };

    var submitHandler = function() {
        var submitUrl = $(this).attr('action');

        if (submitUrl && $(this).attr('method') === 'post') {
            $.post(submitUrl, $(this).serialize(), function(data) {
                $('div#rightPanel').html(data);
            });
        }
        return false;
    };
    
    var submitRecordControlForm = function() {
        var val = $(this).attr('value');
        if (document.forms[val]) {
            $("form[name=" + val + "]").trigger('submit');
        } else {
            $('div#rightPanel').load(val, attachHandler);
        }
        return false;
    }
    
    var jobDetails = function() {
        var id = $(this).attr('title');
        var loadUrl = $(this).attr('href');
        var pieces = loadUrl.split('/'); 
        var loadHistoryUrl = '/' + pieces[1] +'/history/?job=' + id;
        var row = $(this).parent().parent().next().children('td.hidden');
        if (row.css('display') != 'none') {
            row.css('display', 'none');
        } else {
            var detailsContainer = row.children(":first");
            var historyContainer = row.children(":last");
            $(detailsContainer).load(loadUrl, attachHandler);
            $(historyContainer).load(loadHistoryUrl, attachHandler);
            row.toggle('slow')
        }
        return false;
    }

    //this object has to be defined near the bottom so the functions will be defined
    var AjaxControl = {
        '.record_action input:checkbox'  : {type : 'click',      handl : submitRecordControlForm},
        '#jobs a.jobid'                  : {type : 'click',      handl : jobDetails}
    };

    $(document).ready(attachHandler);
});
