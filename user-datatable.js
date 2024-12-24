jQuery(document).ready(function ($) {
    $('#user-data-table').DataTable({
        pageLength: 10,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 4] } // Disable sorting for the "User Data" and "Action" columns
        ]
    });
});
jQuery(document).ready(function ($) {
    $('#user-data-table-ajax').DataTable({
        ajax: {
            url: udt_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'udt_ajax_get_users',
                nonce: udt_ajax_object.nonce,
            },
        },
        // columns: [
        //     { data: 'ID' },
        //     { data: 'user_data', orderable: false },
        //     { data: 'role' },
        //     { data: 'post_count' },
        //     { data: 'action', orderable: false },
        // ],
        // pageLength: 10,
        // responsive: true,
        columns: [
            { data: 'ID' }, // Always hidden
            { data: 'user_data', orderable: false }, // Always visible
            { data: 'role' }, // Visible above 992px
            { data: 'post_count' }, // Visible above 992px
            { data: 'action', orderable: false }, // Always visible
        ],
        responsive: {
            breakpoints: [
                { name: 'desktop', width: Infinity },
                { name: 'tablet', width: 992 },
                { name: 'mobile', width: 576 },
            ],
            details: false,
        },
        columnDefs: [
            // Hide specific columns below 992px
            { targets: [2, 3], visible: true, responsivePriority: 2 },
            { targets: [2, 3], visible: false, responsive: { maxWidth: 991 } },
            { targets: [0], visible: false }, // ID is always hidden
        ],
        pageLength: 10,
    });
});
