var api = new iowdAPI();
api.setApiUrl(iowd.ajaxURL);

jQuery(document).ready(function () {

    if(iowd.iowd_optimizing == 1){
        finishOptimizing(0, 25);
    }

    jQuery(document).on("keypress", "[type=text], [type=number]", function (event) {
        event = event || window.event;
        if (event.keyCode == 13) {
            return false;
        }
    });

    jQuery(document).on("click", ".iowd-abort", function(){
        iowdAbort(this);
        return false;
    });


    // single optimizing
    jQuery(document).on('click', '.iowd-optimize', function () {
        var postId = jQuery(this).attr("data-id");
        var data = {
            action: "get_attachment_data",
            nonce_iowd: iowd.nonce,
            ID: postId
        };
        jQuery(this).find(".iowd-spinner").show();

        jQuery.post(iowd.ajaxURL, data, function () {
        }).done(function (response) {
            response = JSON.parse(response);
            if(response.status == "error"){
                var msg = response["error"];
                if(response["error"] == "limit_error"){
                    msg = "Your limitation has expired for current month.";
                } else if(response["error"] == "no_job"){
                    msg = "Something went wrong. Please try after few minutes.";
                } else{
                    msg = "Something went wrong.";
                }
                jQuery(".iowd_" + postId).html(msg);
                return false;
            }
            var data = {
                action: "optimize",
                nonce_iowd: iowd.nonce,
                iteration: 0,
                data_count: response.data_count,
                ID: postId
            };

            api.setPostData(data);
            api.doAPIRequest(iowdReportSingle);
        });
        return false;

    });

    //bulk optimizing
    jQuery("#iowd_optimizing").click(function () {
        jQuery(this).attr("disabled", "disabled");
        jQuery(".iowd-abort").css("display", "inline-block");
        jQuery(".iowd-cancel").hide();

        jQuery(".iowd_msg_div").hide();
        jQuery(".iowd-standart-mode-view").hide();
        jQuery(".iowd-help-from-media").hide();
        jQuery(".iowd_optimizing_msg").show();

        var other = jQuery("[name=other_folders]").val() ? 1 : 0;
        jQuery(".iowd-loading-bar").show();

        var data = {
            action: "get_attachment_data",
            other: other,
            bulk: 1,
            ID: jQuery("[name=ids]").val(),
            nonce_iowd: iowd.nonce,
        };
        jQuery(".iowd-spinner").show();
        jQuery.post(iowd.ajaxURL, data, function () {
        }).done(function (response) {
                response = JSON.parse(response);
                if(response.status == "error"  || response.data_count == 0){
                    window.location.href = "admin.php?page=iowd_settings";
                    return false;
                }

                var data = {
                    action: "optimize",
                    nonce_iowd: iowd.nonce,
                    iteration: 0,
                    bulk: 1,
                    data_count: response.data_count
                };

                api.setPostData(data);
                api.doAPIRequest(iowdReportBulk);

            }

        );

        return false;
    });


    jQuery(".iowd_opacity, .iowd_close").click(function () {
        jQuery(".iowd_opacity").hide();
        jQuery(".iowd_stats").hide();
        jQuery(".iowd_stats_body").html("");
    });

});

function iowdReportSingle(requestData, responseData, dataCount){

    if(responseData["response"]["status"] == "error"){
        jQuery(".iowd_" + postId).html(responseData["response"]["error"]);
        return false;
    }
    else if(responseData["response"]["status"] == "abort"){
        jQuery(".iowd_" + postId).html("Canceled");
        return false;
    }
    postId = requestData.ID;
    jQuery(".iowd-optimize[data-id=" + postId + "]").find(".iowd-spinner").hide();

    jQuery(".iowd_" + postId).html(iowd.finishUploadingSingle);
    finishOptimizing(postId, 20);

}

