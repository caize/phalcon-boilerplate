$(function(){
    var $containerWider = $('.container-fluid');
    
    var $branchesDetail = $('#branchesDetail');
    var $branchHTML = $('#branchHTML');
    var $moreBranchesButton = $('#moreBranchesButton');
    var $saveChangesButton = $('#saveChangesButton');
    
    var $successMessage = $('#successMessage');
    var $errorMessage = $('#errorMessage');    

    var $logo = $('#logo');

    var $mainForm = $('#mainForm');

    var newCounter = 1;

    if($containerWider.length)
    {
        //loading generic loading screen, locks screen and tells client to wait
        pztShowLoader($containerWider);
    }
    $.ajax({
        url: "/retailer/load",
        method: "POST",
        success: function(result){
            if(result != undefined
                && result.pzt != undefined
                && result.pzt.meta != undefined
                && result.pzt.meta.response_type != undefined
                && result.pzt.meta.response_type == 'success')
            {
                if(result.pzt.response.business != undefined)
                {
                    $('#brand_name').val(result.pzt.response.business.brand_name);
                    $('#logoUrl').val(result.pzt.response.business.logo_path); //LOGO
                    //$('#brand_name').val(result.pzt.response.business.brand_name); //CATEGORIA
                    $('#description').val(result.pzt.response.business.description);
                }
                if(result.pzt.response.branches != undefined
                    && result.pzt.response.branches.length)
                {
                    for(var i=0; i<result.pzt.response.branches.length; i++)
                    {
                        var thisB = result.pzt.response.branches[i];

                        var key = 'ex_' + thisB.pzt_branch_id;
                        var tmpHtml = $branchHTML.html();
                        tmpHtml = replaceAll(tmpHtml, '???', key);

                        $branchesDetail.append(tmpHtml);
                        
                        /*$('#branchLabel'+key).html(thisB.name);*/
                        $('#name'+key).val(thisB.name);
                        $('#city'+key).val(thisB.city);
                        $('#address'+key).val(thisB.address);
                        $('#lat'+key).val(thisB.lat);
                        $('#lon'+key).val(thisB.lon);
                        $('#phone'+key).val(thisB.phone);
                        $('#oh_weekdays'+key).val(thisB.oh_weekdays);
                        $('#oh_saturdays'+key).val(thisB.oh_saturdays);
                        $('#oh_sundays'+key).val(thisB.oh_sundays);
                        $('#oh_holidays'+key).val(thisB.oh_holidays);
                      
                        handleErrorMessages(key);
                        addValidationActions(key);
                        addBranchButtonActions(key);
                    }
                }

                $mainForm.change(); //update preview
            }
            else if(result != undefined
                && result.pzt != undefined
                && result.pzt.meta != undefined
                && result.pzt.meta.response_type != undefined
                && result.pzt.meta.response_type != 'success')
            {
                //TODO: add stuff later
            }
            else
            {
                //TODO: add stuff later
            }

            pztHideLoader();
        },
        error: function(){
            pztHideLoader();
        }
    });
    
    $moreBranchesButton.click(function(){

        $('.branch-compact-view').show();
        $('.branch-full-view').hide();

        var key = 'new_'+newCounter;
        var tmpHtml = $branchHTML.html();
        tmpHtml = replaceAll(tmpHtml, '???', key);

        $branchesDetail.append(tmpHtml);

        $('#compactView'+key).show();
        $('#fullView'+key).hide();

        handleErrorMessages(key);
        addBranchButtonActions(key);
        addValidationActions(key);

        $('#controlsBranchOpen'+key).click();

        newCounter++;
    });

    $(".file-input").fileinput({
        uploadUrl: "/retailer/logo",
        uploadAsync: false,
        showUpload: false, 
        showRemove: false,
        allowedFileExtensions: ['jpg','png'],
        minFileCount: 1,
        maxFileCount: 1
    });

    $logo.on("filebatchselected", function(event, files) {
        // trigger upload method immediately after files are selected
        $logo.fileinput("upload");
    });     
    
    $logo.on('filebatchuploadsuccess', function(event, data, id, index) {
        var result = data.response;
        if(result != undefined
            && result.pzt != undefined
            && result.pzt.meta != undefined
            && result.pzt.meta.response_type != undefined
            && result.pzt.meta.response_type == 'success')
        {
            $('#pLogo').attr('src', '//' + result.pzt.response.s3);
            $('#logoUrl').val(result.pzt.response.s3);
        }
    });

    //TODO: handle error
    $logo.on('filebatchuploaderror', function(event, data, id, index) {
    });

    //handle live preview
    $mainForm.change(function(){        
        $('#pBrandName').text($('#brand_name').val());
        $('#pDescription').text($('#description').val());
        
        var tmpLogoUrl = $('#logoUrl').val();
        if(tmpLogoUrl)
        {
            $('#pLogo').attr('src', '//' + tmpLogoUrl);    
        }        

        var numBranches = $('#branchesDetail .branch-label').length;
        if(numBranches > 1)
        {
            $('#pBranchOthers div').show();
        }
        else
        {
            $('#pBranchOthers div').hide();
        }

        var $firstCV = $('#branchesDetail .branch-compact-view:first');
        var $firstFV = $('#branchesDetail .branch-full-view:first');
        if($firstCV.length && $firstFV.length)
        {
            $('.p-branch-data').show();
            $('#pBranch').text($firstCV.find('.branch-cv-name').val());
            $('#pAddress').text($firstFV.find('.branch-fv-address').val());

            var pPhone = '';
            var tmpPhone = $firstFV.find('.branch-fv-phone').val();
            if(tmpPhone.length)
            {
                pPhone = '&#9742; ' + tmpPhone;
            }
            $('#pPhone').html(pPhone);

            $('#pOpenHoursDataWee').text($firstFV.find('.branch-fv-weekdays').val());
            $('#pOpenHoursDataSat').text($firstFV.find('.branch-fv-saturdays').val());
            $('#pOpenHoursDataSun').text($firstFV.find('.branch-fv-sundays').val());
            $('#pOpenHoursDataHol').text($firstFV.find('.branch-fv-holidays').val());
        }
        else
        {
            $('.p-branch-data').hide();
        }
        
    });

    $saveChangesButton.click(function(){
        var _this = this;
        $(_this).attr('disabled', true);

        pztShowLoader($containerWider);

        $.ajax({
            url: "/retailer",
            data: $mainForm.serialize(),
            method: "POST",
            success: function(result){
                if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.meta != undefined
                    && result.pzt.meta.response_type != undefined
                    && result.pzt.meta.response_type == 'success')
                {
                    $successMessage.html(result.pzt.response.message);
                    $successMessage.fadeTo('slow', 1, function(){
                        window.setTimeout(function(){
                            $successMessage.fadeTo('slow', 0, function(){
                                $successMessage.hide();
                            });
                        }, 1000*5);
                    });

                    $mainForm.change(); //update preview
                }
                else if(result != undefined
                    && result.pzt != undefined
                    && result.pzt.meta != undefined
                    && result.pzt.meta.response_type != undefined
                    && result.pzt.meta.response_type != 'success')
                {
                    $errorMessage.html(result.pzt.response.message);
                    $errorMessage.fadeTo('slow', 1, function(){
                        window.setTimeout(function(){
                            $errorMessage.fadeTo('slow', 0, function(){
                                $errorMessage.hide();
                            });
                        }, 1000*5);
                    });
                }
                else
                {
                    //TODO: add stuff later
                }

                pztHideLoader();
            },
            error: function(){
                pztHideLoader();
            }
        });
    });
});

