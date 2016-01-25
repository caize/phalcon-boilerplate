$(function(){
    $('.table-responsive').before('<span id="noCustomers">No existen Clientes vigentes.</span>');
    $('.table-responsive,#download,#see_map').css('display','none');

    if($containerWider.length)
    {
        //loading generic loading screen, locks screen and tells client to wait
        pztShowLoader($containerWider);
    }
    loadAjax('.customers','/customers/load','pzt_promo_id');
    loadEventOn('click','.seeMember');
     loadEventOn('click','.downloadExcel');
});