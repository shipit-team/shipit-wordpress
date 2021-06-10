jQuery(document).ready(function() {
  jQuery('#woocommerce_shipit_all_communes').change(function() {
    if(this.checked){
      jQuery("#woocommerce_shipit_communes> option").prop("selected","selected");
      jQuery("#woocommerce_shipit_communes").trigger("change");
    } else {
      jQuery('#woocommerce_shipit_communes').val(null).trigger('change');
    }
  });

  jQuery('#woocommerce_shipit_all_free_communes').change(function() {
    if(this.checked){
      jQuery("#woocommerce_shipit_free_communes> option").prop("selected","selected");
      jQuery("#woocommerce_shipit_free_communes").trigger("change");
    } else {
      jQuery('#woocommerce_shipit_free_communes').val(null).trigger('change');
    }
  });
});
