<?php
include '../../config/connect_sqlserver.php';

// --- ดึง ICCAT (หมวดหมู่สินค้า)
$stmt1 = $conn_sqlsvr->prepare("SELECT DISTINCT ICCAT_CODE, ICCAT_NAME FROM ICCAT ORDER BY ICCAT_CODE ASC");
$stmt1->execute();
$iccats = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// --- ดึง BRAND (ยี่ห้อสินค้า)
$stmt2 = $conn_sqlsvr->prepare("SELECT DISTINCT BRN_CODE, BRN_NAME FROM BRAND ORDER BY BRN_CODE ASC");
$stmt2->execute();
$brands = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// --- ดึง WAREHOUSE (คลังสินค้า)
$stmt3 = $conn_sqlsvr->prepare("SELECT DISTINCT WH_CODE, WH_NAME FROM WAREHOUSE ORDER BY WH_CODE ASC");
$stmt3->execute();
$warehouses = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// --- ดึง WARELOCATION (ตำแหน่งจัดเก็บ)
$stmt4 = $conn_sqlsvr->prepare("SELECT DISTINCT WL_CODE, WL_NAME FROM WARELOCATION ORDER BY WL_CODE ASC");
$stmt4->execute();
$locations = $stmt4->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .checkbox-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .checkbox-grid .column {
        flex: 1 1 45%;
        min-width: 200px;
    }
    .checkbox-grid label {
        display: block;
        margin-bottom: 5px;
    }
    fieldset {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ccc;
    }
    legend {
        font-weight: bold;
    }
</style>

<form method="POST" action="">
    <!-- ICCAT Section -->
    <fieldset>
        <legend>เลือก ICCAT_CODE:</legend>
        <div class="checkbox-grid">
            <div class="column">
                <?php
                $half = ceil(count($iccats) / 2);
                foreach (array_slice($iccats, 0, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="icc_codes[]" value="<?= htmlspecialchars($row['ICCAT_CODE']) ?>">
                        <?= htmlspecialchars($row['ICCAT_CODE']) ?> - <?= htmlspecialchars($row['ICCAT_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="column">
                <?php foreach (array_slice($iccats, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="icc_codes[]" value="<?= htmlspecialchars($row['ICCAT_CODE']) ?>">
                        <?= htmlspecialchars($row['ICCAT_CODE']) ?> - <?= htmlspecialchars($row['ICCAT_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </fieldset>

    <!-- BRAND Section -->
    <fieldset>
        <legend>เลือก BRAND:</legend>
        <div class="checkbox-grid">
            <div class="column">
                <?php
                $half = ceil(count($brands) / 2);
                foreach (array_slice($brands, 0, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="brn_codes[]" value="<?= htmlspecialchars($row['BRN_CODE']) ?>">
                        <?= htmlspecialchars($row['BRN_CODE']) ?> - <?= htmlspecialchars($row['BRN_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="column">
                <?php foreach (array_slice($brands, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="brn_codes[]" value="<?= htmlspecialchars($row['BRN_CODE']) ?>">
                        <?= htmlspecialchars($row['BRN_CODE']) ?> - <?= htmlspecialchars($row['BRN_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </fieldset>

    <!-- WAREHOUSE Section -->
    <fieldset>
        <legend>เลือกคลังสินค้า (WAREHOUSE):</legend>
        <div class="checkbox-grid">
            <div class="column">
                <?php
                $half = ceil(count($warehouses) / 2);
                foreach (array_slice($warehouses, 0, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="wh_codes[]" value="<?= htmlspecialchars($row['WH_CODE']) ?>">
                        <?= htmlspecialchars($row['WH_CODE']) ?> - <?= htmlspecialchars($row['WH_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="column">
                <?php foreach (array_slice($warehouses, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="wh_codes[]" value="<?= htmlspecialchars($row['WH_CODE']) ?>">
                        <?= htmlspecialchars($row['WH_CODE']) ?> - <?= htmlspecialchars($row['WH_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </fieldset>

    <!-- WARELOCATION Section -->
    <fieldset>
        <legend>เลือกตำแหน่งจัดเก็บ (WARELOCATION):</legend>
        <div class="checkbox-grid">
            <div class="column">
                <?php
                $half = ceil(count($locations) / 2);
                foreach (array_slice($locations, 0, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="wl_codes[]" value="<?= htmlspecialchars($row['WL_CODE']) ?>">
                        <?= htmlspecialchars($row['WL_CODE']) ?> - <?= htmlspecialchars($row['WL_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="column">
                <?php foreach (array_slice($locations, $half) as $row): ?>
                    <label>
                        <input type="checkbox" name="wl_codes[]" value="<?= htmlspecialchars($row['WL_CODE']) ?>">
                        <?= htmlspecialchars($row['WL_CODE']) ?> - <?= htmlspecialchars($row['WL_NAME']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </fieldset>

    <button type="submit">ค้นหา</button>
</form>
