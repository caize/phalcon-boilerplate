$(function(){
    var $containerWider = $('.container-fluid');
    var $successMessage = $('#successMessage');
    var $errorMessage = $('#errorMessage');    
    var $saveChangesButton = $('#saveChangesButton');
    var $addMoreProducts=$('#addMoreProducts');
    var $mainForm = $('#mainForm');
    var newCounter = 1;
    var countNewProduct=0;
    
    //var productsPreview2='';
    if($containerWider.length)
    {
        //loading generic loading screen, locks screen and tells client to wait
        pztShowLoader($containerWider);
    }
    $.ajax({
        url: "/products/load",
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

                }
                if(result.pzt.response.products != undefined
                    && result.pzt.response.products.length)
                {
                    for(var i=0; i<result.pzt.response.products.length; i++)
                    {
                        var thisP = result.pzt.response.products[i];
                        AppendHtmlProducts(thisP.pzt_product_id,thisP.name,thisP.average_amount,thisP.status);
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

    function AppendHtmlProducts(pzt_product_id,ProductName,averagePrice,ProductActive)
    {
        var InputName=(pzt_product_id.length==0)? 'ProductsInsert':'ProductsUpdate';
        var index= (pzt_product_id.length==0)? countNewProduct:pzt_product_id;
        //var pzt_product_id=(pzt_product_id.length==0)? 'ProductsInsert':'ProductsUpdate';
        $('#LabelProduct').append('<input type="text" id="'+InputName+index+'" name="'+InputName+'['+index+'][name]" class="form-control ProductsInputName '+InputName+index+'" value="'+ProductName+'">');
        $('#averagePrice').append('<input type="text"  name="'+InputName+'['+index+'][average_amount]" class="form-control ProductsInputPrice '+InputName+index+'"  value="'+averagePrice+'">');
        $('#ProductActive').append('<div class="checkbox checkboxProduct"><label><input type="checkbox" name="'+InputName+'['+index+'][status]" class="form-control ProductsCheckbox '+InputName+index+'"  '+((ProductActive==1) ? 'checked' : '')+' value="'+ProductActive+'"></label></div>');
        $('#DeleteProducts').append('<div id="containerDelete" class="ProductsInputDelete" type="'+InputName+'" delete="'+InputName+index+'" value="'+index+'"><img src="/assets/business/images/delete.png" width="24" height="24"></div>');


    }

    function getInputValueByClass(NameClass,index)
    {
        $('.'+NameClass).each(function(j) {
                if (j==index)
                {
                    if (NameClass=='ProductsInputPrice' && !$(this).val().length)
                    {
                        $(this).val(0);
                    }
                    value=$(this).val();
                    return true;
                }
        });
        return value;
    }

	//handle live preview
    $mainForm.change(function(){
    	$('#pProducts1').empty();
    	$('#pProducts2').empty();
        $('#TotalAmount').empty();
        var htmlCheckbox='';
        var productsPreview1='';
        var productsPreview2='';
        var TotalAmount=0;
        var ProductName='';
        var count=0;
        $saveChangesButton.attr('disabled', false);
        $('#mainForm input[type=checkbox]').each(function (i) {
            if (getInputValueByClass('ProductsInputName',i).length)
            {
                (this.checked ? TotalAmount=TotalAmount+parseFloat(getInputValueByClass('ProductsInputPrice',i)) : '');
                htmlCheckbox='<div class="checkbox"><label><input type="checkbox" value="" '+(this.checked ? 'checked' : '')+'>'+getInputValueByClass('ProductsInputName',i)+'</label></div>';

                if (count % 2==0)
                {

                    productsPreview1=productsPreview1+htmlCheckbox;
                }
                else
                {
                    productsPreview2=productsPreview2+htmlCheckbox;
                }
                count=count+1;
            }
            else
            {
                
                $saveChangesButton.attr('disabled', true);
            }
        });
        $('#pProducts1').append(productsPreview1);
        $('#pProducts2').append(productsPreview2);
        $('#TotalAmount').append(TotalAmount.toFixed(2));


    });
    
    $addMoreProducts.click(function(){
        countNewProduct=countNewProduct+1;
        AppendHtmlProducts('','','',1);
        $mainForm.change(); //update preview
        //if (countNewProduct==1){loadEventDelete()};
        //loadEventDelete();
    });

    $( "body" ).on( "click", ".ProductsInputDelete", function() {
        $('.'+$(this).attr('delete')).remove();
        $(this).remove();
        product_id=$(this).attr('value');
        //$mainForm.change(); //update preview

        if ($(this).attr('type')=='ProductsUpdate') 
        {
            $.ajax({
            url: "/products",
            data: 'delete='+product_id,
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
        }

    });

    $saveChangesButton.click(function(){
        var _this = this;
        $(_this).attr('disabled', true);

        pztShowLoader($containerWider);

        $.ajax({
            url: "/products",
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