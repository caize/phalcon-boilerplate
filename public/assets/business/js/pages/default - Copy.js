$(function () {
	// Configuration TINYMCE
	// Dear reader, it's actually very easy to initialize MiniColors. For example:
	// $(selector).init({ Object : atributtes });
   	if(typeof tinyMCE == 'object')
   	{
   		tinyMCE.init({
			mode : "specific_textareas",
			editor_selector : "mceEditor",
			theme: "modern",		
			plugins: [
				"lists charmap preview pagebreak",
				"searchreplace wordcount visualblocks visualchars fullscreen insertdatetime nonbreaking",
				"table contextmenu directionality textcolor code fullpage "			
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | table | preview media fullpage code",
			menubar:false,
			statusbar: false,
			height : 300
		});
   	}


	$('.inputcolor').each( function() {
		//
		// Dear reader, it's actually very easy to initialize MiniColors. For example:
		//
		//  $(selector).minicolors();
		//
		// The way I've done it below is just for the demo, so don't get confused
		// by it. Also, data- attributes aren't supported at this time...they're
		// only used for this demo.
		//
		
			$(this).minicolors({
				control: $(this).attr('data-control') || 'hue',
				defaultValue: $(this).attr('data-defaultValue') || '',
				inline: $(this).attr('data-inline') === 'true',
				letterCase: $(this).attr('data-letterCase') || 'lowercase',
				opacity: $(this).attr('data-opacity'),
				position: $(this).attr('data-position') || 'bottom left',
				change: function(hex, opacity) {
					if( !hex ) return;
					if( opacity ) hex += ', ' + opacity;
					if( typeof console === 'object' ) {
					    //console.log(hex);
					}
				},
				theme: 'bootstrap'
			});			
	});

});

/* Functions */

function load_senders()
{
	var $orgId = $("#org-id");
	var $ctnSenders = $(".ctn-senders");

	$.ajax({
		url: "/sender/list",
		data: { organization_id : $orgId.val()},
		type: "POST"
	}).done(function(json){
		if(json != undefined 
				&& json.response != undefined 
				&& json.response.success)
		{
			var senders = json.response.success.senders;
			var html_ctn_senders = "";			
			$.each(senders, function(i, item){
				html_ctn_senders += "<tr>"
				html_ctn_senders += "<td>" + senders[i].name + "</td>"
				html_ctn_senders += "<td><a href='javascript:void(0)' data-id='"+ senders[i].organization_sender_id +"' class='delete-sender'>Eliminar</a></td>"
				
				//html_ctn_users += "<td><a href='javascript:void(0)' linkOrgId='" + users[i].user_id + "' class='userEditClick'>Administrar</a></td>"
				html_ctn_senders += "</tr>";
			});
			
			$ctnSenders.html(html_ctn_senders);
			var $deleteSender = $(".delete-sender");
			$deleteSender.click(function(){				
				delete_sender($(this).attr("data-id"));
			});
		}
		else
		{
			html_ctn_senders = "<tr><td colpan=2><p class='text-danger'>No se encontrar&oacute;n senders</p></td></tr>";
			$ctnSenders.html(html_ctn_senders);
		}
		return false;
	});
}

/* DELETE SENDER TODO MARIO */
function delete_sender(org_sender_id)
{
	if(confirm("Confirmar la eliminación de sender"))
		{			
			$.ajax({
				url: "/sender/delete",
				data: {
					organization_sender_id : org_sender_id
				},
				method: "POST",
				success: function(json){
					if(json != undefined && json.response.success != undefined)
					{
						load_senders();
					}
					else
					{
						alert("error");
					}
				},error :function(){
					alert("Error de conexi&oacute;n");
					console.log("Error de conexi&oacute;n");
				}
			});
		}
}

function load_users()
{
	/* Todo Refresh Users */
	var $orgId = $("#org-id");
	var $ctnUsers = $(".ctn-users");
	$.ajax({
		url: "/user/list",
		data: { organization_id : $orgId.val()},
		type: "POST"		
	}).done(function(json){
		if(json != undefined 
				&& json.response != undefined 
				&& json.response.success)
		{
			var users = json.response.success.users;
			var html_ctn_users = "";
			$.each(users, function(i, item){				
				html_ctn_users += "<tr id='row-user-detail-'"+ users[i].user_id+">"
				html_ctn_users += "<td>" + users[i].first_name + "</td>"
				html_ctn_users += "<td>" + users[i].last_name1 + "</td>"
				html_ctn_users += "<td>" + users[i].email + "</td>"
				if(users[i].is_active == 1)
				{
					html_ctn_users += "<td><p class='text-success'>Activo</p></td>"	
				}
				else
				{
					html_ctn_users += "<td><p class='text-danger'>Inactivo</p></td>"	
				}
				
				html_ctn_users += "<td><a href='javascript:void(0)' linkOrgId='" + users[i].user_id + "' class='userEditClick'>Administrar</a></td>"
				html_ctn_users += "</tr>";
			});
			
			$ctnUsers.html(html_ctn_users);
			edit_user();			
		}
		else
		{
			html_ctn_users = "<tr><td colpan=5><p class='text-danger'>No se encontrar&oacute;n Cont&aacute;ctos</p></td></tr>";
			$ctnUsers.html(html_ctn_users);
		}
		return false;
	});
}

function edit_user()
{
	var $userEditClick = $('.userEditClick');
	var $userEditModal = $('#userEditModal');	
	$userEditClick.click(function(e){
		//$userEditForm[0].reset();
		$.ajax({
			url: "/user/detail",
			data: 'user_id='+$(this).attr('linkOrgId'),
			method: "POST",
			success: function(result){
				if(result != undefined
					&& result.response != undefined
					&& result.response.success) {

					var data = result.response.success.user;
					
					$userEditModal.modal();

					$(".detail-orgname").text($("#org-name").val());
					$(".detail-username").text(data.first_name+' '+data.last_name1);

					$("#edit-userid").val(data.user_id);
					$("#edit-firstname").val(data.first_name);
					$("#edit-lastname1").val(data.last_name1);
					$("#edit-lastname2").val(data.last_name2);
					$("#edit-jobtitle").val(data.job_title);
					$("#edit-email").val(data.email);
					
					if(data.can_send == 1) {
						$("#edit-cansend1").attr("checked",true);
					}
					else {
						$("#edit-cansend0").attr("checked",true);
					}

					if(data.is_active == 1) {
						$("#edit-isactive1").attr("checked",true);
					}
					else {
						$("#edit-isactive0").attr("checked",true);					
					}					
				}
				else{
					
					var msgerror = 'El contacto no existe';					
					$detErrorMsg.html(msgerror);
					$detErrorMsg.show('slow');
				}

			},
			error: function(){
				$detErrorMsg.html('Error de conexi&oacute;n');
				$detErrorMsg.show('slow');
				
				alert('Error de conexi&oacute;n')
			}
		});
		$userEditModal.modal();
		return false;
	});
}

/* Search Organization */
function searchOrganization(url, _this, current_page)
{
	var $searchForm = $("#formSearch");
	current_page = current_page != undefined ? "&current_page="+current_page : "";
	var html_table = ""
	var html_pagination = "";
	var html_total_items = "<p>Total de Registros 0</p>";
	$.ajax({
		url: url,
		method: "POST",
		data: $searchForm.serialize() + current_page,
		success: function(json){	
			/*console.log(json);*/
			if(json.response.items.length > 0)
			{
				for(i = 0; i < json.response.items.length; i++)
				{
					html_table += "<tr>";
					html_table += "<td>"+ json.response.items[i].name +"</td>";
					html_table += "<td>"+ json.response.items[i].uri +"</td>";
					//html_table += "<td>"+ json.response.items[i].contacts  +" cont&aacute;ctos | <a href='#nuevo_contact'> crear nuevo </a>  </td>";
					html_table += "<td>"+ json.response.items[i].contacts  +" cont&aacute;ctos | <a href='javascript:void(0)' linkOrgId='"+json.response.items[i].organization_id+"' class='userAddClick'>crear nuevo</a></td>";
					html_table += "<td><a href='organization/detail/?id="+json.response.items[i].organization_id+"'> Administrar </a></td>";
					html_table += "</tr>";
				}
			}

			html_pagination = "<ul class='pager'>";
			if(json.response.total_pages > 1)
			{
				html_pagination += "<li><label>Total de p&aacute;gina(s) : <select id='current_page' name='current_page'>";
				for (var i = 1; i <= json.response.total_pages; i++) {
					html_pagination += "<option value='"+i+"'>"+ i +"</option>";
				};
				html_pagination += "</select></label></li>";
				//var total_pages = json.response.total_pages;
				html_pagination += "<li class='opt-before'><a href='#search' class='before_page' data-before=''>Anterior</a></li>";
				html_pagination += "<li class='opt-next '><a href='#search' class='next_page' data-next=''>Siguiente</a></li>";
				// Todo 

			}
			else
			{
				html_pagination += "<li><label>Total de p&aacute;gina(s) : <select id='current_page' name='current_page'><option value='1'>1</option></select></label></li>";
			}
			html_pagination += "</ul>";
			html_total_items = "Total de Registros " + json.response.total_items;
							

			$('.loadTable').html(html_table);
			$(".paginator").html(html_pagination);
			$(".result_set").html(html_total_items);				
			$(_this).attr('disabled', false);

			//current(total_pages, json.response);
			_current(json.response);

			/// start : add new contact ///
			var $userAddClick = $('.userAddClick');
			$userAddClick.click(function(){
			
				//$('#userAddModal')[0].reset();

				$("#orgId").val($(this).attr('linkOrgId'));
				$('#userAddModal').modal('show');
			});
			/// end  : add new contact ///
			
		},error :function(){
			$(_this).attr('disabled', false);
		}
	});
}

/* Current Search Organization */
function _current(json)
{
	var url = "/organization/search";
	var _this = this;
	var $current_page = $("#current_page");
	var $next_page = $(".next_page");
	var $before_page = $(".before_page");
	var current_page;		

	if(json.items.length > 0 )
	{
		var difference = json.total_pages - json.current;

		$next_page.attr("data-next", json.next);
		var val_next = $next_page.attr("data-next");
		current_page = val_next;

		if( difference == 1 )
		{
			current_page = json.last;
			$next_page.attr("data-next", json.last);
		}

		$next_page.click(function(){																
			if(difference == 0)
			{
				last = parseInt(current_page) + json.last;
				$next_page.attr("data-next",last );
				
				return false;
			}
			console.log(current_page);
			searchOrganization(url, _this, current_page);
		});

		$before_page.attr("data-before", json.before);
		var val_before = $before_page.attr("data-before");
		var test = parseInt(current_page) - val_before;
		$before_page.click(function(){
			if(current_page == 0)
			{
				return false;
			}
			current_page = val_before;

			searchOrganization(url, _this, current_page);
		});

		$current_page.on('change', function(){
			current_page = $current_page.val();
			searchOrganization(url, _this, current_page);
		});
		$("#current_page option").each(function(){
			if ($(this).val() == json.current )
			{
				/*console.log($(this).text());
				console.log(json.current);*/
				$(this).attr("selected","selected");
			}
		});
	}
}

function upload_squeeze_file(objElem, uploadUrl, acceptTypeFiles)
{
	acceptTypeFiles = acceptTypeFiles === undefined ? ["html", "htm"] : acceptTypeFiles;
	uploadUrl = uploadUrl === undefined ? "/alert/loadhtml" : uploadUrl;

	var $uploadSqueeze = objElem;

	$uploadSqueeze.fileinput({
		uploadUrl: uploadUrl,
		uploadAsync: true,
		showUpload: false, 
		showRemove: false,
		allowedFileExtensions: acceptTypeFiles,
		minFileCount: 1,
		maxFileCount: 1
		/*uploadExtraData: function() {
			return {
				bdInteli: "xxxx"
			};
		}*/
	});
}

/* Functions Globals */
function validateNumeric(e){	
	var key = window.Event ? e.which : e.keyCode
	
	if(key >= 48 && key <= 57 || (key == 8))
	{
		return key;
	}
	else if(e.keyCode == 9)
	{
		return e.keyCode;
	}
	return false
}

function validateLetters(e)
{
	var key = window.Event ? e.which : e.keyCode		
	if((key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (key == 8))
	{
		return key;
	}
	else if(e.keyCode == 9)
	{
		return e.keyCode;
	}
	return false
}