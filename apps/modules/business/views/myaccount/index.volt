{% include '_partials/generic/doctype.volt' %}
{% include '_partials/generic/html_open.volt' %}

{% include '_partials/generic/head_open.volt' %}
{% include '_partials/head_default.volt' %}
{% include '_partials/generic/head_close.volt' %}

{% include '_partials/generic/body_open.volt' %}
    <div class="container-fluid">
    {% include '_partials/top.volt' %}
    	<div class="container business-container">
	    {% include '_partials/nav.volt' %}
	        <div class="row">
	            <div class="col-xs-12">
	              <div id="successMessageF" class="success-message"></div>
	              <div id="errorMessageF" class="error-message"></div>
	            </div>
	          </div>
			
	          <div class="row">
	              <div class="col-xs-12">
	                <form class="form-horizontal" role="form">
	                  <div class="row">
			        		<div class="col-xs-8 col-sm-9">
			        			<div class="form-group">
									<h4 class="form-subtitle">{{ lang_p.principal }}</h4>
								</div>
								<div class="form-group">
									<label for="name" class="col-sm-4 control-label">{{ lang_p.name }}</label>
									<div class="col-sm-6">
										<input type="text" class="form-control" id="name" placeholder="{{ lang_p.placeholder_name }}">
									</div>
								</div>
								<div class="form-group">
									<label for="surname" class="col-sm-4 control-label">{{ lang_p.surname }}</label>
									<div class="col-sm-6">
										<input type="text" class="form-control" id="surname" placeholder="{{ lang_p.placeholder_surname }}">
									</div>
								</div>
								<div class="form-group">
									<label for="email" class="col-sm-4 control-label">{{ lang_p.email }}</label>
									<div class="col-sm-6">
									<input type="email" class="form-control" id="email" placeholder="{{ lang_p.placeholder_email }}">
									</div>
								</div>
								<div class="form-group">
									<label for="phone" class="col-sm-4 control-label">{{ lang_p.phone }}</label>
									<div class="col-sm-6">
									<input type="text" class="form-control" id="phone" placeholder="{{ lang_p.placeholder_phone }}">
									</div>
								</div>
								<div class="form-group">
									<label for="account_type" class="col-sm-4 control-label">{{ lang_p.account_type }}</label>
									<div class="col-sm-6">
										<label class="radio-inline">
										  <input type="radio" name="administrative" id="administrative" value="administrative"> {{ lang_p.administrative }}
										</label>
										<label class="radio-inline">
										  <input type="radio" name="operative" id="operative" value="operative"> {{ lang_p.operative }}
										</label>
										<p class="note-message-small">{{ lang_p.note1 }}</p>
										<p class="note-message-small">{{ lang_p.note2 }}</p>
									</div>
								</div>
								<div class="form-group">
									<label for="change_password" class="col-sm-4 control-label">{{ lang_p.change_password }}</label>
									<div class="col-sm-6">
										<label class="radio-inline">
										  <input type="radio" name="yes" id="yes" value="yes"> {{ lang_p.yes }}
										</label>
										<label class="radio-inline">
										  <input type="radio" name="not" id="not" value="not"> {{ lang_p.nothing }}
										</label>
									</div>
								</div>
								<div class="form-group">
									<label for="new_password" class="col-sm-4 control-label">{{ lang_p.new_password }}</label>
									<div class="col-sm-6">
									<input type="password" class="form-control" id="new_password" placeholder="{{ lang_p.placeholder_new_password }}">
									</div>
								</div>
								<div class="form-group">
									<label  class="col-sm-4 control-label">{{ lang_p.state }}</label>
									<div class="col-sm-6">
									<p class="form-control-static">{{ lang_p.active }}</p>
									</div>
								</div>
			        		</div>
			        		<div class="col-xs-4 col-sm-3 rounded text-center">
			        			
			        				<h3 class="note-message">{{ lang_p.reminder3 }}</h3>
			        				<img  id="qr" src="/assets/business/images/qr.png"  width="200px" height="200px"> 
			        				<h3>AAAAA51DF474</h3>
			        				<input type="button" id="print" class="btn btn-gray btn-lg" value="{{ lang_p.print }}"><br><br>
			        				<input type="button" id="regenerate" class="btn btn-red btn-lg" value="{{ lang_p.regenerate }}">
			        				<p class="note-message">{{ lang_p.reminder4 }}</p>
			        			
			        		</div>
		        		</div>
		        		<div class="row">
			        		<div class="col-xs-12">
			          			<div class="form-group">
			                    	<div class="form-subtitle">{{ lang_p.branches }}</div>
			                  	</div>
			        		</div>
		        		</div>
		        		<div class="row">
			        		<div class="col-xs-12">
			          			<div class="form-group">
			                    	<p class="note-message">{{ lang_p.reminder1 }}</p>
			                    	<p class="note-message">{{ lang_p.reminder2 }}</p>
			                  	</div>
			        		</div>
		        		</div>
		        		<div class="row">
			        		<div class="col-xs-4">
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>San Miguel
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>Pueblo Libre
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Surco
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Javier Prado Este
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>La perla
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Todas
									</label>
								</div>
								<br>
								<input type="button" id="save_changes" class="btn btn-red btn-lg" value="{{ lang_p.save_changes }}">
			        			<input type="button" id="return" class="btn btn-gray btn-lg" value="{{ lang_p.return_back }}">
			        		</div>
			        		<div class="col-xs-4">
			          			<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>San Miguel
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>Pueblo Libre
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Surco
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Javier Prado Este
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>La perla
									</label>
								</div>
			        		</div>
			        		<div class="col-xs-4">
			          			<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>San Miguel
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>Pueblo Libre
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Surco
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="">Javier Prado Este
									</label>
								</div>
								<div class="checkbox">
									<label>
										<input type="checkbox" value="" checked>La perla
									</label>
								</div>
			        		</div>
		        		</div>
	                </form>
	              </div>
	          </div>
	    </div> <!-- /container -->
    {% include '_partials/bottom.volt' %}
    </div> <!-- /container-fluid -->
{% include '_partials/generic/js_libs.volt' %}
{% include '_partials/generic/body_close.volt' %}
{% include '_partials/generic/html_close.volt' %}     