function iowdReportBulk(requestData, responseData, dataCount){

    if(responseData["response"]["status"] == "error"){
        window.location.href = "admin.php?page=iowd_settings";
        return false;
    }
    else if(responseData["response"]["status"] == "abort"){
        window.location.href = "admin.php?page=iowd_settings";
        return false;
    }
    var iterator = requestData.iteration + 1;
    if (iterator < dataCount) {
        var width = iterator / dataCount * 100;
        jQuery(".iowd-loading-bar-inner").animate({width: width + '%'});
    }
    else {
        jQuery(".iowd-loading-bar-inner").animate({width: '100%'});
        jQuery(".iowd-loading-bar-inner").css({width: '100%'});
        jQuery(".iowd-spinner").hide();
        jQuery(".iowd-loading-bar").hide();
        responseData.iowd_images_count_start
        jQuery(".iowd_msg_div_text").html(responseData.iowd_images_count_start + " " + iowd.finishUploadingBulk);
        jQuery(".iowd_msg_div").show();
        window.location.href = "admin.php?page=iowd_settings&iowd_optimizing=1";
    }
}

function finishOptimizing(postId, tries){

    var data = {
        action: "finish_bulk",
        nonce_iowd: iowd.nonce,
        postId: postId
    };

    jQuery.post(iowd.ajaxURL, data, function (response) {
        var response = JSON.parse(response);
        if(response["status"] == "abort"){
            window.location.href = "admin.php?page=iowd_settings";
        }
        else{
            if (response["status"] == "ok" || tries == 0) {
                if (tries == 0) {
                    var data = {
                        action: "abort",
                        nonce_iowd: iowd.nonce
                    };
                    jQuery.post(iowd.ajaxURL, data, function () {
                        if (postId == 0) {
                            window.location.href = "admin.php?page=iowd_settings";
                        }
                        else {
                            jQuery(".iowd_" + postId).html("Something went wrong");
                        }
                    });
                }
                else {
                    if (postId == 0) {
                        window.location.href = "admin.php?page=iowd_settings";
                    }

                    else {
                        jQuery(".iowd_" + postId).html(response.html);
                        jQuery(".iowd_" + postId).find(".iowd-abort").hide();
                    }
                }
            }
            else{
                setTimeout(function(){
                    finishOptimizing(postId, tries-1);
                }, 2000);
            }
        }
    });

}

function iowdAbort(obj){
    if(jQuery(obj).hasClass("iowd-aborted")){
        return false;
    }
    jQuery(obj).append(" ...");
    jQuery(obj).addClass("iowd-aborted");
    var data = {
        action: "abort",
        nonce_iowd: iowd.nonce
    };
    jQuery.post(iowd.ajaxURL, data, function () {
        window.location.href = "admin.php?page=iowd_settings";
    });
}

function iowdStatus(obj) {
    var data = {
        action: "get_stats",
        ID: jQuery(obj).attr("data-id"),
        nonce_iowd: iowd.nonce
    };
    jQuery(".iowd_opacity").show();
    jQuery(".iowd_stats").show();

    jQuery.post(iowd.ajaxURL, data, function (response) {
        var html = response ? response : "Not Found";
        jQuery(".iowd_stats_body").html(html);

    });
    return false;
}

function wdTabs(tabs, default_tab) {
    var activeClass = tabs + "_active";
    var tabsContainer = tabs + "_container";
    var tabsContainerItem = tabs + "_container_item";
    jQuery("." + tabsContainerItem).hide();
    jQuery("#" + (iowd.iowd_active_tab ? iowd.iowd_active_tab : default_tab)).show();
    jQuery("[href=#" + (iowd.iowd_active_tab ? iowd.iowd_active_tab : default_tab) + "]").addClass(activeClass);

    jQuery("." + tabs + " li a").click(function () {
        jQuery("." + tabsContainerItem).hide();
        jQuery("." + tabs + " li a").removeClass(activeClass);
        jQuery(jQuery(this).attr("href")).show();
        jQuery(this).addClass(activeClass);
        if (jQuery(this).attr("href") == "#report") {
            jQuery(".iowd_save_btn").hide();
        }
        else {
            jQuery(".iowd_save_btn").show();
        }
        jQuery("#" + activeClass).val(jQuery(this).attr("href").substr(1));
        return false;
    });
}



