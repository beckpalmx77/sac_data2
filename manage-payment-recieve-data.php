<?php

include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $curr_date = date("d-m-Y");
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการรับชำระเงิน (Payment Receive)' ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo isset($_GET['m']) ? urldecode($_GET['m']) : 'Payment' ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการรับชำระเงิน' ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form id="export_data" method="post"
                                              action="export_process/payment_export_data.php"
                                              enctype="multipart/form-data">
                                            <div class="col-md-12 col-md-offset-2"
                                                 style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                <label for="name_t"
                                                       class="control-label"><b>ข้อมูล <?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการรับชำระเงิน' ?></b></label>

                                                <button type="button" name="btnRefresh" id="btnRefresh"
                                                        class="btn btn-success btn-xs" onclick="ReloadDataTable();">
                                                    Refresh <i class="fa fa-refresh"></i>
                                                </button>

                                                <label for="doc_date_start" class="control-label mb-0"><b>วันที่&nbsp;</b></label>

                                                <input type="text" class="form-control" id="doc_date_start"
                                                       name="doc_date_start"
                                                       readonly="true" placeholder="เริ่มต้น"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="">
                                                <label for="doc_date_to" class="control-label mb-0"><b>-</b></label>
                                                <input type="text" class="form-control" id="doc_date_to"
                                                       name="doc_date_to"
                                                       readonly="true" placeholder="สิ้นสุด"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="">

                                                <button type="button" name="btnFilter" id="btnFilter"
                                                        class="btn btn-primary btn-xs">
                                                    Filter <i class="fa fa-filter"></i>
                                                </button>

                                                <button type="button" name="btnExport" id="btnExport"
                                                        class="btn btn-success btn-xs" onclick="ExportData();">
                                                    Export <i class="fa fa-file-excel-o"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <br>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>ARCAT_CODE</th>
                                                    <th>ARCAT_NAME</th>
                                                    <th>AR_CODE</th>
                                                    <th>AR_NAME</th>
                                                    <th>DT_DOCCODE</th>
                                                    <th>DT_THAIDESC</th>
                                                    <th>DI_DATE</th>
                                                    <th>DI_REF</th>
                                                    <th>ARD_DATE</th>
                                                    <th>ARD_REF</th>
                                                    <th>ARD_A_AMT</th>
                                                    <th>ARP_CASH_DSC</th>
                                                    <th>ARP_WH_TAX</th>
                                                    <th>ARP_CASH</th>
                                                    <th>ARP_CQ_AMT</th>
                                                    <th>ARP_TRANSF</th>
                                                    <th>ARP_OTHER</th>
                                                    <th>ARP_SHRT</th>
                                                    <th>ARP_FEE</th>
                                                    <th>ARP_INT</th>
                                                    <th>ARP_LOSTXCHG</th>
                                                    <th>ARP_DN</th>
                                                    <th>ARP_CN</th>
                                                    <th>ARP_OVER</th>
                                                    <th>CASHAC_CODE</th>
                                                    <th>BNKAC_CODE</th>
                                                    <th>BANK_INITIAL</th>
                                                    <th>CQ_CHEQUE_NO</th>
                                                    <th>CQ_CHEQUE_DD</th>
                                                    <th>PMT_NAME</th>
                                                    <th>ARP_SOURCE</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ARCAT_CODE</th>
                                                    <th>ARCAT_NAME</th>
                                                    <th>AR_CODE</th>
                                                    <th>AR_NAME</th>
                                                    <th>DT_DOCCODE</th>
                                                    <th>DT_THAIDESC</th>
                                                    <th>DI_DATE</th>
                                                    <th>DI_REF</th>
                                                    <th>ARD_DATE</th>
                                                    <th>ARD_REF</th>
                                                    <th>ARD_A_AMT</th>
                                                    <th>ARP_CASH_DSC</th>
                                                    <th>ARP_WH_TAX</th>
                                                    <th>ARP_CASH</th>
                                                    <th>ARP_CQ_AMT</th>
                                                    <th>ARP_TRANSF</th>
                                                    <th>ARP_OTHER</th>
                                                    <th>ARP_SHRT</th>
                                                    <th>ARP_FEE</th>
                                                    <th>ARP_INT</th>
                                                    <th>ARP_LOSTXCHG</th>
                                                    <th>ARP_DN</th>
                                                    <th>ARP_CN</th>
                                                    <th>ARP_OVER</th>
                                                    <th>CASHAC_CODE</th>
                                                    <th>BNKAC_CODE</th>
                                                    <th>BANK_INITIAL</th>
                                                    <th>CQ_CHEQUE_NO</th>
                                                    <th>CQ_CHEQUE_DD</th>
                                                    <th>PMT_NAME</th>
                                                    <th>ARP_SOURCE</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!-- Modal แสดงรายละเอียดข้อมูลการรับชำระเงิน -->
                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="fa fa-info-circle"></i> รายละเอียดข้อมูลการรับชำระเงิน</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="container-fluid">
                                                                <!-- กลุ่มฟอร์มที่ 1: ข้อมูลเอกสารรับชำระ -->
                                                                <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-file-text-o"></i> 1. ข้อมูลเอกสารรับชำระ</h6>
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_DATE" class="control-label">วันที่เอกสารรับ (DI_DATE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_DATE" name="DI_DATE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_REF" class="control-label">เลขที่เอกสารรับ (DI_REF)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_REF" name="DI_REF" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="DT_DOCCODE" class="control-label">รหัสประเภทเอกสาร (DT_DOCCODE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DT_DOCCODE" name="DT_DOCCODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="DT_THAIDESC" class="control-label">ชื่อประเภทเอกสาร (DT_THAIDESC)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DT_THAIDESC" name="DT_THAIDESC" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="ARP_SOURCE_DESC" class="control-label">แหล่งที่มา (ARP_SOURCE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ARP_SOURCE_DESC" name="ARP_SOURCE_DESC" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <hr class="mt-2 mb-3">

                                                                <!-- กลุ่มฟอร์มที่ 2: ข้อมูลลูกค้า -->
                                                                <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-user"></i> 2. ข้อมูลลูกค้า</h6>
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="AR_CODE" class="control-label">รหัสลูกค้า (AR_CODE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="AR_CODE" name="AR_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <div class="form-group">
                                                                            <label for="AR_NAME" class="control-label">ชื่อลูกค้า (AR_NAME)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="AR_NAME" name="AR_NAME" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="ARCAT_CODE" class="control-label">รหัสกลุ่มลูกค้า (ARCAT_CODE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ARCAT_CODE" name="ARCAT_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="ARCAT_NAME" class="control-label">ชื่อกลุ่มลูกค้า (ARCAT_NAME)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ARCAT_NAME" name="ARCAT_NAME" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <hr class="mt-2 mb-3">

                                                                <!-- กลุ่มฟอร์มที่ 3: ข้อมูลเอกสารตั้งหนี้ / อ้างอิง -->
                                                                <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-list-alt"></i> 3. ข้อมูลเอกสารตั้งหนี้ / อ้างอิง</h6>
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARD_DATE" class="control-label">วันที่เอกสารตั้งหนี้ (ARD_DATE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ARD_DATE" name="ARD_DATE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARD_REF" class="control-label">เลขที่เอกสารตั้งหนี้ (ARD_REF)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ARD_REF" name="ARD_REF" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="ARD_A_AMT" class="control-label">มูลค่าหนี้ (ARD_A_AMT)</label>
                                                                            <input type="text" class="form-control font-weight-bold text-right"
                                                                                   id="ARD_A_AMT" name="ARD_A_AMT" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="ARP_CASH_DSC" class="control-label">ส่วนลดเงินสด (ARP_CASH_DSC)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_CASH_DSC" name="ARP_CASH_DSC" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="ARP_WH_TAX" class="control-label">ภาษีหัก ณ ที่จ่าย (ARP_WH_TAX)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_WH_TAX" name="ARP_WH_TAX" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <hr class="mt-2 mb-3">

                                                                <!-- กลุ่มฟอร์มที่ 4: รายละเอียดการรับชำระเงิน -->
                                                                <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-money"></i> 4. ช่องทางการรับชำระเงิน</h6>
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_CASH" class="control-label">ยอดเงินสด (ARP_CASH)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_CASH" name="ARP_CASH" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="CASHAC_CODE" class="control-label">รหัสสมุดเงินสด (CASHAC_CODE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CASHAC_CODE" name="CASHAC_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_TRANSF" class="control-label">ยอดเงินโอน (ARP_TRANSF)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_TRANSF" name="ARP_TRANSF" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BNKAC_CODE" class="control-label">รหัสบัญชีธนาคาร (BNKAC_CODE)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BNKAC_CODE" name="BNKAC_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_CQ_AMT" class="control-label">ยอดเช็ค (ARP_CQ_AMT)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_CQ_AMT" name="ARP_CQ_AMT" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BANK_INITIAL" class="control-label">รหัสธนาคารเช็ค (BANK_INITIAL)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BANK_INITIAL" name="BANK_INITIAL" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="CQ_CHEQUE_NO" class="control-label">เลขที่เช็ค (CQ_CHEQUE_NO)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQ_CHEQUE_NO" name="CQ_CHEQUE_NO" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="CQ_CHEQUE_DD" class="control-label">วันที่เช็ค (CQ_CHEQUE_DD)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQ_CHEQUE_DD" name="CQ_CHEQUE_DD" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="ARP_OTHER" class="control-label">ยอดชำระอื่นๆ (ARP_OTHER)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_OTHER" name="ARP_OTHER" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="PMT_NAME" class="control-label">ประเภทการชำระอื่นๆ (PMT_NAME)</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="PMT_NAME" name="PMT_NAME" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <hr class="mt-2 mb-3">

                                                                <!-- กลุ่มฟอร์มที่ 5: รายการปรับปรุง / อื่นๆ -->
                                                                <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-calculator"></i> 5. รายการปรับปรุง / ผลต่าง</h6>
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_DN" class="control-label">เพิ่มหนี้ (ARP_DN)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_DN" name="ARP_DN" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_CN" class="control-label">ลดหนี้ (ARP_CN)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_CN" name="ARP_CN" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_OVER" class="control-label">รับเกิน (ARP_OVER)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_OVER" name="ARP_OVER" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="ARP_SHRT" class="control-label">จ่ายขาด (ARP_SHRT)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_SHRT" name="ARP_SHRT" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="ARP_FEE" class="control-label">ค่าธรรมเนียม (ARP_FEE)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_FEE" name="ARP_FEE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="ARP_INT" class="control-label">ดอกเบี้ย (ARP_INT)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_INT" name="ARP_INT" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="ARP_LOSTXCHG" class="control-label">ผลต่างอัตราแลกเปลี่ยน (ARP_LOSTXCHG)</label>
                                                                            <input type="text" class="form-control text-right"
                                                                                   id="ARP_LOSTXCHG" name="ARP_LOSTXCHG" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>    <?php
    include('includes/Footer.php');
    ?>

                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    ?>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <style>
        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }

        .form-control {
            height: calc(1.5em + 0.75rem + 2px);
        }

        .select2-container .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem + 2px) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem + 2px) !important;
        }

        #TableRecordList th, #TableRecordList td {
            white-space: nowrap;
        }
    </style>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_start').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            $('#doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        let dataRecords;
        $(document).ready(function () {
            dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
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
                'order': [[6, 'desc']],
                'processing': true,
                'serverSide': true,
                'autoWidth': true,
                'searching': true,
                'scrollX': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_payment_recieve_data_process.php',
                    'data': function (d) {
                        d.action = "GET_PAYMENT_RECIEVE_DATA";
                        d.doc_date_start = $('#doc_date_start').val();
                        d.doc_date_to = $('#doc_date_to').val();
                    }
                },
                'columns': [
                    {data: 'ARCAT_CODE', className: 'text-left'},
                    {data: 'ARCAT_NAME', className: 'text-left'},
                    {data: 'AR_CODE', className: 'text-left'},
                    {data: 'AR_NAME', className: 'text-left'},
                    {data: 'DT_DOCCODE', className: 'text-left'},
                    {data: 'DT_THAIDESC', className: 'text-left'},
                    {data: 'DI_DATE', className: 'text-left'},
                    {data: 'DI_REF', className: 'text-left'},
                    {data: 'ARD_DATE', className: 'text-left'},
                    {data: 'ARD_REF', className: 'text-left'},
                    {data: 'ARD_A_AMT', className: 'text-right font-weight-bold'},
                    {data: 'ARP_CASH_DSC', className: 'text-right'},
                    {data: 'ARP_WH_TAX', className: 'text-right'},
                    {data: 'ARP_CASH', className: 'text-right'},
                    {data: 'ARP_CQ_AMT', className: 'text-right'},
                    {data: 'ARP_TRANSF', className: 'text-right'},
                    {data: 'ARP_OTHER', className: 'text-right'},
                    {data: 'ARP_SHRT', className: 'text-right'},
                    {data: 'ARP_FEE', className: 'text-right'},
                    {data: 'ARP_INT', className: 'text-right'},
                    {data: 'ARP_LOSTXCHG', className: 'text-right'},
                    {data: 'ARP_DN', className: 'text-right'},
                    {data: 'ARP_CN', className: 'text-right'},
                    {data: 'ARP_OVER', className: 'text-right'},
                    {data: 'CASHAC_CODE', className: 'text-left'},
                    {data: 'BNKAC_CODE', className: 'text-left'},
                    {data: 'BANK_INITIAL', className: 'text-left'},
                    {data: 'CQ_CHEQUE_NO', className: 'text-left'},
                    {data: 'CQ_CHEQUE_DD', className: 'text-left'},
                    {data: 'PMT_NAME', className: 'text-left'},
                    {data: 'ARP_SOURCE', className: 'text-left'},
                    {data: 'update', className: 'text-center'}
                ]
            });

            $('#btnFilter').click(function () {
                dataRecords.ajax.reload();
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.info', function () {
            let infoAttr = $(this).attr("data-info");
            if (infoAttr) {
                try {
                    let data = JSON.parse(infoAttr);
                    $('#DI_DATE').val(data.DI_DATE || '');
                    $('#DI_REF').val(data.DI_REF || '');
                    $('#DT_DOCCODE').val(data.DT_DOCCODE || '');
                    $('#DT_THAIDESC').val(data.DT_THAIDESC || '');
                    $('#ARP_SOURCE_DESC').val(data.ARP_SOURCE_DESC || '');

                    $('#AR_CODE').val(data.AR_CODE || '');
                    $('#AR_NAME').val(data.AR_NAME || '');
                    $('#ARCAT_CODE').val(data.ARCAT_CODE || '');
                    $('#ARCAT_NAME').val(data.ARCAT_NAME || '');

                    $('#ARD_DATE').val(data.ARD_DATE || '');
                    $('#ARD_REF').val(data.ARD_REF || '');
                    $('#ARD_A_AMT').val(data.ARD_A_AMT || '0.00');
                    $('#ARP_CASH_DSC').val(data.ARP_CASH_DSC || '0.00');
                    $('#ARP_WH_TAX').val(data.ARP_WH_TAX || '0.00');

                    $('#ARP_CASH').val(data.ARP_CASH || '0.00');
                    $('#CASHAC_CODE').val(data.CASHAC_CODE || '');
                    $('#ARP_TRANSF').val(data.ARP_TRANSF || '0.00');
                    $('#BNKAC_CODE').val(data.BNKAC_CODE || '');

                    $('#ARP_CQ_AMT').val(data.ARP_CQ_AMT || '0.00');
                    $('#BANK_INITIAL').val(data.BANK_INITIAL || '');
                    $('#CQ_CHEQUE_NO').val(data.CQ_CHEQUE_NO || '');
                    $('#CQ_CHEQUE_DD').val(data.CQ_CHEQUE_DD || '');

                    $('#ARP_OTHER').val(data.ARP_OTHER || '0.00');
                    $('#PMT_NAME').val(data.PMT_NAME || '');

                    $('#ARP_DN').val(data.ARP_DN || '0.00');
                    $('#ARP_CN').val(data.ARP_CN || '0.00');
                    $('#ARP_OVER').val(data.ARP_OVER || '0.00');
                    $('#ARP_SHRT').val(data.ARP_SHRT || '0.00');
                    $('#ARP_FEE').val(data.ARP_FEE || '0.00');
                    $('#ARP_INT').val(data.ARP_INT || '0.00');
                    $('#ARP_LOSTXCHG').val(data.ARP_LOSTXCHG || '0.00');

                    $('#recordModal').modal('show');
                } catch (e) {
                    console.error(e);
                    alert("ไม่สามารถแสดงรายละเอียดได้");
                }
            }
        });
    </script>

    <script>
        function ReloadDataTable() {
            $('#doc_date_start').val('');
            $('#doc_date_to').val('');
            if (typeof dataRecords !== 'undefined') {
                dataRecords.ajax.reload(null, false);
            } else {
                $('#TableRecordList').DataTable().ajax.reload(null, false);
            }
        }
    </script>

    <script>
        function ExportData() {
            const form = document.getElementById("export_data");
            if (form.checkValidity()) {
                form.submit();
            } else {
                alert("Please fill out the required fields.");
            }
        }
    </script>

    </body>
    </html>

<?php } ?>
