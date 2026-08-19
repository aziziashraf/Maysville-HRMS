/**
 * ===================================
 *    HandBook Item Editor 
 * ===================================
*/
var quill = new Quill('#handbook-editor', {
    modules: {
        toolbar: [
        [{ header: [1, 2, false] }],
        ['bold', 'italic', 'underline'],
        ['image', 'code-block']
        ]
    },
    placeholder: 'Write your content here...',
    theme: 'snow'  // or 'bubble'
});

quill.on('text-change', function () {
    var content = quill.root.innerHTML;
    document.getElementById('handbook-editor-input').value = content;
});


/**
 * ====================
 *      File Pond 
 * ====================
*/

// We want to preview images, so we register
// the Image Preview plugin, We also register 
// exif orientation (to correct mobile image
// orientation) and size validation, to prevent
// large files from being added
FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginImageExifOrientation,
    FilePondPluginFileValidateSize,
    // FilePondPluginImageEdit
);

// Select the file input and use 
// create() to turn it into a pond
window.ecommerce = FilePond.create(document.querySelector('.file-upload-multiple'));
// ecommerce.addFiles('../src/assets/img/product-1.jpg');

/**
 * =====================
 *      Blog Tags 
 * =====================
*/
// The DOM element you wish to replace with Tagify
var input = document.querySelector('.blog-tags');

// initialize Tagify on the above input node reference
new Tagify(input)


/**
 * =======================
 *      Blog Category 
 * =======================
*/
var input = document.querySelector('input[name=category]');

new Tagify(input, {
    whitelist: ["Themeforest","Admin","Dashboard","Laravel","Sale","Vue","React","Cork Admin"],
    userInput: false
})