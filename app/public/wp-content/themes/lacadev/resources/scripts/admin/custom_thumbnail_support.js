// create a mutation observer to look for added 'attachments' in the media uploader
let mediaGridObserver = new MutationObserver(function (mutations) {

    // look through all mutations that just occured
    for (let i = 0; i < mutations.length; i++) {

        // look through all added nodes of this mutation
        for (let j = 0; j < mutations[i].addedNodes.length; j++) {

            //get the applicable element
            let element = mutations[i].addedNodes[j];
            
            // Ensure element is an Element node
            if (element.nodeType !== 1) continue;

            //execute only if we have a class
            if (element.className) {
                
                // find all 'attachments'
                if (element.className.indexOf('attachment') !== -1) {

                    //find attachment inner (which contains subtype info)
                    let attachmentPreview = element.querySelector('.attachment-preview');
                    if (attachmentPreview) {

                        //only run for SVG elements
                        if (attachmentPreview.className.indexOf('subtype-svg+xml') !== -1) {

                            (function (el) {
                                const formData = new FormData();
                                formData.append('action', 'mm_get_attachment_url_thumbnail');
                                formData.append('attachmentID', el.getAttribute('data-id'));

                                fetch('/wp-admin/admin-ajax.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.text())
                                .then(data => {
                                    if (data) {
                                        const img = el.querySelector('img');
                                        const filename = el.querySelector('.filename');
                                        if (img) img.src = data;
                                        if (filename) filename.textContent = 'SVG Image';
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                            })(element);
                        }
                    }
                }
            }
        }
    }
});

let attachmentPreviewObserver = new MutationObserver(function (mutations) {
    for (var i = 0; i < mutations.length; i++) {
        for (var j = 0; j < mutations[i].addedNodes.length; j++) {
            var element = mutations[i].addedNodes[j];
            if (element.nodeType !== 1) continue;

            var onAttachmentPage = false;
            if (element.classList.contains('attachment-details') || element.querySelector('.attachment-details')) {
                onAttachmentPage = true;
            }

            if (onAttachmentPage == true) {
                var urlLabel = element.querySelector('label[data-setting="url"]');
                if (urlLabel) {
                    var input = urlLabel.querySelector('input');
                    var value = input ? input.value : '';
                    var detailsImage = element.querySelector('.details-image');
                    if (detailsImage) detailsImage.src = value;
                }
            }
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    mediaGridObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

    // attachmentPreviewObserver.observe(document.body, {
    //     childList: true,
    //     subtree  : true
    // });
});
