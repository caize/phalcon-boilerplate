//http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
var $image_path = $('#promo_image_path');
var $saveChangesButton = $('#saveChangesButton');
var $containerWider = $('.container-fluid');
var $successMessage = $('#successMessage');
var $errorMessage = $('#errorMessage');
var $mainForm = $('#mainForm');
var $SearchPromotions=$('#SearchPromotions');
var $SearchCustomers=$('#SearchCustomers');
var $EditPromotion=$('#EditPromotion');
var $SeeCustomer=$('#SeeCustomer');
var table='';
var new_promotion=false;
var messagePublish='';
var messageNotPublish='';
var days='';
var html='';
var labelAxisYNumber='';
var labelAxisYAmount='';
var labelAxisXNumber='';
var labelAxisXAmount='';
var hystoryInterchangedPromo='';
var historyPurchases='';
var branches='';
var branches_staff='';
var alert_checkbox_branch='';
var i=0;
var flagSearch=false;
var stringIds='';
function escapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function replaceAll(string, find, replace) {
  return string.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

function loadAjax(type, uri, data_ajax)
{
    $.ajax({
    url: uri,
    method: "POST",
    data: data_ajax ,
    success: function(result){
        if(result != undefined
            && result.pzt != undefined
            && result.pzt.meta != undefined
            && result.pzt.meta.response_type != undefined
            && result.pzt.meta.response_type == 'success')
        {
            if( result.pzt.response.promotions!= undefined && result.pzt.response.promotions.length ||
            	result.pzt.response.members!= undefined && result.pzt.response.members.length ||
                result.pzt.response.staff!= undefined && result.pzt.response.staff.length)
            {
                switch (uri) {
                    case '/promotions/load':
                        switchSelectorsDisplayHide('.table-responsive,#download','#noPromo');
                        for(var i=0; i<result.pzt.response.promotions.length; i++)
                        {
                            
                            var thisP = result.pzt.response.promotions[i];
                            var status=thisP.status;
                            html=html+  '<tr pzt_promo_id="'+thisP.pzt_promo_id+'">'+
                                            '<td>'+thisP.name+'</td>'+
                                            '<td>'+thisP.pzt_promo_id+'</td>'+
                                            '<td>'+(result.pzt.response.language[status])+'</td>'+
                                            '<td>'+
                                                '<table class=".table-condensed">'+
                                                    '<tr>'+
                                                        '<td>'+
                                                            result.pzt.response.language.total+':'+ thisP.quantity+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td>'+
                                                            result.pzt.response.language.used+':'+ thisP.used+
                                                        '</td>'+
                                                    '</tr>'+
                                                '</table>'+
                                            '</td>'+
                                            '<td>'+
                                                '<table class=".table-condensed">'+
                                                    '<tr>'+
                                                        '<td>'+
                                                            thisP.start_dt+
                                                        '</td>'+
                                                    '</tr>'+
                                                    '<tr>'+
                                                        '<td>'+
                                                            thisP.end_dt+' '+ result.pzt.response.language.end_day+
                                                        '</td>'+
                                                    '</tr>'+
                                                '</table>'+
                                            '</td>'+
                                            '<td>'+((thisP.type=='events')? result.pzt.response.language.yes:result.pzt.response.language.not)+'</td>'+
                                            '<td>'+((thisP.requires_points==1)? result.pzt.response.language.yes:result.pzt.response.language.not)+'</td>'+
                                            '<td><a href="#" class="editPromo" pzt_promo_id="'+thisP.pzt_promo_id+'">'+result.pzt.response.language.edit+'</a>|<a href="#" class="deletePromo" id="Promo'+thisP.pzt_promo_id+'" pzt_promo_id="'+thisP.pzt_promo_id+'">'+result.pzt.response.language.delete+'</a></td>'+
                                        '</tr>';
                        }
                        //console.log(html);
                        loadDataTable('#promotions',html,result.pzt.response.language.display+' _MENU_ '+ result.pzt.response.language.records_per_page,result.pzt.response.language.placeholder_search,result.pzt.response.language.zero_records,result.pzt.response.language.showing_page+' _PAGE_ '+result.pzt.response.language.of+' _PAGES_',result.pzt.response.language.info_empty,'('+result.pzt.response.language.filtered+' _MAX_ '+result.pzt.response.language.total_records+')');
                        addHtmlTable('#promotions',result.pzt.response.language.page,result.pzt.response.language.of,result.pzt.response.language.create_promo,result.pzt.response.language.actives,result.pzt.response.language.finished,result.pzt.response.language.future,result.pzt.response.language.pendient,result.pzt.response.language.all);
                        break;
                    case '/promotions':
                        switchSelectorsDisplayHide ('#EditPromotion','#SearchPromotions');
                        for(var i=0; i<result.pzt.response.promotions.length; i++)
                        {
                            var thisP = (result.pzt.response.promotions[i]==null || result.pzt.response.promotions[i]==undefined)? '':result.pzt.response.promotions[i];
                        }
                        jQuery.each( thisP, function( selector, value ) {
                            if (selector=='published' || selector=='type' || selector=='requires_points' || selector=='benefit' || selector=='restrict_multi_redemption' || selector=='qr_code_show')
                            {
                                checkRadio('input:radio[id='+selector+']',value);
                            }
                            else if (selector=='quantity_limitless')
                            {
                                checkRadio('input:checkbox[id='+selector+']',value);
                            }
                            else
                            {
                                $('#'+selector).val(value);
                            }
                        });
                        showDisplay('.qr_code','1','1');
                        showPromoType(thisP.type);
                        showPromoBenefits(thisP.requires_points);
                        generateQrCode('#pQrCode',thisP.qr_code);
                        messagePublish=result.pzt.response.language.published;
                        messageNotPublish=result.pzt.response.language.without_publish;
                        days=result.pzt.response.language.days;
                        $('.code').text(thisP.qr_code);
                        $('#ImagePathUrl').val(thisP.image_path);
                        var datetime_obj = {'#start_date':   true,
                                            '#end_date':   false,
                                            };
                        loadDateTime(datetime_obj,result.pzt.response.language.code_language,'DD/MM/YYYY HH:mm A');
                        dependDateTime('#start_date','#end_date');
                        if (thisP.pzt_promo_id==undefined)
                        {
                            $('#saveChangesButton').text(result.pzt.response.language.add_promotion);
                            new_promotion=true;
                        };
                        if (thisP.pzt_promo_id != undefined  && new_promotion==true)
                        {
                            successMessage(result.pzt.response.message);
                            $('#saveChangesButton').text(result.pzt.response.language.save_changes);
                        }
                        $mainForm.change();
                        break;
                    case '/customers/load':
                        switchSelectorsDisplayHide ('.table-responsive,#download,#see_map','#noCustomers');
                        for(var i=0; i<result.pzt.response.members.length; i++)
                        {
                            var thisP = result.pzt.response.members[i];
                            var location=(thisP.state==null && thisP.city==null)? '':thisP.state+','+thisP.city;
                            var reserved=(thisP.reserved==null)? '0':thisP.reserved;
                            var interchanged=(thisP.interchanged==null)? '0':thisP.interchanged;
                            var total_spent=(thisP.total_spent==null)? '0':thisP.total_spent;
                            html=html+  '<tr>'+
		                        '<td>'+thisP.first_name+'</td>'+
		                        '<td>'+thisP.pzt_member_id+'</td>'+
		                        '<td>'+location+'</td>'+
		                        '<td>'+reserved+'</td>'+
		                        '<td>'+interchanged+'</td>'+
		                        '<td>'+total_spent+'</td>'+
		                        '<td><a href="#" class="seeMember" reserved="'+reserved+'" interchanged="'+interchanged+'" total_spent="'+total_spent+'" pzt_member_id="'+thisP.pzt_member_id+'">'+result.pzt.response.language.see+'</a></td>'+
		                      '</tr>';
                        }
                        loadDataTable('#customers',html,result.pzt.response.language.display+' _MENU_ '+ result.pzt.response.language.records_per_page,result.pzt.response.language.placeholder_search,result.pzt.response.language.zero_records,result.pzt.response.language.showing_page+' _PAGE_ '+result.pzt.response.language.of+' _PAGES_',result.pzt.response.language.info_empty,'('+result.pzt.response.language.filtered+' _MAX_ '+result.pzt.response.language.total_records+')');
                        addHtmlTable('#customers',result.pzt.response.language.page,result.pzt.response.language.of,result.pzt.response.language.create_promo,result.pzt.response.language.actives,result.pzt.response.language.finished,result.pzt.response.language.future,result.pzt.response.language.pendient,result.pzt.response.language.all);
                    	break;
                    case '/customers':
                        switchSelectorsDisplayHide ('#SeeMember','#SearchMembers');
                        for(var i=0; i<result.pzt.response.members.length; i++)
                        {
                            var thisP = result.pzt.response.members[i];
                        }
                        jQuery.each( thisP, function( selector, value ) {
                                $('#'+selector).text((value==null)? '':value);
                        });
                        google.load('visualization', '1', {packages: ['corechart', 'line'], "callback": drawChart});
                        labelAxisYNumber=result.pzt.response.language.number_promotions;
                        labelAxisYAmount=result.pzt.response.language.amount_consumer;
                        labelAxisXNumber=result.pzt.response.language.time;
                        labelAxisXAmount=result.pzt.response.language.time;
                        hystoryInterchangedPromo=result.pzt.response.language.hystory_interchanged_promo;
                        historyPurchases=result.pzt.response.language.history_purchases;
                        break;
                    case '/staff/load':
                        switchSelectorsDisplayHide ('#SearchStaff,.table-responsive,#download','#noStaff,#editStaff');
                        for(var i=0; i<result.pzt.response.staff.length; i++)
                        {
                            var thisP = result.pzt.response.staff[i];
                            html=html+  '<tr>'+
                                '<td>'+thisP.first_name+ ' '+thisP.last_name +'</td>'+
                                '<td>'+thisP.pzt_staff_id+'</td>'+
                                '<td>'+'15/09/15 1:15 pm'+'</td>'+
                                '<td><a href="#" class="editStaff"  pzt_staff_id="'+thisP.pzt_staff_id+'">'+result.pzt.response.language.edit+'</a></td>'+
                              '</tr>';
                        }
                        loadDataTable('#staff',html,result.pzt.response.language.display+' _MENU_ '+ result.pzt.response.language.records_per_page,result.pzt.response.language.placeholder_search,result.pzt.response.language.zero_records,result.pzt.response.language.showing_page+' _PAGE_ '+result.pzt.response.language.of+' _PAGES_',result.pzt.response.language.info_empty,'('+result.pzt.response.language.filtered+' _MAX_ '+result.pzt.response.language.total_records+')');
                        addHtmlTable('#staff',result.pzt.response.language.page,result.pzt.response.language.of,result.pzt.response.language.create_account,result.pzt.response.language.actives,result.pzt.response.language.finished,result.pzt.response.language.future,result.pzt.response.language.pendient,result.pzt.response.language.all);
                        break;
                    case '/staff':
                        switchSelectorsDisplayHide ('#editStaff','#SearchStaff');
                        for(var i=0; i<result.pzt.response.staff.length; i++)
                        {
                            var thisP = result.pzt.response.staff[i];
                        }
                        jQuery.each( thisP, function( selector, value ) {
                            if (selector=='account_type' || selector=='status' )
                            {
                                checkRadio('input:radio[id='+selector+']',value);
                            }
                            else if (selector=='branches')
                            {
                                branches=value.split(',');
                                html='<div class="col-xs-12">';
                                jQuery.each(branches, function(index, branch) {
                                    i++;
                                    branch=branch.split(':')
                                    html=html+"<label class='checkbox-inline col-xs-"+((i%4==0)?"2":"3")+"'>"+
                                                "<input type='checkbox' class='StaffBranchesRdios' name='Staff[pzt_branch_id][]' value="+branch[0]+" id='branch"+branch[0]+"'>"+ branch[1]+
                                            "</label>";
                                });
                                html=html+'</div>';
                                $('#branches').html(html);
                                html='';
                            }
                            else if (selector=='branches_staff')
                            {
                                branches_staff=value.split(',');
                            }
                            else
                            {
                                $('#'+selector).val(value);
                            }
                        });
                        branches_staff.forEach(function(branch_id) {
                                    checkRadio('input:checkbox[id=branch'+branch_id+']','"'+branch_id+'"');
                                });
                        generateQrCode('#QrCode',thisP.qc_code);
                        $('#qr').text(thisP.qc_code);
                        if($('#pzt_staff_id').val()=='')
                        {
                            $('#saveChangesStaffButton').addClass('disabled');
                        }
                        if(data_ajax.substring(0,12)=='pzt_staff_id' || data_ajax=='StaffCreate')
                        {
                        }
                        else
                        {
                            successMessage(result.pzt.response.message);
                        }
                        alert_checkbox_branch=result.pzt.response.language.alert_checkbox_branch
                        break;
                    default:
                        '';
                }
            }
            else
            {
                if(data_ajax.substring(0,6)=='delete')
                {
                    var promoid = data_ajax.split('=');
                    deleteRowDataTable('#Promo'+promoid[1]);
                }
                successMessage(result.pzt.response.message);
                $mainForm.change(); //update preview
            }

        }
        else if(result != undefined
            && result.pzt != undefined
            && result.pzt.meta != undefined
            && result.pzt.meta.response_type != undefined
            && result.pzt.meta.response_type != 'success')
        {
            errorMessage(result.pzt.response.message)
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
function switchSelectorsDisplayHide (selectorsToDisplay,selectorsToHide)
{
    $(selectorsToDisplay).css('display','block');
    $(selectorsToHide).css('display','none');
}
function loadDateTime(datetime_obj,language,date_format)
{
    jQuery.each( datetime_obj, function( selector, use_current ){
          $(selector).datetimepicker(
            {
                useCurrent: use_current,
                locale:language,
                format:date_format,
                sideBySide:true,
                //debug:true,
                widgetPositioning: {
                    horizontal: 'auto',
                    vertical: 'bottom'
                 }
            });
    });
}
function dependDateTime(selector1,selector2)
{
    $(selector1).on('dp.change',function(e){
        $(selector2).data('DateTimePicker').minDate(e.date);
    });
    $(selector2).on('dp.change',function(e){
        $(selector1).data('DateTimePicker').maxDate(e.date);
    });
}
function generateQrCode(selector,qr_code)
{
    $(selector).html('');
    $(selector).qrcode({
        "text": qr_code
    });
}
function deleteRowDataTable(selector)
{
	table
    .row( $(selector).parents('tr') )
    .remove()
    .draw();
}
function addHtmlTable(selector,page,of,text_button,actives,finished,future,pendient,all)
{
	$('.paginate_page').html(page+' ');
    var str=$('.paginate_of').html();
    $('.paginate_of').html(str.replace("of", of));
    switch (selector) {
        case '#promotions': 
            $("#InputSearch").after('<div class="col-xs-4 col-sm-2"><input type="button" id="createPromo" class="btn btn-sm btn-red" value="'+text_button+'"></div>');
            var filteradio='<div class="row">'+
                    '<div class="col-xs-12">'+
                         '<label class="radio-inline">'+
                               '<input type="radio" name="status" id="PromoStatus" value="active"> '+actives+''+
                        '</label>'+
                        '<label class="radio-inline">'+
                          '<input type="radio" name="status" id="PromoStatus" value="finished"> '+finished+''+
                        '</label>'+
                        '<label class="radio-inline">'+
                          '<input type="radio" name="status" id="PromoStatus" value="future"> '+future+''+
                        '</label>'+
                        '<label class="radio-inline">'+
                          '<input type="radio" name="status" id="PromoStatus" value="pendient"> '+pendient+''+
                        '</label>'+
                        '<label class="radio-inline">'+
                          '<input type="radio" name="status" id="PromoStatus" value="" checked="checked"> '+all+''+
                        '</label>'
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-xs-12"><br>'+
                    '</div>'+
                '</div>';

            $('#promotions').before(filteradio);
            break;
        case '#StaffList':
            $("#InputSearch").after('<div class="col-xs-4 col-sm-2"><input type="button" id="createStaff" class="btn btn-sm btn-red" value="'+text_button+'"></div>');
            break;
        default:
            '';
    }
}
function loadDataTable(selector,html,lengthMenu,searchPlaceholder,zeroRecords,info,infoEmpty,infoFiltered)
{
    $('.table tbody').html('');
	$('.table tbody').html(html);
    table = $(selector).DataTable( {
                        "lengthChange": false,
                        "stateSave": true,
                        "columnDefs": [
                            {
                                "targets": [ 1 ],
                                "visible": false,
                                "searchable": false
                            }/*,
                            {
                                "targets": [ 2 ],
                                "visible": false
                            }*/ 
                        ],
                        "language": {
                            "lengthMenu":lengthMenu,
                            "search": "_INPUT_",
                            "searchPlaceholder": searchPlaceholder,
                            "zeroRecords": zeroRecords,
                            "info": info,
                            "infoEmpty": infoEmpty,
                            "infoFiltered": infoFiltered
                        },
                        "sPaginationType": "listbox",
                        "dom": '<"row"<"#InputSearch.col-xs-8 col-sm-10"f>>t<"row"<"col-xs-9"i><"col-xs-3"p>><"clear">',
                        "drawCallback": function( settings ) {
                                var api = new $.fn.dataTable.Api( settings );
                                $('.downloadExcel').css('display',(api.rows( {page:'current'} ).data().length>0)?'block':'none');
                            }
                    } );

}
function successMessage(message)
{
    $successMessage.html(message);
    $successMessage.fadeTo('slow', 1, function(){
        window.setTimeout(function(){
            $successMessage.fadeTo('slow', 0, function(){
                $successMessage.hide();
            });
        }, 1000*5);
    });
}
function errorMessage(message)
{
    $errorMessage.html(message);
    $errorMessage.fadeTo('slow', 1, function(){
        window.setTimeout(function(){
            $errorMessage.fadeTo('slow', 0, function(){
                $errorMessage.hide();
            });
        }, 1000*5);
    });
}
function checkRadio(selector,value)
{
    if (value != undefined && value != null)
    {
    	if (value.length)
	    {
	    	var $radios = $(selector);
	        if( $radios.is(':checked') === false) {
	            $radios.filter('[value='+value+']').prop('checked', true);
	        }
    	}
    }
}
function showSelect(value)
{
    $('.byEvents').css("display","");
    if(value=='up-sale'|| value=='frequency')
    {
        $('#'+value).css("display","block");
        $('#launchPromo').css("display","block");
    }
}
function showDisplay(selector,value1,condition1,value2,condition2)
{
	if (value2 != undefined && value2 != null)
    {
	    if (value2.length && condition2.length)
	    {
	        $(selector).css("display",(value1==condition1 && value2==condition2)?"block":"none");
	    }
    }
    else
    {
        $(selector).css("display",(value1==condition1)?"block":"none");
    }
}
function showPromoType(value)
{
    showDisplay('#select_event',value,'events');
    $('.byEvents').css("display","");
    if (value=='events')
    {
        showSelect($('#select_event').val())
    }
}
function showPromoBenefits(value)
{
    showDisplay('.requiresPoints',value,'1');
    showDisplay('#PercentDiscount',$('#PromoBenefits input:checked').val(),'discount',value,'1');
}
function loadEventChange(selector)
{
    $(selector).change(
    function(){
            switch (selector) {
                case '#PromoType input:radio': 
                    showPromoType($(this).val());
                    break;
                case '#PromoRequiresPoints input:radio':
                    showPromoBenefits($(this).val());
                    break;
                case '#PromoBenefits input:radio': 
                    showDisplay('#PercentDiscount',$(this).val(),'discount');
                    break;
                case '#PromoQrCodeShow input:radio':
                    showDisplay('#pQrCode',$(this).val(),'1');
                    break;
                case '#changePassword input:radio':
                    showDisplay('#newPassword',$(this).val(),'1');
                    break;
                case '#selectAllBranches':
                    $('div#branches input:checkbox').prop('checked', (this.checked)? true:false);
                    break;
                default:
                    '';
            }
        }
    );

}
function loadEventOn(eventon,selector)
{
    $( "body" ).on( eventon, selector, function() {
        switch (selector) {
            case '#createPromo':
                pztShowLoader($containerWider);
                loadAjax('.promotions','/promotions','PromoCreate');
                break;
            case '.editPromo': 
                pztShowLoader($containerWider);
                loadAjax('.promotions','/promotions','pzt_promo_id='+$(this).attr('pzt_promo_id'));
                break;
            case '#select_event': 
                showSelect(this.value);
                break;
            case '#PromoStatus':

                $.fn.dataTable.ext.search.push(
                    function( settings, data, dataIndex ) {
                        var column =  data[2]  || '';
                        var PromoStatus=$('input:radio[name=status]:checked').val();
                        if (column==PromoStatus )
                        {
                            return true;
                        }
                        if (PromoStatus=='')
                        {
                            return true;
                        }
                        return false;
                    }
                );
                table.draw();
                break;
            case '.deletePromo':
                pztShowLoader($containerWider);
                loadAjax('.promotions','/promotions','delete='+$(this).attr('pzt_promo_id'));
                break;
            case '#saveChangesButton':
                $mainForm.submit();

            	break;
            case '.seeMember': 
                pztShowLoader($containerWider);
                loadAjax('.customers','/customers','pzt_member_id='+$(this).attr('pzt_member_id'));
                $('#reserved').text($(this).attr('reserved'));
                $('#interchanged').text($(this).attr('interchanged'));
                $('#total_spent').text($(this).attr('total_spent'));
                break;
            case '.downloadExcel':
                stringIds=findStringIds('#'+$(this).attr('nameTable'),$('.input-sm').val());
                var PromoStatus=($(this).attr('nameTable')=='promotions')?$('input:radio[name=status]:checked').val():'';
                window.location.href='/'+$(this).attr('nameTable')+'/download/'+ stringIds+'/'+PromoStatus;
                break;
            case '.editStaff':
                pztShowLoader($containerWider);
                loadAjax('.staff','/staff','pzt_staff_id='+$(this).attr('pzt_staff_id'));
                break;
            case '#regenerate':
                pztShowLoader($containerWider);
                loadAjax('.staff','/staff','qc_code='+$('#qr').text()+'&pzt_staff_id='+$('#pzt_staff_id').val());
                break;
            case '#saveChangesStaffButton':
                if (!$(this).hasClass('disabled') )
                {
                    if(jQuery('div#branches input[type=checkbox]:checked').length)
                    {
                        pztShowLoader($containerWider);
                        loadAjax('.staff','/staff',$mainForm.serialize());
                    }
                    else
                    {
                        alert(alert_checkbox_branch);
                    }
                }
                break;
            case '#return':
                //switchSelectorsDisplayHide ('#SearchStaff','#editStaff');
                table.destroy();
                pztShowLoader($containerWider);
                loadAjax('.staff','/staff/load','pzt_staff_id');

                break;
            case '#createStaff':
                $mainForm.trigger('reset');
                pztShowLoader($containerWider);
                loadAjax('.staff','/staff','StaffCreate');
                break;
            default:
                '';
        }
    });
}

function findStringIds(selectorNameTable,stringToCheckFor){
    var table = $(selectorNameTable).dataTable();
    stringIds='';
    $.each(table.fnGetData(), function(i, dtRow) {
        $.each(dtRow, function(j, dtCol) {
            if (j==0) //busca en la primera columna
            {
                if (stringToCheckFor=='')
                {
                    stringIds=stringIds+','+dtRow[1];
                }
                else if (dtCol.toLowerCase().indexOf(stringToCheckFor) >= 0)
                {
                    stringIds=stringIds+','+dtRow[1];
                }
            }
        });
    });
    stringIds=stringIds.substr(1);
    return stringIds;
}

function loadImageAjax()
{
	$image_path.on("filebatchselected", function(event, files) {
        pztShowLoader($containerWider);
        $image_path.fileinput("upload");
    });
    $image_path.on('filebatchuploadsuccess', function(event, data, id, index) {
        var result = data.response;
        if(result != undefined
            && result.pzt != undefined
            && result.pzt.meta != undefined
            && result.pzt.meta.response_type != undefined
            && result.pzt.meta.response_type == 'success')
        {
            $('#pImagePath').attr('src', '//' + result.pzt.response.s3);
            $('#ImagePathUrl').val(result.pzt.response.s3);
            $('#image_path').val(result.pzt.response.s3);
            $('.file-caption-name').append(result.pzt.response.s3);
            pztHideLoader();
        }
    });

    //TODO: handle error
    $image_path.on('filebatchuploaderror', function(event, data, id, index) {
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
}
var update = function () {
    var now  = $('#end_dt').val() ;
    var then = moment().format('DD/MM/YYYY HH:mm:ss');
    var ms = moment(now,"DD/MM/YYYY HH:mm:ss").diff(moment(then,"DD/MM/YYYY HH:mm:ss"));
    var d = moment.duration(ms);
    if(Math.floor(d.asDays())==0)
    {
        var s = Math.floor(d.asHours()) + moment.utc(ms).format(":mm:ss");
    }
    else
    {
        var s = Math.floor(d.asDays()) + 'day (s)';
    }
    $('#PreviewHoursLeft').text(s);
};

function drawChart() {

      var dataNumberVsTime = new google.visualization.DataTable();
      dataNumberVsTime.addColumn('date', 'X');
      dataNumberVsTime.addColumn('number', hystoryInterchangedPromo);

      dataNumberVsTime.addRows([
        [new Date(2012,7,1), 0],   [new Date(2012,7,2), 10],[new Date(2012,7,3), 23],   [new Date(2012,7,4), 17],
        [new Date(2012,7,5), 18],   [new Date(2012,7,6), 9],[new Date(2012,7,7), 11],   [new Date(2012,7,8),27]
      ]);

      var optionsNumberVsTime = {
        hAxis: {
          title: labelAxisXNumber,
          format: "MM/dd/yy"
        },
        vAxis: {
          title: labelAxisYNumber
        }
      };

      var dataAmountVsTime = new google.visualization.DataTable();
      dataAmountVsTime.addColumn('date', 'X');
      dataAmountVsTime.addColumn('number', historyPurchases);

      dataAmountVsTime.addRows([
        [new Date(2012,7,1), 0],   [new Date(2012,7,2), 10],[new Date(2012,7,3), 23],   [new Date(2012,7,4), 17],
        [new Date(2012,7,5), 18],   [new Date(2012,7,6), 9],[new Date(2012,7,7), 11],   [new Date(2012,7,8),27]
      ]);

      var optionsAmountVsTime = {
        hAxis: {
          title: labelAxisXAmount
        },
        vAxis: {
          title: labelAxisYAmount
        }
      };

      var chartNumberVsTime = new google.visualization.LineChart(document.getElementById('NumberVsTime'));
      var chartAmountVsTime = new google.visualization.LineChart(document.getElementById('AmountVsTime'));
      chartNumberVsTime.draw(dataNumberVsTime, optionsNumberVsTime);
      chartAmountVsTime.draw(dataAmountVsTime, optionsAmountVsTime);
     }