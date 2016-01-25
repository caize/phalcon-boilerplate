//http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

$(function(){
    var $resetButton = $('#resetButton');
    var $passwordID = $('#passwordID');
    var $passwordCheckID = $('#passwordCheckID');

    var $successMessage = $('#successMessage');
    var $errorMessage = $('#errorMessage');
    
    $resetButton.click(function(){
        var _this = this;
        $(_this).attr('disabled', true);

        $.ajax({
            url: "/security/reset",
            data: "t=" + getParameterByName('t') + "&password=" + $passwordID.val(),
            method: "POST",
            success: function(result){
                $(_this).attr('disabled', false);

                if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.meta != undefined
                    && result.pzt.meta.response_type != undefined
                    && result.pzt.meta.response_type == 'success')
                {
                    $passwordID.val('');
                    $passwordCheckID.val('');

                    $successMessage.html(result.pzt.response.message);
                    $successMessage.show('slow');
                    window.setTimeout(function(){
                        window.location.href = '/';
                    }, 5*1000);
                }
                else if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.meta != undefined
                    && result.pzt.meta.response_type != undefined
                    && result.pzt.meta.response_type != 'success')
                {
                    $errorMessage.html(result.pzt.response.message);
                    $errorMessage.show('slow');
                    window.setTimeout(function(){
                        $errorMessage.hide('slow');
                    }, 3*1000);                    
                }
                else{
                    $errorMessage.html('Error');
                    $errorMessage.show('slow');
                    window.setTimeout(function(){
                        $errorMessage.hide('slow');
                    }, 3*1000);
                }                
            },
            error: function(){
                $(_this).attr('disabled', false);

                $errorMessage.html('Error');
                $errorMessage.show('slow');
                window.setTimeout(function(){
                    $errorMessage.hide('slow');
                }, 3*1000);
            }
        });

        return false;
    });
});