<?php
    require 'config/connect_db.php'; // เชื่อมต่อฐานข้อมูล
?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="img/favicon.ico" type="image/x-icon">
        <link href="../img/logo/logo.png" rel="icon">
        <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body>
    <div class="container-fluid" id="container-wrapper">
        <h2>Manage LINE API</h2>
        <a href="Dashboard_admin" class="btn btn-outline-danger mb-3">Back to Dashboard</a>
        <form id="alineForm">
            <input type="hidden" id="id" name="id">
            <div class="mb-3">
                <label for="line_api_token" class="form-label">LINE API Token</label>
                <textarea id="line_api_token" name="line_api_token" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="doc_type" class="form-label">Document Type</label>
                <input type="text" id="doc_type" name="doc_type" class="form-control">
            </div>
            <div class="mb-3">
                <label for="detail" class="form-label">Detail</label>
                <input type="text" id="detail" name="detail" class="form-control">
            </div>
            <button type="button" id="insert" class="btn btn-success">Insert</button>
            <button type="button" id="update" class="btn btn-warning">Update</button>
        </form>
        <table class="table table-bordered mt-4">
            <thead>
            <tr>
                <th>ID</th>
                <th>Token</th>
                <th>Document Type</th>
                <th>Detail</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="dataBody"></tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            loadData();

            $('#insert').click(function () {
                $.post('model/aline_api_crud.php', $('#alineForm').serialize() + '&action=insert', function () {
                    loadData();
                    $('#alineForm')[0].reset();
                });
            });

            $('#update').click(function () {
                $.post('model/aline_api_crud.php', $('#alineForm').serialize() + '&action=update', function () {
                    loadData();
                    $('#alineForm')[0].reset();
                });
            });

            $(document).on('click', '.edit', function () {
                let row = $(this).closest('tr');
                $('#id').val(row.find('.id').text());
                $('#line_api_token').val(row.find('.token').text());
                $('#doc_type').val(row.find('.doc_type').text());
                $('#detail').val(row.find('.detail').text());
            });

            $(document).on('click', '.delete', function () {
                let id = $(this).data('id');
                if (confirm('Delete this record?')) {
                    $.post('model/aline_api_crud.php', {id: id, action: 'delete'}, function () {
                        loadData();
                    });
                }
            });
        });

        function loadData() {
            $.get('model/aline_api_crud.php', function (data) {
                $('#dataBody').html(data);
            });
        }
    </script>
    </body>
    </html>