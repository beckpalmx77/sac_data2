<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart.js with MySQL and Bootstrap</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">กราฟแสดงจำนวนสินค้าตามประเภทสินค้า</h2>

    <!-- ฟอร์มสำหรับกรองข้อมูลตามประเภทสินค้าและวันที่ -->
    <form id="filter-form" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="SKU_CAT" class="form-label">เลือกประเภทสินค้า</label>
            <input type="text" class="form-control" id="SKU_CAT" name="SKU_CAT" placeholder="ประเภทสินค้า"
                   value="ยางเล็ก">
        </div>
        <div class="col-md-4">
            <label for="doc_date_start" class="form-label">เริ่มวันที่</label>
            <input type="date" class="form-control" id="doc_date_start" name="doc_date_start">
        </div>
        <div class="col-md-4">
            <label for="doc_date_to" class="form-label">ถึงวันที่</label>
            <input type="date" class="form-control" id="doc_date_to" name="doc_date_to">
        </div>
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary mt-3">Filter</button>
        </div>
    </form>

    <!-- Canvas สำหรับแสดงกราฟ -->
    <div class="row">
        <div class="col-md-12">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>

<!-- JavaScript สำหรับแสดงผลกราฟ -->
<script>
    // ฟังก์ชั่นสำหรับดึงข้อมูลจาก PHP และแสดงกราฟ
    function fetchChartData(SKU_CAT = '', doc_date_start = '', doc_date_to = '') {
        fetch(`get_data_sale_chart.php?SKU_CAT=${SKU_CAT}&doc_date_start=${doc_date_start}&doc_date_to=${doc_date_to}`)
            .then(response => response.json())
            .then(data => {
                let labels = data.map(item => item.SKU_CAT);
                let values = data.map(item => item.total_qty);

                // สร้างหรืออัพเดทกราฟ
                const ctx = document.getElementById('myChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'จำนวนสินค้า',
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
    }

    // ฟอร์มส่งข้อมูลไปยัง PHP เมื่อกดปุ่ม Filter
    document.getElementById('filter-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const SKU_CAT = document.getElementById('SKU_CAT').value;
        const doc_date_start = document.getElementById('doc_date_start').value;
        const doc_date_to = document.getElementById('doc_date_to').value;
        fetchChartData(SKU_CAT, doc_date_start, doc_date_to);
    });

    // เรียกข้อมูลเมื่อโหลดหน้าเว็บ
    fetchChartData();

</script>

<script>
    const ctx = document.getElementById('myChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
            datasets: [{
                label: '# of Votes',
                data: [12, 19, 3, 5, 2, 3],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>








</script>

<!-- Bootstrap JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>