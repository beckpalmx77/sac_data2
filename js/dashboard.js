let dailyChart = null;
let monthlyChart = null;

function loadDailyChart() {
    $.getJSON('../ajax/chart_daily.php', function (data) {

        let labels = [];
        let totals = [];

        data.forEach(row => {
            labels.push(row.SLMN_NAME);
            totals.push(row.total);
        });

        if (dailyChart) dailyChart.destroy();

        dailyChart = new Chart($('#myChartDaily'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอดขายรายวัน',
                    data: totals,
                    backgroundColor: '#24c9f1'
                }]
            }
        });
    });
}

function loadMonthlyChart() {
    $.getJSON('../ajax/chart_monthly.php', function (data) {

        let labels = [];
        let totals = [];

        data.forEach(row => {
            labels.push(row.SLMN_NAME);
            totals.push(row.total);
        });

        if (monthlyChart) monthlyChart.destroy();

        monthlyChart = new Chart($('#myChartMonthly'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอดขายรายเดือน',
                    data: totals,
                    backgroundColor: '#285bfa'
                }]
            }
        });
    });
}

function loadTables() {
    $('#daily-table').load('../ajax/table_daily.php');
    $('#monthly-table').load('../ajax/table_monthly.php');
}

// โหลดครั้งแรก
loadDailyChart();
loadMonthlyChart();
loadTables();

// รีเฟรชทุก 60 วินาที
setInterval(function () {
    loadDailyChart();
    loadMonthlyChart();
    loadTables();
}, 60000);
