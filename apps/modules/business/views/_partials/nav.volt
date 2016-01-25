<ul class="nav nav-tabs">  
  <li role="presentation" {% if (navbar["name"] == "dashboard") %} {{ "class='active'" }} {% endif %} ><a href="/dashboard">Dashboard</a></li>
  <li role="presentation" {% if (navbar["name"] == "retailer") %} {{ "class='active'" }} {% endif %}><a href="/retailer">Tienda</a></li>
  <li role="presentation" {% if (navbar["name"] == "products") %} {{ "class='active'" }} {% endif %}><a href="/products">Productos</a></li>
  <li role="presentation" {% if (navbar["name"] == "segment") %} {{ "class='active'" }} {% endif %}><a href="/segment">Segmentación</a></li>

  <li role="presentation" {% if (navbar["name"] == "promotions") %} {{ "class='active'" }} {% endif %} ><a href="/promotions">Promociones</a></li>
  <li role="presentation" {% if (navbar["name"] == "customers") %} {{ "class='active'" }} {% endif %}><a href="/customers">Clientes</a></li>
  <li role="presentation" {% if (navbar["name"] == "sales") %} {{ "class='active'" }} {% endif %}><a href="/sales">Ventas</a></li>
  <li role="presentation" {% if (navbar["name"] == "aditional") %} {{ "class='active'" }} {% endif %}><a href="/aditional">Adicionales</a></li>
  <li role="presentation" {% if (navbar["name"] == "staff") %} {{ "class='active'" }} {% endif %}><a href="/staff">Staff</a></li>
  <li role="presentation" {% if (navbar["name"] == "myaccount") %} {{ "class='active'" }} {% endif %}><a href="/myaccount">Mi Cuenta</a></li>
  <li role="presentation"><a href="#" id="logoutLink">Salir</a></li>
</ul>