jQuery(document).ready(function () {

    /*if(!iowdSettingsGlobal.overview_visited){
        jQuery(".toplevel_page_iowd_settings").attr("href", "admin.php?page=overview_iowd");
        jQuery(".toplevel_page_iowd_settings").next("ul").find("a.wp-first-item").attr("href", "admin.php?page=overview_iowd");
    }*/

    if (iowdSettingsGlobal.page == "iowd_settings") {

        if (iowdSettingsGlobal.from_gallery == 1) {
            removeGalleryFromOther();
            jQuery(".iowd_optimize_gallery").prop('checked', true);
            iowdCickSettings("optimize_gallery", 1);
        } else {
            iowdScan(0);
        }

        jQuery(".iowd-toggle").click(function () {
            if (jQuery(this).find("span").hasClass("iowd-toggle-open")) {
                jQuery(this).closest(".iowd-toggle-container").find(".iowd-toggle-body").slideUp();
                jQuery(this).find("span").removeClass("iowd-toggle-open");
                jQuery(this).find("span").addClass("iowd-toggle-close");
            } else {
                jQuery(this).closest(".iowd-toggle-container").find(".iowd-toggle-body").slideDown();
                jQuery(this).find("span").removeClass("iowd-toggle-close");
                jQuery(this).find("span").addClass("iowd-toggle-open");
            }

        });

        jQuery("#settings_form").tooltip();
        // quick settings
        jQuery(".iowd_quick_settings_el").change(function () {
            var value = 0;
            if (jQuery(this).is(":checked") == true) {
                value = 1;
            }
            var name = jQuery(this).attr("name");
            iowdCickSettings(name, value);

        });

        // standart settings
        jQuery(".iowd-standart-mode-view1 .iowd-standart-cell").click(function () {
            if (jQuery(this).attr("data-value") == "extreme") {
                return false;
            }
            jQuery("[name=standard_setting]").val(jQuery(this).attr("data-value"));
            jQuery("#settings_form").submit()

        });

        // how it works
        jQuery(".iowd-how-works").click(function () {
            jQuery(".iowd-popup-help").show();
        });
        jQuery(".iowd-standart-mode-view-help .iowd-standart-cell").click(function () {
            jQuery(".iowd-standart-mode-view-help .iowd-standart-cell").removeClass("iowd-standart-cell-active");
            jQuery(this).addClass("iowd-standart-cell-active");
            var mode = jQuery(this).attr("data-value");
            var src = jQuery(".iowd-optimized-img").attr("src", iowdSettingsGlobal.image_url + "/help" + mode + ".jpg");

            if (mode == "conservative") {
                jQuery(".iowd-stat-val").html("8 KB (4.65%)");
                jQuery(".iowd-percent").html("20%");
                jQuery(".iowd-help-optimized").html("749 KB");
                jQuery(".iowd-help-type-txt").html("Light reduction");
                jQuery(".iowd-help-exif").attr("src", iowdSettingsGlobal.image_url + "/plus.png");
                jQuery(".iowd-help-full").attr("src", iowdSettingsGlobal.image_url + "/plus.png");

            }
            else if (mode == "balanced") {
                jQuery(".iowd-stat-val").html("457 KB (59.31%)");
                jQuery(".iowd-percent").html("40%");
                jQuery(".iowd-help-optimized").html("300 KB");
                jQuery(".iowd-help-type-txt").html("Lossy reduction");
                jQuery(".iowd-help-exif").attr("src", iowdSettingsGlobal.image_url + "/minus.png");
                jQuery(".iowd-help-full").attr("src", iowdSettingsGlobal.image_url + "/plus.png");
            }
            else {
                jQuery(".iowd-stat-val").html("598 KB (78.93%)");
                jQuery(".iowd-percent").html("90%");
                jQuery(".iowd-help-optimized").html("159 KB");
                jQuery(".iowd-help-type-txt").html("Extreme reduction");
                jQuery(".iowd-help-exif").attr("src", iowdSettingsGlobal.image_url + "/minus.png");
                jQuery(".iowd-help-full").attr("src", iowdSettingsGlobal.image_url + "/minus.png");
            }

        });
        jQuery(".iowd-popup-help-close").click(function () {
            jQuery(".iowd-popup-help").hide();
        });


        // tabs
        wdTabs("iowd_tabs", "general");
        showOtherFolders();

        // show hide
        iowdShowHide("scheduled_optimization", ["scheduled_optimization_recurrence"], 1);
        iowdShowHideChange("scheduled_optimization", ["scheduled_optimization_recurrence"], 1);

        iowdShowHide("enable_conversion", ["jpg_to_png", "png_to_jpg", "gif_to_png", "jpg_to_webp", "png_to_webp"], 1);
        iowdShowHideChange("enable_conversion", ["jpg_to_png", "png_to_jpg", "gif_to_png", "jpg_to_webp", "png_to_webp"], 1);

        iowdShowHide("exclude_full_size", ["enable_resizing", "resize_media_images_width"], 0);
        iowdShowHideChange("exclude_full_size", ["enable_resizing", "resize_media_images_width"], 0, "enable_resizing", ["resize_media_images_width"], 1);


        iowdShowHide("enable_resizing", ["resize_media_images_width"], 1, "exclude_full_size", 0);
        iowdShowHideChange("enable_resizing", ["resize_media_images_width"], 1);

        iowdShowHide("enable_resizing_other", ["resize_other_images_width"], 1);
        iowdShowHideChange("enable_resizing_other", ["resize_other_images_width"], 1);


        iowdShowHide("keep_exif_data", ["exclude_full_size_metadata_removal"], 0);
        iowdShowHideChange("keep_exif_data", ["exclude_full_size_metadata_removal"], 0);


        //conversion
        jQuery("[name=jpg_to_png]").change(function () {
            iowdDisable("jpg_to_png", "jpg_to_webp");
        });
        jQuery("[name=jpg_to_webp]").change(function () {
            iowdDisable("jpg_to_webp", "jpg_to_png");
        });

        jQuery("[name=png_to_jpg]").change(function () {
            iowdDisable("png_to_jpg", "png_to_webp");
        });

        jQuery("[name=png_to_webp]").change(function () {
            iowdDisable("png_to_webp", "png_to_jpg");
        });

        jQuery(".iowd_msg_div_close").click(function () {
            jQuery(".iowd_msg_div").hide();
            jQuery(".iowd_msg_div_text").html("");
        });

        jQuery(".iowd-cancel").click(function () {
            var dataCancelType = jQuery(this).attr("data-cancel-type");
            if (dataCancelType == "1") {
                jQuery("[name=other_folders]").val("");
                jQuery("#settings_form").submit();
                return false;
            }
        });

        jQuery(".iowd-dir-tree-title").on('click', function (e) {
            e.stopPropagation();
            var thisElem = jQuery(e.target).closest("li");

            jQuery(".iowd_other_folders_container li").removeClass("iowd-active-folder");
            if (jQuery(thisElem).find(">ul").length > 0) {
                jQuery(thisElem).find('>ul').slideUp(500, function () {
                    jQuery(thisElem).find('>ul').remove();
                });
            } else {
                var data = {
                    action: "get_subdirs",
                    nonce_iowd: iowd.nonce,
                    dir: thisElem.attr("data-path")
                };

                jQuery.post(iowd.ajaxURL, data, function (response) {
                    jQuery(thisElem).append(response);
                    jQuery(thisElem).find('>ul').slideDown(500);
                });
                jQuery(thisElem).addClass("iowd-active-folder");
            }


            jQuery(".iowd-selected-dir").val(jQuery(thisElem).attr("data-path"));
            return false;
        });


        jQuery(document).on("click", ".iowd-show-images", function () {
            jQuery(this).closest(".iowd_other_folders_row").find(".folder-images").slideToggle(200);
        });

        jQuery(document).on("click", ".iowd_update_dirs", function () {
            var dirData = {};
            jQuery(".iowd_other_folders_row").each(function () {
                var otherFolderPath = jQuery(this).find(".iowd_other_path").attr("data-name");
                dirData[otherFolderPath] = [];
            });
            var data = {
                action: "choose_dirs",
                nonce_iowd: iowd.nonce,
                dir: JSON.stringify(dirData)
            }
            jQuery(".iowd-spinner-select").show();
            jQuery.post(iowd.ajaxURL, data, function (response) {
                response = JSON.parse(response);
                jQuery(".iowd-dir-paths").html(response["other"]);
                jQuery(".iowd-dir-gallery-paths").html(response["gallery"]);
                jQuery(".iowd-spinner-select").hide();
                save_settings();
            });
        });


        jQuery(".iowd-select-dir-btn").click(function () {
            var selectedDir = jQuery(".iowd-selected-dir").val();
            var dirData = {};
            dirData[selectedDir] = [];
            var flag = true;
            jQuery(".iowd_other_folders_row").each(function () {
                var otherFolderPath = jQuery(this).find(".iowd_other_path").attr("data-name");

                if (otherFolderPath == selectedDir) {
                    flag = false;
                    return false;
                }
            });
            if (flag === true) {
                var data = {
                    action: "choose_dirs",
                    nonce_iowd: iowd.nonce,
                    dir: JSON.stringify(dirData)
                }
                jQuery(".iowd-spinner-select").show();
                jQuery.post(iowd.ajaxURL, data, function (response) {
                    response = JSON.parse(response);
                    jQuery(".iowd-dir-paths").append(response["other"]);
                    jQuery(".iowd-dir-gallery-paths").append(response["gallery"]);
                    jQuery(".iowd-spinner-select").hide();
                    updateOtherFolders();
                });

            }
            jQuery(".iowd-popup").hide();

            return false;
        });
        // other folders remove
        jQuery(document).on("click", ".iowd_remove", function () {
            jQuery(this).closest(".iowd_other_folders_row").remove();
            updateOtherFolders(false);
        });
        jQuery(document).on("click", ".iowd_remove_img", function () {

            var imagesCount = Number(jQuery(this).closest(".iowd_other_folders_row").find(".iowd_other_img_path").length) - 1;
            if (imagesCount == 0) {
                jQuery(this).closest(".iowd_other_folders_row").remove();
            }
            else {
                jQuery(this).closest(".iowd_other_folders_row").find(".iowd-show-images").html(imagesCount + " images");
                jQuery(this).closest(".iowd_other_img_path").remove();
            }

            updateOtherFolders();
        });


        //remove from bulk
        jQuery(document).on("click", ".iowd_remove_attachment", function () {
            var ids = jQuery("[name=ids]").val() ? jQuery("[name=ids]").val().split(",") : [];
            var postId = jQuery(this).attr("data-id");
            var index = ids.indexOf(postId);
            if (index > -1) {
                ids.splice(index, 1);
            }
            jQuery(".attachment-row-" + postId).remove();
            jQuery("[name=ids]").val(ids.join());
            if (jQuery(".attachment-row").length == 0) {
                window.location.href = "upload.php?page=iowd_settings";
            }

        });


        jQuery(".iowd-popup-close, .iowd-opacity").click(function () {
            jQuery(".iowd-popup").hide();
            return false;
        });
        jQuery(".iowd-open-dir-tree-btn").click(function () {
            jQuery(".iowd-popup").show();
            return false;
        });

        // optimize thumbs
        jQuery(".wp_sizes").change(function () {

            var cid = [];
            jQuery(".wp_sizes").each(function () {
                if (jQuery(this).is(":checked") == true) {
                    cid.push(jQuery(this).val());
                }
            });
            jQuery("[name=optimize_thumbs]").val(cid.join());
        });
    }
    else if (iowdSettingsGlobal.page == "iowd_report") {
        // delete
        jQuery(document).on("click", ".iowd-clear-history-single, .iowd-clear-history-bulk", function () {
            jQuery(".iowd_reports").addClass("iowd-report-loader-class");
            jQuery(".iowd-report-loader").show();
            var postId = jQuery(this).attr("data-post-id");
            var data = {
                action: "clear_report",
                nonce_iowd: iowd.nonce,
                post_id: postId
            };
            var _this = jQuery(this);
            jQuery.post(iowd.ajaxURL, data, function (response) {
                if (postId) {
                    _this.closest(".main_tr").remove();
                }
                else {
                    jQuery(".iowd_reports_table_tbody").html("");
                }

                jQuery(".iowd_reports").removeClass("iowd-report-loader-class");
                jQuery(".iowd-report-loader").hide();
            });
        });


        //report filters
        jQuery(document).on("click", ".iowd_report_search, .iowd_report_reset, .iowd-more", function () {
            jQuery(".iowd_reports").addClass("iowd-report-loader-class");
            jQuery(".iowd-report-loader").show();

            if (jQuery(this).hasClass("iowd_report_reset")) {
                jQuery(".iowd-filter-elem").val("");
            }
            if (jQuery(this).hasClass("iowd-more")) {
                var newLimit = Number(jQuery(".iowd-more").attr("data-limit")) + 10;
            }
            else {
                var newLimit = jQuery(".iowd-more").attr("data-limit");
            }

            var data = {
                action: "filter_report",
                nonce_iowd: iowd.nonce,
                limit: newLimit
            };
            jQuery(".iowd-filter-elem").each(function () {
                var name = jQuery(this).attr("name");
                if (jQuery(this).is("input[type=radio]")) {
                    data[name] = jQuery("[name=" + name + "]:checked").val();
                }
                else if (jQuery(this).is("select")) {
                    data[name] = jQuery("[name=" + name + "] :selected").val();
                }
                else {
                    data[name] = jQuery(this).val();
                }
            });

            jQuery.post(iowd.ajaxURL, data, function (response) {
                jQuery(".iowd_reports_table .iowd_reports_table_tbody").html(response);
                jQuery(".iowd-more").attr("data-limit", newLimit);

                jQuery(".iowd_reports").removeClass("iowd-report-loader-class");
                jQuery(".iowd-report-loader").hide();
            });
        });
    }

    // update already used
    jQuery(document).on("click", ".iowd_update_alreday_used", function () {
        var data = {
            action: "update_already_used",
            nonce_iowd: iowd.nonce
        }
        jQuery(".iowd-spinner-select-already-used").show();
        jQuery.post(iowd.ajaxURL, data, function (response) {
            // response = JSON.parse(response);
            // jQuery(".iowd_already_used_cell .images_count").html(response.already_optimized);
            // var remained = jQuery(".iowd_remained_cell .images_count").html();
            // jQuery(".iowd_remained_cell .images_count").html(response.limit-response.already_optimized);
            // jQuery(".iowd-spinner-select-already-used").hide();
            window.location.href = "admin.php?page=iowd_settings";
        });
    });
    jQuery(".iowd-butn-ajax-container").closest(".iowd-main").append('<img src="' + iowdSettingsGlobal.image_url + '/spinner.gif" class="iowd-spinner iowd-loading-spinner-ajax" style="display:inline-block; vertical-align:sub;">');


});


