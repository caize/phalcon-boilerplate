function pztShowLoader($jqueryContainer)
{
    var logoHeight = 0;
    if($jqueryContainer != undefined
        && $jqueryContainer.length)
    {
        var h = $(document).height() - logoHeight;
        var t = logoHeight;
        var loader = $('<div id="loaderBackground" class="pztLoader" style="top:'+t+'px;'
                + 'width:100%;height:'+h+'px;'
                + 'background-color:#000000;opacity: 0;filter: alpha(opacity=0);">'
                + '</div>');

        $jqueryContainer.append(loader);
        $('#loaderBackground').fadeTo('fast', 0.7, function(){
            var loaderIcon = $('<div class="pztLoader" style="top:250px;text-align:center;color:#ffffff;">'
                + '<img src="/assets/common/images/spinner.gif" style="max-height:150px;;max-width:150px;"><br /><br />'
                + 'Cargando los datos</div>');
            $('body').append(loaderIcon);
        });
    }    
}

function pztHideLoader()
{
    $('.pztLoader').fadeTo('fast', 0, function(){
        $('.pztLoader').remove();
    });
}