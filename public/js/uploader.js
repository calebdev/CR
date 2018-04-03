$(document).ready(function(){
    $('#input-file').change(function(){
        uploader.run();
    });
});
var uploader = {
    run: function(){
        var formData = new FormData();
        var files = $("#input-file")[0].files;
        formData.append('csvFile', files[0]);
        formData.append('delimiter', $('#delimiter').val());
        formData.append('init', $('#init').val());

        var uploadURL = 'upload'; //Upload URL
        var jqXHR = $.ajax({
            xhr: function() {
                var xhrobj = $.ajaxSettings.xhr();
                if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = (position / total * 100);
                            var percentVal = percent+'%';
                            $('#percent').text(percentVal);
                        }

                    }, false);
                }
                return xhrobj;
            },
            url: uploadURL,
            type: "POST",
            contentType:false,
            processData: false,
            cache: false,
            data: formData,
            beforeSend: function() {
                var percentVal = '0%';
            },
            success: function(data){
                var percentVal = '100%';
                $('#tabValidator').html(data);
            }
        });
    }
};