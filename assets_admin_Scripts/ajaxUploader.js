jQuery(document).ready(function () {
    var MyUploadForm = {
        //target: '#output', // target element(s) to be updated with server response 
        beforeSubmit: beforeSubmit, // pre-submit callback 
        success: afterSuccess, // post-submit callback 
        uploadProgress: OnProgress, //upload progress callback 
        dataType: 'json',
        resetForm: true        // reset the form after successful submit 
    };

    jQuery('#MyUploadForm').submit(function () {
        jQuery(this).ajaxSubmit(MyUploadForm);
        // always return false to prevent standard browser submit and page navigation 
        return false;
    });

    //function after succesful file upload (when server response)
    function afterSuccess(data) {
        if (data.status) {
            jQuery('#mainFloor').attr('src', FULL_URL_PATH + '/uploads/maps/' + data.filename);
            hightlight();//make hightlight
            jQuery('#mainForm #output').html(data.msg).delay(3000).fadeOut(function () {
                jQuery('#imageName').val(data.filename).trigger("change");
                jQuery('#drawCanvas').fadeIn('slow');
            });
        } else {
            jQuery('#output').html(data.msg).delay(3000).fadeOut();
        }
        jQuery('#mainForm #submit-btn').show(); //hide submit button
        jQuery('#mainForm #loading-img').hide(); //hide submit button
        jQuery('#mainForm #progressbox').delay(1000).fadeOut(); //hide progress bar
    }

    //function to check file size before uploading.
    function beforeSubmit() {
        //check whether browser fully supports all File API
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            //check empty input filed
            if (!jQuery('#mainForm #FileInput').val()) {
                jQuery('#mainForm #output').html('Please select an image');
                return false;
            }

            var fsize = jQuery('#mainForm #FileInput')[0].files[0].size; //get file size
            var ftype = jQuery('#mainForm #FileInput')[0].files[0].type; // get file type

            //allow file types 
            switch (ftype) {
                case 'image/png':
                case 'image/jpeg':
                case 'image/pjpeg':
                    break;
                default:
                    jQuery('#mainForm #output').html('<b>' + ftype + '</b> Unsupported file type!');
                    return false;
            }

            //Allowed file size is less than 5 MB (1048576)
            if (fsize > 5242880) {
                jQuery('#mainForm #output').html('<b>' + bytesToSize(fsize) + '</b> Too big file! <br />File is too big, it should be less than 5 MB.');
                return false;
            }
            jQuery('#mainForm #submit-btn').hide(); //hide submit button
            jQuery('#mainForm #loading-img').show(); //hide submit button
            jQuery('#mainForm #output').html('');
        } else {
            //Output error to older unsupported browsers that doesn't support HTML5 File API
            jQuery('#mainForm #output').html('Please upgrade your browser, because your current browser lacks some new features we need!');
            return false;
        }
    }

    //progress bar function
    function OnProgress(event, position, total, percentComplete) {
        //Progress bar
        jQuery('#mainForm #progressbox').show();
        jQuery('#mainForm #progressbar').width(percentComplete + '%');//update progressbar percent complete
        jQuery('#mainForm #statustxt').html(percentComplete + '%'); //update status text
        if (percentComplete > 50) {
            jQuery('#mainForm #statustxt').css('color', '#000'); //change status text to white after 50%
        }
    }

    //function to format bites bit.ly/19yoIPO
    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes === 0)
            return '0 Bytes';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }

    var mySeatUpload = {
        //target: '#output', // target element(s) to be updated with server response 
        beforeSubmit: beforeSubmit2, // pre-submit callback 
        success: afterSuccess2, // post-submit callback 
        uploadProgress: OnProgress2, //upload progress callback 
        dataType: 'json',
        resetForm: true        // reset the form after successful submit 
    };

    jQuery('#mySeatUpload').submit(function () {
        jQuery(this).ajaxSubmit(mySeatUpload);
        // always return false to prevent standard browser submit and page navigation 
        return false;
    });

    //function after succesful file upload (when server response)
    function afterSuccess2(data) {
        if (data.status) {
            jQuery('#seatFloor').attr('src', FULL_URL_PATH + '/uploads/maps/' + data.filename);
            jQuery('#seatForm #output').html(data.msg).delay(3000).fadeOut(function () {
                jQuery('#mapName').val(data.filename).trigger("change");
                jQuery('#seatForm #drawCanvas').fadeIn('slow');
            });
        } else {
            jQuery('#seatForm #output').html(data.msg).delay(3000).fadeOut();
        }
        jQuery('#seatForm #submit-btn').show(); //hide submit button
        jQuery('#seatForm #loading-img').hide(); //hide submit button
        jQuery('#seatForm #progressbox').delay(1000).fadeOut(); //hide progress bar
    }

    //function to check file size before uploading.
    function beforeSubmit2() {
        //check whether browser fully supports all File API
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            //check empty input filed
            if (!jQuery('#seatForm #FileInput').val()) {
                jQuery('#seatForm #output').html('Please select an image');
                return false;
            }

            var fsize = jQuery('#seatForm #FileInput')[0].files[0].size; //get file size
            var ftype = jQuery('#seatForm #FileInput')[0].files[0].type; // get file type

            //allow file types 
            switch (ftype) {
                case 'image/png':
                case 'image/jpeg':
                case 'image/pjpeg':
                    break;
                default:
                    jQuery('#seatForm #output').html('<b>' + ftype + '</b> Unsupported file type!');
                    return false;
            }

            //Allowed file size is less than 5 MB (1048576)
            if (fsize > 5242880) {
                jQuery('#seatForm #output').html('<b>' + bytesToSize2(fsize) + '</b> Too big file! <br />File is too big, it should be less than 5 MB.');
                return false;
            }
            jQuery('#seatForm #submit-btn').hide(); //hide submit button
            jQuery('#seatForm #loading-img').show(); //hide submit button
            jQuery('#seatForm #output').html('');
        } else {
            //Output error to older unsupported browsers that doesn't support HTML5 File API
            jQuery('#seatForm #output').html('Please upgrade your browser, because your current browser lacks some new features we need!');
            return false;
        }
    }

    //progress bar function
    function OnProgress2(event, position, total, percentComplete) {
        //Progress bar
        jQuery('#seatForm #progressbox').show();
        jQuery('#seatForm #progressbar').width(percentComplete + '%'); //update progressbar percent complete
        jQuery('#seatForm #statustxt').html(percentComplete + '%'); //update status text
        if (percentComplete > 50) {
            jQuery('#seatForm #statustxt').css('color', '#000'); //change status text to white after 50%
        }
    }

    //function to format bites bit.ly/19yoIPO
    function bytesToSize2(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes === 0)
            return '0 Bytes';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }

});