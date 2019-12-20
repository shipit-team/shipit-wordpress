jQuery( document ).ready(function() {
    jQuery('label[for="billing_state"]').text('Comunas').append('&nbsp; <abbr class="required" title="obligatorio">*</abbr>');
    function explode(){
    jQuery('label[for="billing_state"]').text('Comunas').append('&nbsp; <abbr class="required" title="obligatorio">*</abbr>');
    }
    setTimeout(explode, 6000);
});
