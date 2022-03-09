jQuery.noConflict();
jQuery(function($){
    $('#export').click(function(e){
    e.preventDefault()
    $j("#form-export").attr('action', startUrl)
    $j("#form-key").attr('value', formKey)
    $j("#form-export").submit()
    })
})