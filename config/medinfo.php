<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Разрешения для документов Мединфо
    |--------------------------------------------------------------------------
    |
    | Разрешения определяются по битовой маске
    |
    */
    'permission' => [
        'permission_denied' => 0,
        'permission_read_report' => 1,
        'permission_edit_report' => 2,
        'permission_edit_prepared_report' => 4,
        'permission_edit_accepted_report' => 8,
        'permission_edit_approved_report' => 16,
        'permission_edit_aggregated_report' => 32,
        'permission_change_any_status' => 64,
        'permission_set_status_prepared' => 128,
        'permission_set_status_accepted_declined' => 256,
        'permission_set_status_approved' => 512,
        'permission_audit_document' => 1024,
    ],
    // Период по умолчанию
    'default_period' => '2015',
    // Альбом отчетных форм по умолчанию
    'default_album' => 1,
    // запрещенные для ролей пользователей Мединфо
    'disabled_states' => [
        '0' => "",
        '1' => "'performed', 'accepted', 'declined', 'approved'",
        '2' => "'performed', 'prepared', 'accepted', 'declined', 'approved'",
        '3' => "'performed', 'prepared', 'approved'",
        '4' => "'performed', 'prepared'",
    ],
    'control_disabled' => false,
    'miac_emails' => 'nuv@miac-io.ru,zhsa@miac-io.ru',
    'director_emails' => 'shameev@miac-io.ru,evb@miac-io.ru',
];