function handleErrorMessages(key)
{
    var nameVal = $('#name'+key).val();
    var addressVal = $('#address'+key).val();
    var cityVal = $('#city'+key).val();
    var latVal = $('#lat'+key).val();
    var lonVal = $('#lon'+key).val();

    if(nameVal != "")
    {
        $('#vNameS'+key).show();
        $('#vNameE'+key).hide();
    }
    else
    {
        $('#vNameS'+key).hide();
        $('#vNameE'+key).show();
    }

    if(addressVal != "" && cityVal != "")
    {
        $('#vAddressS'+key).show();
        $('#vAddressE'+key).hide();
    }
    else
    {
        $('#vAddressS'+key).hide();
        $('#vAddressE'+key).show();
    }

    if(latVal != "" && lonVal != "")
    {
        $('#vGeoS'+key).show();
        $('#vGeoE'+key).hide();
    }
    else
    {
        $('#vGeoS'+key).hide();
        $('#vGeoE'+key).show();
    }      
}

function addValidationActions(key)
{
    $('#name'+key+', #address'+key+', #city'+key+', #lat'+key+', #lon'+key).change(function(){
        handleErrorMessages(key);
    });
}

function addBranchButtonActions(key)
{
    $('#controlsBranchUndo'+key).hide();
    $('#controlsBranchClose'+key).hide();

    $('#mapButton'+key).click(function(){
        $canvas = $('#mapCanvas'+key);
        $canvas.addClass('map-canvas-active');

        var myLatLng = {lat: -12.047322986740829, lng: -77.04265594482422}; //default location Lima TODO: make dynamic

        var geocoder = new google.maps.Geocoder();

        // Create a map object and specify the DOM element for display.
        var map = new google.maps.Map(document.getElementById('mapCanvas'+key), {
            center: myLatLng,
            scrollwheel: false,
            zoom: 15,
            mapTypeControl: true,
            mapTypeControlOptions: {
                mapTypeIds: [
                    google.maps.MapTypeId.ROADMAP
                ]
            }            
        });

        var address = $('#address'+key).val();
        var prevMarkertObj = null;
        if($('#city'+key).val().length > 0)
        {
            address += ', ' + $('#city'+key).val();
        }
        address += ', Peru';

        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);
          }
        });

        //setting existing marker
        if($('#lat'+key).val() && $('#lon'+key).val())
        {
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng($('#lat'+key).val(), $('#lon'+key).val()),
                map: map
            });
            prevMarkertObj = marker;
        }

        google.maps.event.addListener(map, 'click', function(event) {
            //clearing prev marker
            if(prevMarkertObj != null)
            {
                prevMarkertObj.setMap(null);
                prevMarkertObj = null; //remove reference                
            }

            //creating new marker
            var marker = new google.maps.Marker({
                position: event.latLng, 
                map: map
            });

            $('#lat'+key).val(event.latLng.lat());
            $('#lon'+key).val(event.latLng.lng());

            $('#lon'+key).change(); //firing change event (not triggered if value changed programatically)

            prevMarkertObj = marker;
        });
    });

    $('#controlsBranchOpen'+key).click(function(){
        var _this = this;
        var thisKey = $(_this).attr('data-key');
        $('#fullView'+thisKey).show('blind', function(){
            $(_this).hide();
            $('#controlsBranchClose'+key).show();            
        });
    });

    $('#controlsBranchClose'+key).click(function(){
        var _this = this;
        var thisKey = $(_this).attr('data-key');
        $('#fullView'+thisKey).hide('blind', function(){
            $(_this).hide();
            $('#controlsBranchOpen'+key).show();          
        });
    });    

    $('#controlsBranchDelete'+key).click(function(){                            
        var thisKey = $(this).attr('data-key');
        $('#fullView'+thisKey).hide();

        $fc = $('#branchLabel'+thisKey).find('.form-control');
        $fc.addClass('branch-deleted');
        $fc.attr('disabled', true);

        $('#controlsBranchClose'+thisKey).hide();
        $('#controlsBranchOpen'+thisKey).hide();
        $(this).hide();

        $('#controlsBranchUndo'+thisKey).show();
    });

    $('#controlsBranchUndo'+key).click(function(){
        var thisKey = $(this).attr('data-key');
        $fc = $('#branchLabel'+thisKey).find('.form-control');
        $fc.removeClass('branch-deleted');
        $fc.attr('disabled', false);

        $(this).hide();

        $('#controlsBranchOpen'+thisKey).show();
        $('#controlsBranchDelete'+thisKey).show();
    });   
}