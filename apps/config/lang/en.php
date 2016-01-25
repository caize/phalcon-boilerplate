<?php
/**
 * Language Settings
 */
return new \Phalcon\Config(array(
    'business' => array(
        'global' => array(
            'header' => array(),
            'nav' => array(
                    'dashboard' => 'dashboard',
                    'business' => 'tienda',
                    'products' => 'productos',
                    'segmentation' => 'sergmentaci&oacute;',
                    'promos' => 'promociones',
                    'clients' => 'clientes',
                    'sales' => 'ventas',
                    'staff' => 'staff',
                    'me' => 'mi cuenta',
                    'logout' => 'salir'
                ),
            'footer' => array('note' => date('Y').' &copy; Portal administrador por Promoziti SAC. Desarrollado por <a href="//flexit.net" target="_blank">FLEXIT</a>')
            ),
        'pages' => array(
            'login' => array('title' => 'Portal Empresarial',                            
                            'title_forgot' => 'Olvid&eacute; mi Contrase&ntilde;a',
                            'email' => 'email',
                            'password' => 'contrase&ntilde;a',
                            'remember_me' => 'recordarme',
                            'forgot' => 'olvid&eacute; mi contrase&ntilde;a',
                            'login' => 'ingresar',
                            'new_account' => 'nueva cuenta',
                            'invalid_email_password' => 'email o contrase&ntilde;a inv&aacute;lidos',

                            'title_signup' => 'Crear Nueva Cuenta',
                            'about_company' => 'Acerca de tu empresa',
                            'brand_name' => 'nombre comercial/marca',
                            'legal_name' => 'raz&oacute;n social',
                            'legal_id' => 'RUC',
                            'about_you' => 'Acerca de ti',
                            'first_name' => 'nombre',
                            'last_name' => 'apellido',
                            'password_check' => 'repetir contrase&ntilde;a',
                            'phone' => 'tel&eacute;fono de contacto',
                            'new_account' => 'crear una cuenta',
                            'signup' => 'registrarse',
                            'signup_cancel' => 'cancelar',
                            'try_again' => 'intentarlo otra vez',
                            'recover_password' => 'Recuperar contrase&ntilde;a'

                            ),
            'reset' => array('title' => 'Restablecer Contrase&ntilde;a',
                            'password' => 'contrase&ntilde;a',
                            'password_check' => 'repetir contrase&ntilde;a',
                            'save' => 'restablecer',
                            'invalid_token_message' => 'El token no es v&aacute;lido o a expirado',
                            'action_success' => 'Su contrase&ntilde;a ha sido restablecida',
                            'action_error' => 'No se pudo restablecer su contrase&ntilde;a',
                            ),
            'retailer' => array('title' => 'Tienda',
                            'brand_name' => 'Nombre Comercial / Marca',
                            'logo' => 'Logo',
                            'category' => 'Categoría',
                            'description'=>'Descripción',
                            'branches' => 'Sucursales',
                            'name' => 'Nombre',
                            'name_example' => 'E.j. San Miguel,Jockey, etc..',
                            'address' => 'Dirección',
                            'address_example' => 'E.j. Calle la molina 2051',
                            'city_example' => 'E.j. Lima',
                            'map' => 'Ubicar',
                            'phone' => 'Tel&eacute;fono',
                            'phones' => '+ Tel&eacute;fonos',
                            'opening_hours' => 'Horarios de atención',
                            'opening_hours_weekdays'=>'Lun - Vie',
                            'opening_hours_weekdays_sample'=>'(ej. 10am - 10pm)',
                            'opening_hours_saturdays'=>'S&aacute;bados',
                            'opening_hours_saturdays_sample'=>'(ej. 10am - 11pm)',
                            'opening_hours_sundays'=>'Domingos',
                            'opening_hours_sundays_sample'=>'(ej. 10am - 3pm)',
                            'opening_hours_holidays'=>'Feriados',
                            'opening_hours_holidays_sample'=>'(ej. 10am - 1pm)',
                            'branch_open'=>'Ver detalles',
                            'branch_close'=>'Cerrar',
                            'branch_delete'=>'Eliminar',
                            'branch_undo'=>'Recuperar',
                            'branch_validation_name_s' => '&#x2713; Nombre de sucursal completado',
                            'branch_validation_name_e' => '&#x2717; Asegurate de haber nombrado tu sucursal',
                            'branch_validation_address_s' => '&#x2713; Direcci&oacute;n completada',
                            'branch_validation_address_e' => '&#x2717; Es necesario que completes tu direcci&oacute;n',
                            'branch_validation_geo_s' => '&#x2713; Ubicaci&oacute;n en mapa completada',
                            'branch_validation_geo_e' => '&#x2717; Es necesario que ubiques tu sucursal en el mapa',                            
                            'more_branches' => '+ Sucursales',
                            'save_changes' => 'Guardar Cambios',
                            'save_success_message' => 'Los cambios han sido guardados exitosamente',
                            'save_error_message' => 'Hubo un problema guardando los cambios, int&eacute;ntelo m&aacute;s tarde',
                            'preview' => '- preview -'
                            ),
            'products' => array('title' => 'Productos',
                            'subtitle_reminder' => 'Lista de productos / servicios',
                            'reminder1' => 'Recuerda que esta lista será visualizada por tus clientes al momento en que ellos realizan una compra.',
                            'reminder2'=>'Con el fin de optimizar el proceso de compra, solo permitimos tener hasta 20 productos / servicios activos',
                            'product_service'=>'Producto / Servicio',
                            'average_price'=>'Precio Promedio',
                            'active'=>'Activo',
                            'add_more'=>'Agregar más',
                            'note1'=>'* Sólo podrás eliminar productos que no hayan sido utilizados previamente en alguna transaccion.',
                            'note2'=>'* Siempre podrás desactivar productos de esta lista',
                            'save_changes' => 'Guardar Cambios',
                            'action_delete'=>'Borrar',
                            'save_success_message' => 'Los cambios han sido guardados exitosamente',
                            'save_error_message' => 'Hubo un problema guardando los cambios, int&eacute;ntelo m&aacute;s tarde',
                            'delete_success_message'=>'Hubo un problema al borrar el producto, intentelo más tade',
                            'preview' => '- preview -'
                            ),
            'segment' => array('title' => 'Segmentación',
                            'reminder1' => 'Definir una segmentación de clientes te permitirá poder crear promociones o comunicación efectiva a un segmento de tus clientes importantes para tu empresa',
                            'customers_vip' => 'Clientes VIP',
                            'by_ticket'=>'Por ticket de compra',
                            'message_ticket1'=>'Aquellos que compran al menos un total de ',
                            'message_ticket2'=>'soles en 1',
                            'select_frequency'=>'Seleccionar frecuencia',
                            'by_frequency'=>'Por frecuencia de compra',
                            'message_frequency1'=>'Aquellos que compran al menos',
                            'message_frequency2'=>'veces en 1',
                            'frequency_week'=>'semana',
                            'frequency_month'=>'mes',
                            'check_restrict'=>'Restringir compra mínima',
                            'message_restrict'=>'El monto minimo para considerar la compra es de',
                            'currency'=>'soles',
                            'save_changes' => 'Guardar Cambios',
                            'potential_vip'=>'Potenciales VIP (?)',
                            'by_preferences'=>'Por preferencias (?)',
                            'add_preferences'=>'+ preferencias',
                            'name'=>'Nombre',
                            'asociate_products'=>'Productos asociados',
                            'actions'=>'Acciones'
                            ),
            'promotions'=>array('title'=>'Promociones',
                            'search_promotions'=>'Buscar promociones',
                            'search'=>'buscar',
                            'create_promo'=>'crear promo',
                            'placeholder_search'=>'Nombre de promoción, codigo',
                            'actives'=>'activas',
                            'active'=>'activo',
                            'finished'=>'finalizada',
                            'future'=>'futura',
                            'pendient'=>'pendiente',
                            'pendient2'=>'pendiente',
                            'left'=>'restantes',
                            'all'=>'todas',
                            'promotion'=>'Promoción',
                            'quantity'=>'Cantidad',
                            'begin_end'=>'Inicio / Fin',
                            'events'=>'Eventos',
                            'points'=>'Puntos',
                            'actions'=>'Acciones',
                            'download'=>'Descargar',
                            'edit'=>'Editar',
                            'delete'=>'Eliminar',
                            'save_success_message' => 'Los cambios han sido guardados exitosamente',
                            'save_error_message' => 'Hubo un problema guardando los cambios, int&eacute;ntelo m&aacute;s tarde',
                            'delete_success_message'=>'Hubo un problema al borrar el producto, intentelo más tade',
                            'yes'=>'Si',
                            'not'=>'No',
                            'not2'=>'No',
                            'total'=>'Total',
                            'used'=>'Usados',
                            'end_day'=>'Fin del día',
                            'days'=>'día(s)',
                            'intern_code'=>'Código Interno',
                            'publish'=>'Publicar',
                            'symbol_question'=>'(?)',
                            'promotions_type'=>'Tipo de promoción',
                            'generic'=>'Genérica',
                            'by_events'=>'Por eventos',
                            'just_for_you'=>'Sólo para tí',
                            'first_buy'=>'Primera compra',
                            'increment_frecuency'=>'Incrementar frecuencia de compra',
                            'increment_ticket'=>'Incrementar ticket de compra',
                            'birthday'=>'Cumpleaños',
                            'if_buy'=>'Si compra menos de',
                            'money_currency'=>'S/.',
                            'by'=>'por',
                            'week'=>'Semana',
                            'times_by'=>'veces',
                            'launch_promo'=>'Lanzar promo al menos',
                            'before_days'=>'días antes de finalizar la',
                            'require_points'=>'Requiere puntos',
                            'benefits'=>'Beneficio',
                            'discount'=>'Descuento',
                            'estimated_savings'=>'Ahorro estimado',
                            'quantity_available'=>'Cantidad disponible',
                            'units'=>'unidades',
                            'quantity_ilimited'=>'cantidad ilimitada',
                            'question_promo'=>'Esta promo puede ser canjeada por el mismo cliente mas de una vez?',
                            'until'=>'hasta',
                            'product_photo'=>'Foto del producto',
                            'photo_dimentions'=>'(410px X 245px)',
                            'sales_argument'=>'Argumento de venta',
                            'max_letters100'=>'(max 100 letras)',
                            'max_letters250'=>'(max 250 letras)',
                            'description'=>'Descripción',
                            'conditions'=>'Condiciones',
                            'available'=>'Disponibilidad',
                            'monday_to_friday'=>'De Lunes a Viernes',
                            'example_horary'=>'(ej. 10am - 8.30pm)',
                            'chars'=>'caracteres',
                            'saturdays'=>'Sábados',
                            'sunday'=>'Domingos',
                            'holidays'=>'Feriados',
                            'show_qr'=>'Mostrar código QR',
                            'autogenerate_code'=>'El código autogenerado es:',
                            'save_changes'=>'guardar cambios',
                            'see_statistics'=>'Ver estadísticas',
                            'preview'=>'Preview',
                            'without_publish'=>'SIN PUBLICAR',
                            'published'=>'PUBLICADO',
                            'get_promo'=>'Conseguir Promo',
                            'message_promo'=>'Una vez que obtengas esta promo tendrás hasta 24 horas para canjearlo',
                            'week'=>'Semana',
                            'month'=>'Mes',
                            'display'=>'Mostrar',
                            'records_per_page'=>'registros por página',
                            'zero_records'=>'Ningúna promoción encontrada',
                            'showing_page'=>'Mostrando página',
                            'of'=>'de',
                            'info_empty'=>'Ningúna promoción disponible',
                            'filtered'=>'Filtrado de',
                            'total_records'=>'registros en total',
                            'page'=>'Página',
                            'add_promotion'=>'Agregar Promoción',
                            'start_dt'=>'Fecha Inicial',
                            'end_dt'=>'Fecha Final',
                            'code_language'=>'es',
                            'status'=>'Estado',
                            'excel'=>array(
                                            'setCreator'=>'Promoziti',
                                            'setLastModifiedBy'=>'Promoziti',
                                            'setTitle'=>'Promociones',
                                            'setSubject'=>'Lista de Promociones',
                                            'setDescription'=>'Lista de Promociones',
                                            'setKeywords'=>'promociones',
                                            'setCategory'=>'promociones',
                                            'ActiveSheetsetTitle'=>'Promociones',
                                            'filename'=>'promociones.xlsx'
                                )
                            ),


            'customers'=>array('title'=>'Clientes',
                            'search_customer'=>'Buscar cliente',
                            'search'=>'buscar',
                            'placeholder_search'=>'Nombre, apellido, DNI',
                            'name'=>'Nombre',
                            'surname'=>'Apellido',
                            'ubication'=>'Ubicación',
                            'reserved'=>'Reservados',
                            'interchanged'=>'Canjeados',
                            'total_spent'=>'Total gastado',
                            'actions'=>'Acciones',
                            'download'=>'Descargar',
                            'see_map'=>'Ver en mapa',
                            'see'=>'Ver',
                            'display'=>'Mostrar',
                            'records_per_page'=>'registros por página',
                            'zero_records'=>'Ningúna cliente encontrada',
                            'showing_page'=>'Mostrando página',
                            'of'=>'de',
                            'info_empty'=>'Ningúna cliente disponible',
                            'filtered'=>'Filtrado de',
                            'total_records'=>'registros en total',
                            'page'=>'Página',
                            'date_of_birth'=>'Fecha de nacimiento',
                            'gender'=>'Género',
                            'city'=>'Ciudad',
                            'state'=>'Estado',
                            'zipcode'=>'Código Postal',
                            'country'=>'País',
                            'customers_vip'=>'Cliente VIP',
                            'customers_potential'=>'Potencial VIP',
                            'amount_consumer'=>'Monto de consumos',
                            'number_promotions'=>'Número de Promociones',
                            'time'=>'Tiempo',
                            'total_reserved'=>'Total reservado',
                            'total_interchanged'=>'Total canjeado',
                            'probability_interchanged'=>'Probabilidad de canje',
                            'back'=>'Volver',
                            'preferences_profile'=>'Perfil de preferencias',
                            'hystory_interchanged_promo'=>'Canje de promociones',
                            'history_purchases'=>'Compras',
                            'completed'=>'completado',
                            'download_data'=>'Descargar data',
                            'save_success_message' => 'Los cambios han sido guardados exitosamente',
                            'save_error_message' => 'Hubo un problema guardando los cambios, int&eacute;ntelo m&aacute;s tarde',
                            'subtitle_chart_number'=>'Historial de canje de promociones (numero vs tiempo)',
                            'subtitle_chart_amount'=>'Historial de compras (monto vs tiempo)',
                            'excel'=>array(
                                            'setCreator'=>'Promoziti',
                                            'setLastModifiedBy'=>'Promoziti',
                                            'setTitle'=>'Clientes',
                                            'setSubject'=>'Lista de Clientes',
                                            'setDescription'=>'Lista de Clientes',
                                            'setKeywords'=>'clientes',
                                            'setCategory'=>'clientes',
                                            'ActiveSheetsetTitle'=>'Clientes',
                                            'filename'=>'clientes.xlsx'
                                )
                            ),
            'sales'=>array('title'=>'Ventas',
                            'search_sales'=>'Buscar transacciones de ventas',
                            'search'=>'buscar',
                            'placeholder_search'=>'Nombre, apellido, DNI, nombre de producto, número de transacción,nombre staff',
                            'transactions'=>'Transacción',
                            'datetime'=>'Fecha / Hora',
                            'customer'=>'Cliente',
                            'amount'=>'Monto',
                            'products'=>'Productos',
                            'aproved_by'=>'Aprobado por',
                            'actions'=>'Acciones',
                            'download'=>'Descargar',
                            'see'=>'Ver'
                            ),
            'sales_detail'=>array('title'=>'Detalle de Venta',
                            'num_sale'=>'# 0000001',
                            'branch'=>'Sucursal Real plaza',
                            'name'=>'Julia Smith',
                            'price'=>'S/. 38.50',
                            'birth'=>'Fecha de cumpleaños',
                            'birthday'=>'03/04/1984',
                            'gender'=>'Género',
                            'gender_value'=>'Masculino',
                            'city'=>'Ciudad',
                            'city_value'=>'Lima',
                            'state'=>'Provincia',
                            'state_value'=>'Lima',
                            'zipcode'=>'Código Postal',
                            'zipcode_value'=>'17',
                            'country'=>'País',
                            'country_value'=>'Perú',
                            'aproved_by'=>'Aprobado por',
                            'aproved_by_value'=>'Jorgue Dominguez',
                            'return_back'=>'Volver',
                            'reminder'=>'Productos en esta venta'
                            ),
            'staff'=>array('title'=>'Staff',
                            'search_staff'=>'Buscar staff',
                            'search'=>'buscar',
                            'placeholder_search'=>'Nombre, staff, nombre de sucursal',
                            'create_account'=>'crear cuenta',
                            'staff'=>'Staff',
                            'branch'=>'Sucursal',
                            'last_transaction'=>'Ultima transaccion',
                            'actions'=>'Acciones',
                            'download'=>'Descargar',
                            'see'=>'Ver',
                            'edit'=>'Editar',
                            'display'=>'Mostrar',
                            'records_per_page'=>'registros por página',
                            'zero_records'=>'Ningún staff encontrado',
                            'showing_page'=>'Mostrando página',
                            'of'=>'de',
                            'info_empty'=>'Ningún staff disponible',
                            'filtered'=>'Filtrado de',
                            'total_records'=>'registros en total',
                            'page'=>'Página',
                            'create_account'=>'Crear cuenta',
                            'principal'=>'Datos principales',
                            'name'=>'Nombre',
                            'placeholder_name'=>'Juan',
                            'surname'=>'Apellido',
                            'placeholder_surname'=>'Dominguez',
                            'email'=>'Email',
                            'placeholder_email'=>'jdominguez@elchinito.pe',
                            'phone'=>'Teléfono de contacto',
                            'placeholder_phone'=>'454-5445',
                            'account_type'=>'Tipo de cuenta',
                            'administrative'=>'Administrativo',
                            'operative'=>'Operativo',
                            'note1'=>'* Administradores tambien pueden realizar labores operativas como por ejemplo validar una venta',
                            'note2'=>'* Staff operativo '. html_entity_decode('<bold>').'no tiene acceso'.html_entity_decode('</bold>').' al panel empresarial',
                            'change_password'=>'Cambiar contraseña',
                            'yes'=>'Si',
                            'nothing'=>'No',
                            'new_password'=>'Ingresar nueva contraseña',
                            'placeholder_new_password'=>'....',
                            'state'=>'Estado',
                            'active'=>'Activo',
                            'inactive'=>'Inactivo',
                            'branches'=>'Sucursales',
                            'reminder1'=>'Miembros de tu staff deben estar asignados a por lo menos una sucursal.',
                            'reminder2'=>'En caso esta persona ya no labore en tu empresa deberás desactivar su cuenta.',
                            'reminder3'=>'Código QR para validar ventas y canje de promociones',
                            'reminder4'=>'Por medidas de seguridad, te recomendamos regenerar el codigo QR cada cierto tiempo y distribuirlo a tu staff',
                            'print'=>'Imprimir',
                            'regenerate'=>'Regenerar',
                            'save_changes'=>'Guardar cambios',
                            'return_back'=>'Volver',
                            'save_success_message' => 'Los cambios han sido guardados exitosamente',
                            'save_error_message' => 'Hubo un problema guardando los cambios, int&eacute;ntelo m&aacute;s tarde',
                            'alert_checkbox_branch'=>'Por favor seleccione al menos una sucursal',
                            'excel'=>array(
                                            'setCreator'=>'Promoziti',
                                            'setLastModifiedBy'=>'Promoziti',
                                            'setTitle'=>'Staff',
                                            'setSubject'=>'Lista de Staff',
                                            'setDescription'=>'Lista de Staff',
                                            'setKeywords'=>'staff',
                                            'setCategory'=>'staff',
                                            'ActiveSheetsetTitle'=>'Staff',
                                            'filename'=>'staff.xlsx'
                                )
                            ),
            'myaccount'=>array('title'=>'Mi cuenta',
                            'principal'=>'Datos principales',
                            'name'=>'Nombre',
                            'placeholder_name'=>'Juan',
                            'surname'=>'Apellido',
                            'placeholder_surname'=>'Dominguez',
                            'email'=>'Email',
                            'placeholder_email'=>'jdominguez@elchinito.pe',
                            'phone'=>'Teléfono de contacto',
                            'placeholder_phone'=>'454-5445',
                            'account_type'=>'Tipo de cuenta',
                            'administrative'=>'Administrativo',
                            'operative'=>'Operativo',
                            'note1'=>'* Administradores tambien pueden realizar labores operativas como por ejemplo validar una venta',
                            'note2'=>'* Staff operativo '. html_entity_decode('<bold>').'no tiene acceso'.html_entity_decode('</bold>').' al panel empresarial',
                            'change_password'=>'Cambiar contraseña',
                            'yes'=>'Si',
                            'nothing'=>'No',
                            'new_password'=>'Ingresar nueva contraseña',
                            'placeholder_new_password'=>'....',
                            'state'=>'Estado',
                            'active'=>'Activo',
                            'branches'=>'Sucursales',
                            'reminder1'=>'Miembros de tu staff deben estar asignados a por lo menos una sucursal.',
                            'reminder2'=>'En caso esta persona ya no labore en tu empresa deberás desactivar su cuenta.',
                            'reminder3'=>'Código QR para validar ventas y canje de promociones',
                            'reminder4'=>'Por medidas de seguridad, te recomendamos regenerar el codigo QR cada cierto tiempo y distribuirlo a tu staff',
                            'print'=>'Imprimir',
                            'regenerate'=>'Regenerar',
                            'save_changes'=>'Guardar cambios',
                            'return_back'=>'Volver'
                            ),
            )
    ),
));