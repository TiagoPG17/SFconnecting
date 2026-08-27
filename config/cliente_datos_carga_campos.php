<?php

declare(strict_types=1);

// Campos del perfil de un cliente nuevo que trae el archivo plano al convertir
// un prospecto en cliente. Cada registro (fila) del archivo es la ficha completa
// de UN cliente (no una lista de empleados); si el archivo trae varios
// registros, la pantalla de conversión los muestra como pestañas para revisar
// uno por uno. 'sinonimos' se usa para reconocer la columna aunque el
// encabezado del archivo no coincida exactamente (sin tildes, mayúsculas,
// espacios o guiones bajos).
return [
    ['key' => 'ejecutivo_comercial', 'label' => 'Ejecutivo comercial a cargo', 'tipo' => 'text', 'sinonimos' => ['ejecutivo comercial a cargo', 'ejecutivo comercial', 'comercial a cargo', 'ejecutivo a cargo']],
    ['key' => 'razon_social', 'label' => 'Razón social', 'tipo' => 'text', 'sinonimos' => ['razon social', 'nombre cliente', 'nombre del cliente']],
    ['key' => 'nit', 'label' => 'NIT (sin dígito de verificación)', 'tipo' => 'text', 'sinonimos' => ['nit sin digito de verificacion', 'nit sin dv', 'nit']],
    ['key' => 'telefono_corporativo', 'label' => 'Teléfono corporativo (celular o fijo)', 'tipo' => 'text', 'sinonimos' => ['telefono corporativo celular o fijo', 'telefono corporativo', 'telefono']],
    ['key' => 'extension', 'label' => 'Extensión', 'tipo' => 'text', 'sinonimos' => ['extension', 'ext']],
    ['key' => 'email', 'label' => 'E-mail', 'tipo' => 'email', 'sinonimos' => ['e mail', 'email', 'correo', 'correo electronico']],
    ['key' => 'direccion_correspondencia', 'label' => 'Dirección de correspondencia', 'tipo' => 'text', 'sinonimos' => ['direccion de correspondencia', 'direccion correspondencia']],
    ['key' => 'ciudad_correspondencia', 'label' => 'Ciudad (correspondencia)', 'tipo' => 'text', 'sinonimos' => ['ciudad correspondencia', 'ciudad de correspondencia']],
    ['key' => 'direccion_despacho', 'label' => 'Dirección de despacho', 'tipo' => 'text', 'sinonimos' => ['direccion de despacho', 'direccion despacho']],
    ['key' => 'ciudad_despacho', 'label' => 'Ciudad (despacho)', 'tipo' => 'text', 'sinonimos' => ['ciudad despacho', 'ciudad de despacho']],
    ['key' => 'gran_contribuyente', 'label' => 'Gran contribuyente (Sí / No)', 'tipo' => 'text', 'sinonimos' => ['gran contribuyente si no', 'gran contribuyente']],
    ['key' => 'autorretenedor', 'label' => 'Autorretenedor (Sí / No)', 'tipo' => 'text', 'sinonimos' => ['autorretenedor si no', 'autorretenedor', 'auto retenedor']],
    ['key' => 'contactos_pago', 'label' => 'Contactos para información de pago', 'tipo' => 'text', 'sinonimos' => ['contactos para informacion de pago', 'contactos de pago', 'contacto de pago']],
    ['key' => 'telefonos_contacto_pago', 'label' => 'Teléfonos de contacto de pago', 'tipo' => 'text', 'sinonimos' => ['telefonos de contacto de pago', 'telefono contacto de pago', 'telefonos contacto pago']],
    ['key' => 'fecha_cierre_facturacion', 'label' => 'Fecha de cierre de facturación', 'tipo' => 'text', 'sinonimos' => ['fecha de cierre de facturacion', 'fecha cierre facturacion', 'cierre de facturacion']],
    ['key' => 'lapso_entrega_min_dias', 'label' => 'Lapso de entrega – días mínimos', 'tipo' => 'text', 'sinonimos' => ['lapso de entrega dias minimos', 'dias minimos de entrega', 'lapso entrega minimo']],
    ['key' => 'lapso_entrega_max_dias', 'label' => 'Lapso de entrega – días máximos', 'tipo' => 'text', 'sinonimos' => ['lapso de entrega dias maximos', 'dias maximos de entrega', 'lapso entrega maximo']],
    ['key' => 'horario_recepcion_mercancia', 'label' => 'Horario y días para recepción de mercancía', 'tipo' => 'text', 'sinonimos' => ['horario y dias para recepcion de mercancia', 'horario recepcion mercancia', 'horario de recepcion']],
    ['key' => 'correo_facturacion_electronica', 'label' => 'Correo para facturación electrónica', 'tipo' => 'email', 'sinonimos' => ['correo para facturacion electronica', 'correo facturacion electronica', 'email facturacion electronica']],
];
