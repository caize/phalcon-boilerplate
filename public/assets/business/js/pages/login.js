$(function(){
    var $loginForm = $('#loginForm');
    var $loginButton = $('#loginButton');
    var $errorMessage = $('#errorMessage');
    
    var $errorMessage = $('#errorMessage');
    var $successMessage = $('#successMessage');

    var $errorMessageF = $('#errorMessageF');
    var $successMessageF = $('#successMessageF');

    var $errorMessageS = $('#errorMessageS');
    var $successMessageS = $('#successMessageS');

    var $forgotForm = $('#forgotForm');
    var $forgotView = $('#forgotView');
    var $loginTry = $('#loginTry');
    var $forgotButton = $('#forgotButton');    

    var $signupForm = $('#signupForm');
    var $signupButton= $('#signupButton');
    var $signupCancelButton= $('#signupCancelButton');

    var $newAccountButton = $('#newAccountButton');
    var $loginTry = $('#loginTry');
    var $forgotButton = $('#forgotButton');    

    
    
    
    $forgotForm.hide();
    $signupForm.hide();

    $loginButton.click(function(){
        var _this = this;
        $(_this).attr('disabled', true);

        $.ajax({
            url: "/security/login",
            data: $loginForm.serialize(),
            method: "POST",
            success: function(result){
                
                if(result.pzt != undefined
                    && result.pzt.response != undefined
                    && result.pzt.response.success){

                    $(_this).attr('disabled', false);
                    window.location.href = '/dashboard';
                }
                else{
                    $errorMessage.html('Email o password inv&aacute;lidos.');
                    $errorMessage.show('slow');
                    window.setTimeout(function(){
                        $errorMessage.hide('slow');
                    }, 3*1000);

                    $(_this).attr('disabled', false);
                }                
            },
            error: function(){
                $errorMessage.html('Error de conexi&oacute;n');
                $errorMessage.show('slow');
                window.setTimeout(function(){
                    $errorMessage.hide('slow');
                }, 3*1000);

                $(_this).attr('disabled', false);
            }
        });

        return false;    
    });

    $signupButton.click(function(){
        var _this = this;
        $(_this).prop('disabled', true);
        $.ajax({
            url: "/security/signup",
            data: $signupForm.serialize(),
            method: "POST",
            success: function(result){
                $(_this).attr('disabled', false);
                $signupForm.find('input').removeClass('fieldError');

                if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.response != undefined
                    && result.pzt.response.success != undefined
                    && result.pzt.response.success)
                {
                    $signupCancelButton.click();

                    $successMessage.html('Su cuenta ha sido creada');
                    $successMessage.show('slow');

                    $('#inputEmail').val($('#emailID').val());
                }
                else if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.response != undefined
                    && result.pzt.response.success != undefined
                    && !result.pzt.response.success)
                {
                    var errorData = result.pzt.response.data
                    for(var i=0; i<errorData.length; i++)
                    {                        
                        $signupForm.find('[name='+errorData[i]+']').addClass('fieldError');
                    }
                    
                    $errorMessageS.html('Todos los datos son requeridos');
                    $errorMessageS.show('slow');
                    window.setTimeout(function(){
                        $errorMessageS.hide('slow');
                    }, 3*1000);  
                }
                else
                {
                    $errorMessageS.html('Error');
                    $errorMessageS.show('slow');
                    window.setTimeout(function(){
                        $errorMessageS.hide('slow');
                    }, 3*1000);                    
                }                
            },
            error: function(){
                $errorMessageS.html('Error de conexi&oacute;n');
                $errorMessageS.show('slow');
                window.setTimeout(function(){
                    $errorMessageS.hide('slow');
                }, 3*1000);

                $(_this).attr('disabled', false);
            }
        });

        return false;
    });

    $forgotButton.click(function(){
        var _this = this;
        $(_this).attr('disabled', true);

        $.ajax({
            url: "/security/forgot",
            data: $forgotForm.serialize(),
            method: "POST",
            success: function(result){
                $(_this).attr('disabled', false);

                if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.response != undefined
                    && result.pzt.response.success != undefined
                    && result.pzt.response.success)
                {
                    $successMessageF.html('Por favor revise su correo.');
                    $successMessageF.show('slow');
                    window.setTimeout(function(){
                        $successMessageF.hide();
                        $loginTry.click();

                        $('#inputEmail').val($('#forgotEmail').val());
                    }, 5*1000);
                }
                else if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.response != undefined
                    && result.pzt.response.success != undefined
                    && !result.pzt.response.success)
                {
                    $errorMessageF.html(result.pzt.response.reason);
                    $errorMessageF.show('slow');
                    window.setTimeout(function(){
                        $errorMessageF.hide('slow');
                    }, 3*1000);                    
                }
                else{
                    $errorMessageF.html('El email es inv&aacute;lido.');
                    $errorMessageF.show('slow');
                    window.setTimeout(function(){
                        $errorMessageF.hide('slow');
                    }, 3*1000);
                }                
            },
            error: function(){
                $errorMessageF.html('Error de conexi&oacute;n');
                $errorMessageF.show('slow');
                window.setTimeout(function(){
                    $errorMessageF.hide('slow');
                }, 3*1000);

                $(_this).attr('disabled', false);
            }
        });

        return false;    
    });

    $newAccountButton.click(function(){
        $loginForm.hide();
        $forgotForm.hide();
        $signupForm.show('drop', {}, 500);

        return false;
    });

    $signupCancelButton.click(function(){
        $signupForm.hide();
        $forgotForm.hide();
        $loginForm.show('drop', {}, 500);

        return false;
    }); 

    $forgotView.click(function(){
        $signupForm.hide();
        $loginForm.hide();
        $forgotForm.show('drop', {}, 500);

        return false;
    });

    $loginTry.click(function(){
        $signupForm.hide();
        $forgotForm.hide();
        $loginForm.show('drop', {}, 500);

        return false;
    });    
});