/* api class */

var iowdAPI = (function () {
    // constructor
    var api = function () {
        this._apiURL = null;
        this._triesCount = 25;
        this._triesIteration = 0;
        this._postData = null;
        this.error = {
            error_number: null,
            error_msg: null,
        };

        // set nessary params
        this.setApiUrl = function (url) {
            this._apiURL = url;
        };

        // data
        this.setPostData = function (postData) {
            this._postData = postData;
        };

        // do api request
        this.doAPIRequest = function (callback) {
            this.ajaxCall(this._postData, callback);
        };


        this.ajaxCall = function (data, callback) {

            var thisAPI = this;
            jQuery.post(this._apiURL, data, function () {
            }).done(function (response) {
                response = JSON.parse(response);
                var dataCount = data.data_count;
                
                var responseData = response["response"];
                thisAPI.analyzeResponse(callback, data, response, dataCount);
                if (responseData["status"] == "error" || responseData["status"] == "abort") {
                    return false;
                }
                if (dataCount > (data.iteration + 1)) {
                    data.iteration = data.iteration + 1;
                    thisAPI.ajaxCall(data, callback);
                }

            }).fail(function() {
                if (thisAPI._triesIteration < thisAPI._triesCount) {
                    console.log('Something went wrong. IO will retry.');
                    setTimeout(function () {
                        thisAPI.ajaxCall(data, callback);
                    }, 1000);
                    thisAPI._triesIteration++;
                } else {
                    var abortedData = {
                        action: "abort",
                        nonce_iowd: data.nonce_iowd
                    };
                    jQuery.post(thisAPI._apiURL, abortedData, function () {
                        window.location.href = "admin.php?page=iowd_settings";
                    });
                }
            });
        };

        // function for analyze response
        this.analyzeResponse = function (callback, requestData, responseData, dataCount) {
            callback(requestData, responseData, dataCount);
        };

        // function for error handling
        this.errorHandler = function (errorType) {
            switch (errorType) {
                case "missing_api_key" :
                    this.error.error_number = errorType;
                    this.error.error_msg = apiText.error_1;
                    break;
            }

        };
    };

    return api;
})();

