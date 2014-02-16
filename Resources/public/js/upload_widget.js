(function() {

    $.fn.uploadWidget = function(options) {
        var $container = $(this).parent();
        var $button = $container.find('.upload_button');
        var $preview = $container.find('.upload_preview');
        var $error = $container.find('.upload_error');
        var $input = $container.find('input[type=hidden]');
        var $remove_button = $container.find('.upload_remove_button');
        var $progress;
        var $temp;

        var createProgressBar = function() {
            var progress = $('<div/>').addClass('upload_progress')
                .progressbar({value: 0});
            return progress;
        }

        var createImagePreview = function(file) {
            var div = $('<div/>').addClass('upload_temp')
            var image = $('<img/>');
            var preloader = new mOxie.Image();

            preloader.onload = function() {
                preloader.downsize( 250, 200 );
                image.prop( "src", preloader.getAsDataURL() );
            };

            preloader.load( file.getSource() );

            div.append(image);
            $progress = createProgressBar();
            div.append($progress);
            return div;
        }

        var createImageServerPreview = function(data) {
            var image = $('<img/>').attr('src', data.url)
                .attr('width', data.width)
                .attr('height', data.height);
            var link = $('<a/>').attr('href', data.full_url).append(image);
            link.fancybox();
            return link;
        }

        $remove_button.click(function(e) {
            e.preventDefault();
            $input.val('');
            $preview.html('');
            $(this).hide();
        })

        var uploader = new plupload.Uploader({
            runtimes : 'html5,html4',
            browse_button : $button.get(0),
            multi_selection: false,
            url : options.post_url,
            filters : {
                max_file_size : '10mb',
                mime_types: [
                    {title : "Image files", extensions : "jpg,jpeg,gif,png"},
                ]
            },
            flash_swf_url : options.flash_url,
            init: {
                FilesAdded: function(up, files) {
                    $preview.hide();
                    $temp = createImagePreview(files[0]);
                    $container.prepend($temp);
                    uploader.stop();
                    uploader.start();
                },

                UploadProgress: function(up, file) {
                    $progress.progressbar('value', file.percent);
                },

                FileUploaded: function(up, file, response) {
                    response = response.response;
                    //With HTML4 runtime for some reason the repsonse comes wrapped in a <pre> tag
                    if (response.match(/\<pre/i)) {
                        response = $(response).text();
                    }
                    var data = $.parseJSON(response)
                    $preview.html(createImageServerPreview(data)).css('height', data.height).show();
                    $temp.css({position: 'absolute'});
                    $temp.fadeOut(1000, function() {
                        $temp.remove();
                        $preview.css({'height': 'auto'})
                    });
                    $input.val(data.id);
                    $remove_button.fadeIn(500);
                },

                Error: function(up, err) {
                    $error.text(err.message);
                }
            }
        });
        uploader.init();
    };

})();