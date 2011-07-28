jQuery(function($) {

    var attachHandler = function () {
        $.each(AjaxControl, function(key, value) {
            if (value['handl'] instanceof Array) {
                $.each(AjaxControl, function(i) {
                    $(key).bind(value['type'], i);
                });
            } else {
                $(key).bind(value['type'], value['handl']);
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
        var Action = $(this).attr('action');

        if (submitUrl && $(this).attr('method') === 'post') {
            $.post(submitUrl, $(this).serialize(),
            function(data){
                    FormHelper(Name, data)
                }, "json");
        }
        return false;
    };

    //this object has to be defined near the bottom so the functions will be defined
    var AjaxControl = {
        '#menu a'                   : {type : 'click',      handl : rightPanelLink},
        '#newjob form'              : {type : 'submit',     handl : submitHandler},
        '#history .pagination a'    : {type : 'click',      handl : rightPanelLink}
    };


    $(document).ajaxSend(function(e, xhr, settings) {
        var targetUrl = e.target.activeElement.getAttribute('href');
        if (targetUrl!=null) {
            setUiIndex(e.target.activeElement.classList[0], targetUrl)
        }
    });


    $(document).ready(attachHandler);
});
