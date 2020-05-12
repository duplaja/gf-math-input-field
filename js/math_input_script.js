// placeholder for javascript
var MQ = MathQuill.getInterface(2);

function togglebuttons(id) {
    keyboard = jQuery('#keyboard_'+id).toggle('slow');
}

jQuery(document).on('gform_post_render', function() {
    console.log('it ran');
    jQuery( 'm' ).each(function() {
        MQ.StaticMath(jQuery( this )[0]);
    });

});

          