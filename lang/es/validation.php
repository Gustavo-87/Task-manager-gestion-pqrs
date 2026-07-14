<?php

return [
    'accepted' => 'El campo :attribute debe ser aceptado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña actual es incorrecta.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'exists' => 'El valor seleccionado para :attribute no es válido.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file' => 'El archivo :attribute no debe pesar más de :max kilobytes.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'array' => 'El campo :attribute no debe contener más de :max elementos.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe contener al menos :min elementos.',
    ],
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'mixed' => 'La contraseña debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'La contraseña debe contener al menos un número.',
        'symbols' => 'La contraseña debe contener al menos un símbolo.',
        'uncompromised' => 'Esta contraseña apareció en una filtración de datos. Elige otra contraseña.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El valor de :attribute ya está registrado.',

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'current_password' => 'contraseña actual',
        'asunto' => 'asunto',
        'descripcion' => 'descripción',
        'fecha_radicacion' => 'fecha de radicación',
        'fecha_limite_respuesta' => 'fecha límite de respuesta',
        'tipo_pqr_id' => 'tipo de PQR',
        'estado' => 'estado',
    ],
];
