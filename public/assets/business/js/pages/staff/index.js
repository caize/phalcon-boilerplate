$(function(){
    $('.table-responsive').before('<span id="noStaff">No existe Staff vigentes.</span>');
    $('.table-responsive,#download').css('display','none');

    if($containerWider.length)
    {
        //loading generic loading screen, locks screen and tells client to wait
        pztShowLoader($containerWider);
    }
    loadAjax('.staff','/staff/load','pzt_staff_id');
    loadEventOn('click','.editStaff');
    loadEventOn('click','#regenerate');
    loadEventOn('click','#saveChangesStaffButton');
    loadEventChange('#changePassword input:radio');
    loadEventChange('#selectAllBranches');
    loadEventOn('click','#return');
    loadEventOn('click','#createStaff');
    loadEventOn('click','.downloadExcel');
    $('#mainForm').bind('submit',function(e) {
	  e.preventDefault(); //Will prevent the submit...
	});
});