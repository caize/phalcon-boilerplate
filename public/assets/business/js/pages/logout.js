$(function(){
    var $logoutLink = $('#logoutLink');
    
    $logoutLink.click(function(){

        $.ajax({
            url: "/security/logout",
            method: "GET",
            success: function(result){
                window.location.href = '/';
            },
            error: function(){
                window.location.href = '/';
            }
        });

        return false;
    });
});