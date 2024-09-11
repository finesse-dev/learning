jQuery(document).ready(function ($) {
    var mediaUploader;
    var removedContent = [];

    $('#upload_images_button').click(function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Choose Images',
            button: {
                text: 'Choose Images'
            },
            multiple: true
        });

        mediaUploader.on('select', function () {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var imageIds = [];

            attachments.forEach(function (attachment) {
                imageIds.push(attachment.id);
            });

            appendImagesToContainer(imageIds);
        });

        mediaUploader.open();
    });

    function appendImagesToContainer(imageIds) {
        var container = $('#custom_content_container');
        imageIds.forEach(function (imageId) {
            if (imageId !== 0) {
                var imageUrl = wp.media.attachment(imageId).attributes.url;
                if (imageUrl) {
                    container.append('<div class="content-item image-container"><img src="' + imageUrl + '" alt="Custom Image" style="max-width: 100%;"><button class="remove-image" data-id="' + imageId + '">Remove</button></div>');
                    updateContentData('image', imageId);
                }
            }
        });
    }

    $('#add_text_button').click(function () {
        var index = $('#custom_content_container .text-container').length;
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_wp_editor',
                index: index
            },
            success: function (response) {
                $('#custom_content_container').append(response);
                updateContentData('text', index);
            }
        });
    });

    $(document).on('click', '.remove-image', function () {
        var imageIdToRemove = $(this).data('id');
        $(this).closest('.image-container').remove();
        removeContentData('image', imageIdToRemove);
    });

    $(document).on('click', '.remove-text', function () {
        var index = $(this).closest('.text-container').index('.text-container');
        $(this).closest('.text-container').remove();
        removeContentData('text', index);
    });

    function removeContentData(type, idOrIndex) {
        removedContent.push({ type: type, id: idOrIndex });
        $('#removed_content').val(JSON.stringify(removedContent));

        var contentData = $('#custom_content_data').val();
        if (contentData) {
            contentData = JSON.parse(contentData);
            contentData = contentData.filter(function (item) {
                return !(item.type === type && item.id === idOrIndex);
            });
            $('#custom_content_data').val(JSON.stringify(contentData));
        }
    }

    function updateContentData(type, idOrIndex) {
        var contentData = $('#custom_content_data').val() ? JSON.parse($('#custom_content_data').val()) : [];
        contentData.push({ type: type, id: idOrIndex });
        $('#custom_content_data').val(JSON.stringify(contentData));
    }
});
