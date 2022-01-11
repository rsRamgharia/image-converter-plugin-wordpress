<div class="image-convertor flex-column">
    <div class="message">
        <span>Your file will download automatically once complete</span>
    </div>
    <div class="w-50 d-none add-more">
        <button class="btn btn-primary btn-lg rounded-0 rounded-top">Add more <i class="fas fa-angle-down"></i></button>
    </div>
    <div action="#" class="dropzone dropzone-previews dz-clickable" id="my-awesome-dropzone">
        <span class="preview"><img data-dz-thumbnail /></span>
        <div class="dz-message" data-dz-message>
            <button class="upload-btn">Choose File</button>
            <p class="text-muted mt-2">Or drop file here. Max file size 1GB</p>
        </div>
    </div>
</div>
<div class="container">
    <div class="table table-striped" class="files">
        <div id="template">
            <div class="card" id="">
                <div class="card-body">
                    <div class="file-row d-flex justify-content-between">
                        <div>
                            <p class="name" data-dz-name></p>
                        </div>
                        <div>
                            <span class="size text-secondary" data-dz-size></span>
                            <a data-dz-remove class="btn btn-link py-0 px-2 bg-light text-muted remove">
                                <i class="fas fa-close"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

  $image_converter_options = get_option( 'image_converter_option_name' ); // Array of All Options
  $api_key_0 = $image_converter_options['api_key_0']; // API Key
?>

<script>
    (function($) {

        let imageId = [];
        let myDropzone;
        var previewNode = document.querySelector('#template');
        console.log(previewNode);
        var previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        const baseUrl = 'https://api.convertio.co/';
        Dropzone.options.myAwesomeDropzone = {
            url: '#',
            previewsContainer: '.dropzone-previews',
            previewTemplate: previewTemplate,
            uploadMultiple: false,
            parallelUploads: 1,
            // addRemoveLinks: true,
            maxFiles: 1,
            init: function() {
                myDropzone = this;
                this.on('addedfile', function(file) {
                    $('.image-convertor').find('.add-more').removeClass('d-none');
                    $(file.previewTemplate).parent().addClass('p-0');
                    let reader = new FileReader();
                    reader.onload = (e) => {
                        var base64result = e.target.result.split(',')[1];
                        convertFile(base64result, 'base64').then((response) => {
                            if (response.code == 200) {
                                imageId.push(response.data.id);
                                $(file.previewTemplate).attr('id', response.data.id);
                            }
                        });
                    };
                    reader.readAsDataURL(file);
                    addStyle(file);
                });
                this.on('removedfile', function(file) {
                    imageId = imageId.filter((id) => id != file.previewTemplate.id);
                    if (!imageId.length) {
                        removeStyle(file);
                    }
                });

                $(document).on('click', '#convert_to_jpg button', function() {
                    $('.image-convertor .message').show();

                    $('#convert_to_jpg button')
                        .attr('disabled', true)
                        .html('Loading <i class="fas fa-sync fa-spin"></i>');
                    setTimeout(() => {
                        imageId.forEach(async (id) => {
                            await downloadImage(id);
                            $('#convert_to_jpg button')
                                .attr('disabled', false)
                                .html('Convert to JPG <i class="fas fa-arrow-right"></i>');
                        });
                        imageId = [];
                        setTimeout(() => {
                            $('.image-convertor .message').hide('slow');
                        }, 4000);
                        myDropzone.removeAllFiles();
                    }, 10000);
                });

                $(document).on('click', '.add-more button', function() {
                    myDropzone.hiddenFileInput.click();
                });
            },
        };

        function convertFile(blob, type) {
            return new Promise((resolve) => {
                const params = {
                    apikey: '<?php echo $api_key_0 ?>',
                    input: type,
                    file: blob,
                    filename: `${new Date().getTime()}.png`,
                    outputformat: 'jpg',
                };

                fetch(`${baseUrl}convert`, {
                        method: 'POST',
                        body: JSON.stringify(params),
                    })
                    .then((res) => {
                        return res.json();
                    })
                    .then((response) => {
                        if (response.code == 200 && response.status == 'ok') {
                            resolve(response);
                        }
                    });
            });
        }

        function removeStyle() {
            $('.dropzone').removeAttr('style').prev().addClass('d-none');
            $('#convert_to_jpg').remove();
        }

        function addStyle(file) {
            $('.dz-image-preview').next('.card-footer').remove();
            $(file.previewTemplate).parent().css({
                minHeight: 'unset',
                border: '1px solid #fefefe',
                background: '#fff',
            });
            $(file.previewTemplate).after(
                `<div class="card-footer text-end" id="convert_to_jpg"><button class="btn btn-primary">Convert to JPG <i class="fas fa-arrow-right"></i></button></div>`
            );
        }

        function downloadImage(id) {
            return new Promise((resolve) => {
                fetch(`${baseUrl}convert/${id}/dl/base64`)
                    .then((res) => {
                        return res.json();
                    })
                    .then((result) => {
                        if (result.code == 200) {
                            let a = document.createElement('a');
                            a.href = `data:image/jpeg;base64,${result.data.content}`;
                            a.download = `${new Date().getTime()}.jpg`;
                            a.click();
                        }
                    });
                resolve(true);
            });
        }
    })(jQuery);
</script>