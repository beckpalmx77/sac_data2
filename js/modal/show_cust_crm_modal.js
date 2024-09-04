$(document).ready(function () {
    let formData = {action: "GET_CUSTOMER_CRM", sub_action: "GET_SELECT"};
    let dataRecords = $('#TableCusCRMList').DataTable({
        'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
        'language': {
            search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'หน้าที่ _PAGE_ จาก _PAGES_',
            infoEmpty: 'ไม่มีข้อมูล',
            zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
            infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
            paginate: {
                previous: 'ก่อนหน้า',
                last: 'สุดท้าย',
                next: 'ต่อไป'
            }
        },
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': 'model/manage_cust_crm_process.php',
            'data': formData
        },
        'columns': [
            {data: 'customer_id'},
            {data: 'customer_name'},
            {data: 'select'}
        ]
    });
});

$("#TableCusCRMList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#customer_id').val(data[0]);
    $('#customer_name').val(data[1]);
    $('#SearchCusModal').modal('hide');
});
