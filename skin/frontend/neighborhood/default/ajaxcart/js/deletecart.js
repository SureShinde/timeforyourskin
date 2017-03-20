function setLocationAjax3(url,id,page){
    url += '&isAjax=1';
    url = url.replace("checkout/cart","ajax/index");
    if(page == 'sidebar'){
        jQuery('.peerforest-sidebar-'+id).show();
    }
    else{
        jQuery('.peerforest-cart-'+id).show();
    }
    
    try {
        jQuery.ajax( {
            url : url,
            dataType : 'json',
            success : function(data) {
                if(page == 'sidebar'){
                    jQuery('.peerforest-sidebar-'+id).hide();
                }
                else{
                    jQuery('.peerforest-cart-'+id).hide();
                }
                if(jQuery('.block-cart')){
                    jQuery('.block-cart').replaceWith(data.sidebar);
                }
                if(jQuery('.header .links')){
                    jQuery('.header .links').replaceWith(data.toplink);
                }
                if(jQuery('.peerforest-cart').length)
                    window.location.reload();
                else
                    successMessage(data.message);
            }
        });
    } catch (e) {
    }
}