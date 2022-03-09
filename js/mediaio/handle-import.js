jQuery.noConflict();
jQuery(function ($) {
    $("#import").click(function (e) {
        e.preventDefault()
        data = getData()
        if (!data) {
            alert('Select a file!')
        } else {
            showLogsArea()
            $.ajax({
                url: validateUrl,
                type: "POST",
                contentType: false,
                processData: false,
                data: data,
                success: function (result) {
                    if (result !== "invalid format") {
                        setTimeout(startProcess, 1000)
                    }
                }
            })
        }
    })

    function showLogsArea() {
        $("#logs").css("display", "flex")
        $("#progress").empty()
        $("#details").empty()
        $("#loader").empty()
        $("#loader").append(`<img src="${loaderGifUrl}" style="float: right; width: 100%">`)
    }

    function getData() {
        var fileData = $("#file").prop("files")[0]
        if (!fileData) {
            return false
        }
        var formData = new FormData()
        formData.append("template", $("#template").val())
        formData.append("form_key", formKey)
        formData.append("file", fileData)
        formData.append("row", row)
        return formData
    }

    function startProcess() {
        $("#details").append("<p>Starting...</p>")
        function nextBatch() {
            $.ajax({
                url: batchesUrl,
                type: "POST",
                data: {
                    "form_key": formKey
                },
                success: function (result) {
                    if (result !== "end") {
                        $.ajax({
                            url: startUrl,
                            type: "POST",
                            data: {
                                "form_key": formKey
                            },
                            success: function (result) {
                                $("#loadarea").append(result)
                                setTimeout(nextBatch, 1000)
                            }
                        })
                    } else {
                        $("#loader").empty()
                    }
                }
            })
        }
        nextBatch()
    }
})