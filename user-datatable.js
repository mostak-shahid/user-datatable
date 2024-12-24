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


        // columns: [
        //     { data: 'ID' }, // Always hidden
        //     { data: 'user_data', orderable: false }, // Always visible
        //     { data: 'role' }, // Visible above 992px
        //     { data: 'post_count' }, // Visible above 992px
        //     { data: 'action', orderable: false }, // Always visible
        // ],
        // responsive: {
        //     breakpoints: [
        //         { name: 'desktop', width: Infinity },
        //         { name: 'tablet', width: 992 },
        //         { name: 'mobile', width: 576 },
        //     ],
        //     details: false,
        // },
        // columnDefs: [
        //     // Hide specific columns below 992px
        //     { targets: [2, 3], visible: true, responsivePriority: 2 },
        //     { targets: [2, 3], visible: false, responsive: { maxWidth: 991 } },
        //     // { targets: [0], visible: false }, // ID is always hidden
        // ],
        // pageLength: 10,

        columns: [
            { data: 'ID' }, // Hidden on mobile, shown in details
            { data: 'user_data', orderable: false }, // Always visible
            { data: 'role' }, // Hidden on mobile, shown in details
            { data: 'post_count' }, // Hidden on mobile, shown in details
            { data: 'action', orderable: false }, // Always visible
        ],
        responsive: {
            details: {
                renderer: function (api, rowIdx, columns) {
                    var data = $.map(columns, function (col, i) {
                        return col.hidden
                            ? '<tr data-dt-row="' +
                                  col.rowIndex +
                                  '" data-dt-column="' +
                                  col.columnIndex +
                                  '">' +
                                  '<td>' +
                                  col.title +
                                  ':' +
                                  '</td> ' +
                                  '<td>' +
                                  col.data +
                                  '</td>' +
                                  '</tr>'
                            : '';
                    }).join('');

                    return data ? $('<table/>').append(data) : false;
                },
            },
        },
        columnDefs: [
            { targets: [0], visible: false }, // ID is always hidden by default
            { className: 'control', targets: 1 }, // Add "+" button to "User Data" column
        ],
        pageLength: 10,
    });
});