function updateOtherFolders(html) {
    if (typeof html == "undefined") {
        html = true;
    }
    var otherFolders = {};
    var otherFolderPaths = [];
    jQuery(".iowd_other_folders_row").each(function () {
        var otherFolderPath = jQuery(this).find(".iowd_other_path").attr("data-name").trim();
        if (otherFolderPath && otherFolderPaths.indexOf(otherFolderPath) == -1) {
            var otherImages = [];
            jQuery(this).find(".iowd_other_img_path").each(function () {
                var imagePath = jQuery(this).find(".iowd_image_path").html();
                if (imagePath) {
                    otherImages.push(imagePath);
                }
            });
            otherFolders[otherFolderPath] = otherImages;
            otherFolderPaths.push(otherFolderPath);
        }

    });

    if (html === true) {
        if (Number(Object.keys(otherFolders).length) > 0) {
            var otherFoldersGallery = {};
            var otherFoldersOther = jQuery.extend({}, otherFolders);
            if (jQuery("[name=gallery_upload_dir]").length > 0) {
                otherFoldersGallery = otherFolders[jQuery("[name=gallery_upload_dir]").val()];
                delete otherFoldersOther[jQuery("[name=gallery_upload_dir]").val()];
            }

            if (jQuery(".iowd_other_save_btn").length == 0 && Number(Object.keys(otherFoldersOther).length) > 0) {
                var saveButton = '<div class="iowd_other_save_btn"><button class="iowd-btn iowd-btn-small iowd-btn-secondary">' + iowdSettingsGlobal.save_dirs_txt + '</button></div>';
                jQuery(".iowd_other_dirs_container .iowd-toggle-body").append(saveButton);

            }
            if (jQuery(".iowd_gallery_save_btn").length == 0 && Number(Object.keys(otherFoldersGallery).length) > 0) {
                var saveButton = '<div class="iowd_gallery_save_btn"><button class="iowd-btn iowd-btn-small iowd-btn-secondary">' + iowdSettingsGlobal.save_gallery_dirs_txt + '</button></div>';

                jQuery(".iowd_wd_plugins .iowd-toggle-body").append(saveButton);
            }
        }
        else {
            jQuery(".iowd_other_save_btn").remove();
            jQuery(".iowd_gallery_save_btn").remove();
            otherFolders = "";
        }
    }
    otherFolders = typeof otherFolders == "object" ? JSON.stringify(otherFolders) : otherFolders;
    jQuery("[name=other_folders]").val(otherFolders);

}


