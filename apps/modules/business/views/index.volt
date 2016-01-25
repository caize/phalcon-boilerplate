{% include '_partials/generic/doctype.volt' %}
{% include '_partials/generic/html_open.volt' %}

{% include '_partials/generic/head_open.volt' %}
{% include '_partials/head_default.volt' %}
{% include '_partials/generic/head_close.volt' %}

{% include '_partials/generic/body_open.volt' %}
    <div class="container-fluid">
    {% include '_partials/top.volt' %}    
      <div class="container business-container">
        <form id="loginForm" name="loginForm" class="form-signin">
          <h2 class="form-heading">{{ lang_p.title }}</h2>
          <div id="successMessage" class="success-message"></div>
          <div id="errorMessage" class="error-message"></div>
          <label for="inputEmail" class="sr-only">{{ lang_p.email }}</label>
          <input type="email" id="inputEmail" name="email" class="form-control" placeholder="{{ lang_p.email }}" required autofocus value="{{ remembered_email }}">
          <label for="inputPassword" class="sr-only">{{ lang_p.password }}</label>
          <input type="password" id="inputPassword" name="password" class="form-control" placeholder="{{ lang_p.password }}" required>
          <div class="checkbox">
            <label>
              {% if remembered_email is empty %}
                  <input type="checkbox" name="remember_me" value="1"> {{ lang_p.remember_me }}
              {% else %}
                  <input type="checkbox" name="remember_me" value="1" checked> {{ lang_p.remember_me }}
              {% endif %}
            </label>
          </div>
          <div class="forgot">
            <a id="forgotView" href="#">{{ lang_p.forgot }}</a>
          </div>
          <button id="loginButton" class="btn btn-lg btn-primary btn-block" type="submit">{{ lang_p.login }}</button>
          <button id="newAccountButton" class="btn btn-lg btn-secondary btn-block" type="submit">{{ lang_p.new_account }}</button>
        </form>

        <form id="signupForm" name="signupForm" class="form-signup">
          <h2 class="form-heading">{{ lang_p.title_signup }}</h2>
          <div id="successMessageS" class="success-message"></div>
          <div id="errorMessageS" class="error-message"></div>

          <div class="form-subtitle">{{ lang_p.about_company }}</div>
          <label for="brandNameID" class="sr-only">{{ lang_p.brand_name }}</label>
          <input type="text" id="brandNameID" name="brand_name" class="form-control" placeholder="{{ lang_p.brand_name }}" required autofocus value="">

          <label for="legalNameID" class="sr-only">{{ lang_p.legal_name }}</label>
          <input type="text" id="legalNameID" name="legal_name" class="form-control" placeholder="{{ lang_p.legal_name }}" required value="">

          <label for="legalIdID" class="sr-only">{{ lang_p.legal_id }}</label>
          <input type="text" id="legalIdID" name="legal_id" class="form-control" placeholder="{{ lang_p.legal_id }}" required value="">

          <div class="form-subtitle">{{ lang_p.about_you }}</div>
          <label for="firstNameID" class="sr-only">{{ lang_p.first_name }}</label>
          <input type="text" id="firstNameID" name="first_name" class="form-control" placeholder="{{ lang_p.first_name }}" required value="">

          <label for="lastNameID" class="sr-only">{{ lang_p.last_name }}</label>
          <input type="text" id="lastNameID" name="last_name" class="form-control" placeholder="{{ lang_p.last_name }}" required value="">

          <label for="emailID" class="sr-only">{{ lang_p.email }}</label>
          <input type="email" id="emailID" name="email" class="form-control" placeholder="{{ lang_p.email }}" required value="">          

          <label for="passwordID" class="sr-only">{{ lang_p.password }}</label>
          <input type="password" id="passwordID" name="password" class="form-control" placeholder="{{ lang_p.password }}" required value="">          

          <label for="passwordCheckID" class="sr-only">{{ lang_p.password_check }}</label>
          <input type="password" id="passwordCheckID" name="password_check" class="form-control" placeholder="{{ lang_p.password_check }}" required value="">

          <label for="phoneID" class="sr-only">{{ lang_p.phone }}</label>
          <input type="text" id="phoneID" name="phone" class="form-control" placeholder="{{ lang_p.phone }}" required value="">                              

          <button id="signupButton" class="btn btn-lg btn-primary btn-block" type="submit">{{ lang_p.signup }}</button>
          <button id="signupCancelButton" class="btn btn-lg btn-secondary btn-block" type="submit">{{ lang_p.signup_cancel }}</button>
        </form>

        <form id="forgotForm" name="forgotForm" class="form-forgot">
          <h2 class="form-heading">{{ lang_p.title_forgot }}</h2>
          <div id="successMessageF" class="success-message"></div>
          <div id="errorMessageF" class="error-message"></div>
          <label for="forgotEmail" class="sr-only">{{ lang_p.email }}</label>
          <input type="email" id="forgotEmail" name="email" class="form-control" placeholder="{{ lang_p.email }}" required autofocus value="{{ remembered_email }}">
          <div class="forgot">
            <a id="loginTry" href="#">{{ lang_p.try_again }}</a>
          </div>
          <button id="forgotButton" class="btn btn-lg btn-primary btn-block" type="submit">{{ lang_p.recover_password }}</button>
        </form>        
      </div>
    {% include '_partials/bottom.volt' %}
    </div> <!-- /container -->
{% include '_partials/generic/js_libs.volt' %}
{% include '_partials/generic/body_close.volt' %}
{% include '_partials/generic/html_close.volt' %}     