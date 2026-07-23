<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IT Work Order (arka-rest-server)
    |--------------------------------------------------------------------------
    |
    | Zoom Meeting ID requests use:
    |   POST {base_url}/api/v1/zoom-meeting-requests
    |   GET  {base_url}/api/v1/zoom-meeting-requests/{it_wo_id}
    |
    | Category / subcategory are fixed on rest-server (not sent by HERO):
    |   id_kategori = 8  (ZOOM MEETING ID)
    |   id_subkat   = 35 (ROOM MEETING ID)
    |
    | All RCR details are packed into wo.issue on the IT WO side.
    |
    */
    'base_url' => env('IT_WO_BASE_URL', 'http://192.168.32.37/arka-rest-server'),
    'api_key' => env('IT_WO_API_KEY', ''),

    'category_zoom' => 8,
    'subcategory_room_meeting' => 35,
];
