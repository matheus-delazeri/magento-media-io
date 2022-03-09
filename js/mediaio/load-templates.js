var template = 'default.json'
$j = jQuery.noConflict();
function loadTemplates() {
        $j(".attr-table").css('background', 'transparent')
        $j(".attr-table").html(`<img src="${loaderGifUrl}" style="float: right; width: 100%">`)
        $j.ajax({
            url: templatesUrl,
            type: 'POST',
            data: {
                'form_key': formKey,
                'template': template,
                'path': templatePath
            },
            success: function (result) {
                $j(".templates-section").empty()
                $j(".templates-section").append(result)
                $j("#template").change(function () {
                    template = this.value
                    loadTemplates()
                })
            }
        })
}