function showOtherFolders() {
    var otherFolders = jQuery("[name=other_folders]").val();
    if (otherFolders.trim()) {
        var data = {
            action: "choose_dirs",
            nonce_iowd: iowd.nonce,
            dir: otherFolders
        }

        jQuery(".iowd-spinner-select").show();
        jQuery.post(iowd.ajaxURL, data, function (response) {
            response = JSON.parse(response);
            jQuery(".iowd-dir-paths").append(response["other"]);
            jQuery(".iowd-dir-gallery-paths").append(response["gallery"]);
            jQuery(".iowd-spinner-select").hide();
            updateOtherFolders();
        });
    }


}

function save_settings() {
    updateOtherFolders();
    jQuery("#settings_form").submit();
}

function iowdShowHide(onChangeElem, showToggleElem, checkedVal, parent, parentVal) {
    var _show = true;

    if (parent != "undefined") {
        if (jQuery("[name=" + parent + "]:checked").val() != parentVal) {
            _show = false;
        }
    }

    if (jQuery("[name=" + onChangeElem + "]:checked").val() == checkedVal && _show) {
        for (var i = 0; i < showToggleElem.length; i++) {
            jQuery("[name=" + showToggleElem[i] + "]").closest(".iowd-table-row").show();
        }
    }
    else {
        for (var i = 0; i < showToggleElem.length; i++) {
            jQuery("[name=" + showToggleElem[i] + "]").closest(".iowd-table-row").hide();
        }
    }
}

