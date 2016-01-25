$(function(){
    var $containerWider = $('.container-fluid');
    
    

    var newCounter = 1;

    if($containerWider.length)
    {
        //loading generic loading screen, locks screen and tells client to wait
        pztShowLoader($containerWider);
    }
    