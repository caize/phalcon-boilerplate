$(function(){
    $('.table-responsive').before('<span id="noPromo">No existen Promociones vigentes.</span>');
    $('.table-responsive,#download').css('display','none');

    if($containerWider.length)
    {
        //loading generic loading screen, locks screen and tells client to wait
        pztShowLoader($containerWider);
    }
    loadAjax('.promotions','/promotions/load','pzt_promo_id');
    loadEventChange('#PromoType input:radio');
    loadEventChange('#PromoRequiresPoints input:radio');
    loadEventChange('#PromoBenefits input:radio');
    loadEventChange('#PromoQrCodeShow input:radio');
    loadEventOn('click','#PromoStatus');
    loadEventOn('click','#createPromo')
    loadEventOn('click','.editPromo');
    loadEventOn('click','.deletePromo');
    loadEventOn('change','#select_event');
    loadEventOn('click','#saveChangesButton');
    loadEventOn('click','.downloadExcel');
    loadImageAjax();
    $mainForm.validator().on('submit', function (e) {
      if (e.isDefaultPrevented()) {
        // handle the invalid form...
        console.log('error');
      } else {
        console.log('todo bien');
        if (!$('#saveChangesButton').hasClass('disabled') )
        {
            $('#saveChangesButton').attr('disabled', true);
            pztShowLoader($containerWider);
            loadAjax('.promotions','/promotions',$mainForm.serialize());
        }
      }
    })

	//handle live preview
    $mainForm.change(function(){
        var obj = { '#messagePublished':    ($('input:radio[id=published]:checked').val()==1)? messagePublish:messageNotPublish,
                    '#pPromoName':          $('#name').val(),
                    '#pPromoSalesPitch':    '"'+$('#sales_pitch').val()+'"',
                    '#pPromoDescription':   $('#description').val(),
                    '#pPromoConditions':    $('#conditions').val(),
                    '#pOpenHoursDataWee':   $('#availability_weekdays').val(),
                    '#pOpenHoursDataSat':   $('#availability_saturdays').val(),
                    '#pOpenHoursDataSun':   $('#availability_sundays').val(),
                    '#pOpenHoursDataHol':   $('#availability_holidays').val(),
                    '#PreviewQuantity'  :   $('#quantity').val()
                    };
        jQuery.each( obj, function( selector, val ) {
          $(selector).text(val);
        });
        $saveChangesButton.attr('disabled', false);
        var tmpLogoUrl = $('#ImagePathUrl').val();
        if(tmpLogoUrl)
        {
            $('#pImagePath').attr('src', '//' + tmpLogoUrl);    
        }
        if ($('#end_dt').val()){setInterval(update, 1000)};
        $('#MessageLeft').css('display',($('#quantity').val())?'block':'none');
    });
});