function iowdShowHideChange(onChangeElem, showToggleElem, checkedVal, onChangeElemChild, showToggleElemChild, checkedValChild) {

    jQuery("[name=" + onChangeElem).change(function () {
        iowdShowHide(onChangeElem, showToggleElem, checkedVal);
        if (onChangeElemChild && showToggleElemChild && checkedValChild) {
            iowdShowHide(onChangeElemChild, showToggleElemChild, checkedValChild, onChangeElem, checkedVal);
        }
    });

}

function iowdCickSettings(name, value) {

    var data = {
        action: "quick_settings",
        nonce_iowd: iowd.nonce,
        name: name,
        value: value
    };

    jQuery.post(iowd.ajaxURL, data, function (response) {
        if (name == "optimize_gallery") {
            if (value == 1) {
                getGalleryDir();
            } else {
                removeGalleryFromOther();
                jQuery("#settings_form").submit();
            }
        }
    });
}

function removeGalleryFromOther() {
    var gelleryDir = jQuery("[name=gallery_upload_dir]").val().trim();
    var otherFolders = jQuery("[name=other_folders]").val().trim();
    if (otherFolders) {
        var otherFolders = JSON.parse(otherFolders);
        delete otherFolders[gelleryDir];
        jQuery("[name=other_folders]").val(JSON.stringify(otherFolders));
    }

}

