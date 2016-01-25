{{ javascript_include("assets/common/js/jquery/jquery-1.11.3.min.js") }}
{{ javascript_include("assets/common/js/jquery.ui/jquery-ui.min.js") }}
{{ javascript_include("assets/common/js/bootstrap/bootstrap.min.js") }}
{{ javascript_include("assets/common/js/bootstrap/validator.min.js") }}
<!-- https://github.com/kartik-v/bootstrap-fileinput -->
{{ javascript_include("assets/common/js/bootstrap-fileinput/js/fileinput.min.js") }}
{{ javascript_include("assets/common/js/jquery/jquery.placeholder.min.js") }}
{{ javascript_include("assets/common/js/jquery-qrcode/jquery.qrcode-0.12.0.min.js") }}
<!-- Minicolors -->
{{ javascript_include('assets/common/js/jquery.colorpicker/jquery.minicolors.js') }}

{{ javascript_include("assets/common/js/jquery/jquery.dataTables.min.js") }}
{{ javascript_include("assets/common/js/jquery/dataTables.bootstrap.min.js") }}

{{ javascript_include("assets/common/js/jquery/select.js") }}

{{ javascript_include("assets/common/js/bootstrap-datepicker/bower_components/moment/min/moment-with-locales.min.js") }}
{{ javascript_include("assets/common/js/bootstrap-datepicker/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js") }}
{{ javascript_include("https://www.google.com/jsapi") }}

{% if js_files is defined %}
    {% for js_file in js_files %}
        {{ javascript_include(js_file) }}
    {% endfor %}
{% endif %}