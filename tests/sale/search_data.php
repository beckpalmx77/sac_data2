<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Search System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
        .card-header {
            text-align: center;
            font-weight: bold;
        }
        .sale-header {
            background-color: #f8d7da;
            color: #721c24;
            text-align: center;
            padding: 10px;
            font-weight: bold;
        }
        .yellow-header {
            background-color: #ffff00;
            text-align: center;
            font-weight: bold;
        }
        .green-header {
            background-color: #d4edda;
            text-align: center;
            font-weight: bold;
        }
        .sale-total {
            background-color: #ffff00;
            font-weight: bold;
            text-align: center;
        }
        .area-name {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            padding: 5px;
        }
        .region-title {
            color: purple;
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .ll-cell {
            background-color: #f8d7da;
        }
        .le-cell {
            background-color: #e2e3e5;
        }
        .at-cell {
            background-color: #d1ecf1;
        }
        table {
            width: 100%;
            text-align: center;
        }
        th, td {
            text-align: center;
            padding: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="region-title">SAC : PLAN 2568<br>LL / LE / AT</h2>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">ค้นหาข้อมูลยอดขาย</div>
        <div class="card-body">
            <form id="searchForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="salePerson" class="form-label">เลือก Sale:</label>
                        <select class="form-select" id="salePerson" name="salePerson">
                            <option value="">--- เลือก Sale ---</option>
                            <option value="บาส">บาส</option>
                            <option value="เมษายน">เมษายน</option>
                            <option value="กทม.3">กทม.3</option>
                            <option value="กลาง 3">กลาง 3</option>
                            <option value="ตะวันตก">ตะวันตก</option>
                            <option value="บางเคน">บางเคน</option>
                            <option value="นครปฐม">นครปฐม</option>
                            <option value="กาญจนบุรี">กาญจนบุรี</option>
                            <option value="หนองเขม">หนองเขม</option>
                            <option value="สมุทรสาคร">สมุทรสาคร</option>
                            <option value="บางบอน">บางบอน</option>
                            <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">ค้นหา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div id="searchResults" class="row"></div>
</div>

<!-- Bootstrap and jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Sample data (in a real application, this would come from MySQL)
        const salesData = {
            "กทม.3": {
                total: 2000,
                breakdown: { LL: 1000, LE: 900, AT: 100 }
            },
            "กลาง 3": {
                total: 2500,
                breakdown: { LL: 900, LE: 900, AT: 700 }
            },
            "ตะวันตก": {
                total: 500,
                breakdown: { LL: 300, LE: 200, AT: 0 }
            },
            "บางเคน": {
                total: 500,
                breakdown: { LL: 200, LE: 250, AT: 50 }
            },
            "นครปฐม": {
                total: 900,
                breakdown: { LL: 300, LE: 300, AT: 300 }
            },
            "กาญจนบุรี": {
                total: 500,
                breakdown: { LL: 300, LE: 200, AT: 0 }
            },
            "หนองเขม": {
                total: 500,
                breakdown: { LL: 250, LE: 200, AT: 50 }
            },
            "บาส": {
                total: 800,
                breakdown: { LL: 300, LE: 300, AT: 200 }
            },
            "บางบอน": {
                total: 500,
                breakdown: { LL: 300, LE: 200, AT: 0 }
            },
            "บาส": {
                total: 800,
                breakdown: { LL: 300, LE: 300, AT: 200 }
            },
            // Sample data for individual sales people
            "บาส": {
                total: 1200,
                breakdown: { LL: 600, LE: 500, AT: 100 }
            },
            "เมษายน": {
                total: 1500,
                breakdown: { LL: 700, LE: 600, AT: 200 }
            }
        };

        // Summary data
        const summaryData = {
            total: 5000,
            breakdown: { LL: 2200, LE: 2000, AT: 800 }
        };

        // Handle search form submission
        $("#searchForm").submit(function(e) {
            e.preventDefault();
            const salePerson = $("#salePerson").val();

            // Clear previous results
            $("#searchResults").empty();

            if (salePerson === "") {
                // If no selection, show all regions
                displayAllRegions();
            } else {
                // Show selected salesperson data
                if (salesData[salePerson]) {
                    displaySaleCard(salePerson, salesData[salePerson]);
                } else {
                    $("#searchResults").html('<div class="col-12"><div class="alert alert-warning">ไม่พบข้อมูลสำหรับ Sale ที่เลือก</div></div>');
                }
            }
        });

        // Function to display all regions (similar to image 2)
        function displayAllRegions() {
            // Display summary card
            const summaryCol = $('<div class="col-md-4 mb-4"></div>');
            const summaryCard = `
                    <div class="card">
                        <div class="yellow-header">
                            ${summaryData.total}
                        </div>
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th class="ll-cell">LL</th>
                                <th class="le-cell">LE</th>
                                <th class="at-cell">AT</th>
                            </tr>
                            <tr>
                                <td>${summaryData.breakdown.LL}</td>
                                <td>${summaryData.breakdown.LE}</td>
                                <td>${summaryData.breakdown.AT}</td>
                            </tr>
                        </table>
                        <div class="card-footer text-center text-danger">
                            เขตพื้นที่ดูแล
                        </div>
                    </div>
                `;
            summaryCol.html(summaryCard);
            $("#searchResults").append(summaryCol);

            // Top row - main regions
            const topRegions = ["กทม.3", "กลาง 3", "ตะวันตก"];
            topRegions.forEach(region => {
                if (salesData[region]) {
                    displayRegionCard(region, salesData[region], true);
                }
            });

            // Other regions - display in 3 columns per row
            const otherRegions = ["บางเคน", "นครปฐม", "กาญจนบุรี", "หนองเขม", "สมุทรสาคร", "บางบอน", "สุพรรณบุรี"];
            otherRegions.forEach(region => {
                if (salesData[region]) {
                    displayRegionCard(region, salesData[region], false);
                }
            });
        }

        // Function to display a region card
        function displayRegionCard(region, data, isMainRegion) {
            const bgColor = isMainRegion ? "sale-header" : "green-header";
            const col = $('<div class="col-md-4 mb-4"></div>');
            const card = `
                    <div class="card">
                        <div class="${bgColor}">
                            ${data.total}
                        </div>
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th class="ll-cell">LL</th>
                                <th class="le-cell">LE</th>
                                <th class="at-cell">AT</th>
                            </tr>
                            <tr>
                                <td>${data.breakdown.LL}</td>
                                <td>${data.breakdown.LE}</td>
                                <td>${data.breakdown.AT}</td>
                            </tr>
                        </table>
                        <div class="area-name">
                            ${region}
                        </div>
                    </div>
                `;
            col.html(card);
            $("#searchResults").append(col);
        }

        // Function to display a salesperson card
        function displaySaleCard(name, data) {
            const col = $('<div class="col-md-6 offset-md-3 mb-4"></div>');
            const card = `
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            ข้อมูลยอดขาย: ${name}
                        </div>
                        <div class="card-body">
                            <div class="sale-total">
                                ${data.total}
                            </div>
                            <table class="table table-bordered mt-3">
                                <tr>
                                    <th class="ll-cell">LL</th>
                                    <th class="le-cell">LE</th>
                                    <th class="at-cell">AT</th>
                                </tr>
                                <tr>
                                    <td>${data.breakdown.LL}</td>
                                    <td>${data.breakdown.LE}</td>
                                    <td>${data.breakdown.AT}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
            col.html(card);
            $("#searchResults").append(col);
        }

        // Initialize with all regions displayed
        displayAllRegions();
    });
</script>
</body>
</html>