function getGalleryDir() {
    var dirData = {};
    dirData[jQuery("[name=gallery_upload_dir]").val()] = [];
    var data = {
        action: "choose_dirs",
        nonce_iowd: iowd.nonce,
        dir: JSON.stringify(dirData)
    };

    jQuery(".iowd-spinner-select").show();
    jQuery.post(iowd.ajaxURL, data, function (response) {
        response = JSON.parse(response);
        jQuery(".iowd-dir-paths").append(response["other"]);
        jQuery(".iowd-dir-gallery-paths").append(response["gallery"]);
        jQuery(".iowd-spinner-select").hide();
        updateOtherFolders();

        var data = {
            action: "quick_settings",
            nonce_iowd: iowd.nonce,
            name: "other_folders",
            value: jQuery("[name=other_folders]").val()
        };

        jQuery.post(iowd.ajaxURL, data, function (response) {
            /*if (jQuery("[name=other_folders]").val()) {
                var otherFolders = JSON.parse(jQuery("[name=other_folders]").val().trim());
                var count = 0;
                for (var key in otherFolders) {
                    count = count + otherFolders[key].length;
                }
                if(count){
                    jQuery(".iowd-optimized-ajax-text").remove();
                }
            }*/

            iowdScan(0);
        });

    });
}


function iowdDisable(changedElem, disabledElem) {

    if (jQuery("[name=" + changedElem + "]:checked").val() == "1") {
        jQuery("#" + disabledElem + "0").attr("checked", "checked");
    }
}

function iowdScan(limit) {
    var data = {
        action: "scan",
        nonce_iowd: iowd.nonce,
        limit: limit
    };

    jQuery.post(iowd.ajaxURL, data, function (response) {
        response = JSON.parse(response);
        if (response["status"] != "done") {
            limit = limit + 2000;
            iowdScan(limit);
        } else {
            var data = {
                action: "scan_all",
                nonce_iowd: iowd.nonce,
                limit: limit
            };

            jQuery.post(iowd.ajaxURL, data, function (response) {
                response = JSON.parse(response);
                iowdLoadBlocks(response);
            });
        }

    });
}

function iowdLoadBlocks(scan) {
    jQuery(".iowd-loading-spinner-ajax").remove();
    if (scan["not_optimized_data_media"] > 0 || scan["not_optimized_data_media_sizes"] > 0 || scan["not_optimized_data_other"] > 0) {
        jQuery(".iowd-stat-ajax span b").html("There are " + scan["not_optimized_data_media"] + " full-size ( totally " + scan["not_optimized_data_media_sizes"] + " ) images from media library  and " + scan["not_optimized_data_other"] + " from other directories ready for optimize");
    }

    dataAttachments = jQuery(".iowd-butn-ajax-container").attr("data-attachments");
    if (dataAttachments == "1" || scan["not_optimized_data_media"] > 0 || scan["not_optimized_data_media_sizes"] > 0 || scan["not_optimized_data_other"] > 0) {
        jQuery(".iowd-butn-ajax-container").show();
    } else {
        if (scan["optimized_data"]) {
            jQuery(".iowd-optimized-ajax-text").html('<strong class="iowd-up-to-date">All images are optimized and up to date.</strong>');
        } else {
            jQuery(".iowd-optimized-ajax-text").html('<strong>There are no images for optimizing.</strong>');
        }
        jQuery(".iowd-optimized-ajax-text").show();
    }

    //statistics

    if (scan["not_optimized_data_media_sizes"] > 0 || scan["not_optimized_data_media"] > 0 || scan["not_optimized_data_other"] > 0) {
        ratio = "<span class='iowd_opt_data'>" + scan["optimized_data_sizes"] + "</span>/<span class='iowd_all_data'>" + scan["all_data_sizes"] + "</span>";
        width = (scan["optimized_data_sizes"] / scan["all_data_sizes"]) * 100;
        style = 'width:' + width + '%';
    } else {
        ratio = "<span class='iowd_opt_data'>" + scan["optimized_data_sizes"] + "</span>/<span class='iowd_all_data'>" + scan["optimized_data_sizes"] + "</span>";
        if (scan["optimized_data_sizes"] == 0) {
            style = 'width:0%';
        } else {
            style = 'width:100%';
        }
    }

    jQuery(".iowd-stat-ratio").html(ratio);
    jQuery(".iowd-stat-progress-bar-inner").attr("style", style);

}



