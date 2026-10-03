<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include 'config/connect_db.php';
    $sql_province = " SELECT DISTINCT(province) AS province
    FROM ims_customer_ar WHERE 1 
    order by id ";
    $stmt_province = $conn->prepare($sql_province);
    $stmt_province->execute();
    $ProvinceRecords = $stmt_province->fetchAll();
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <div id="wrapper">


        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><span id="main_menu"></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><span id="sub_menu"></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form method="post" id="MainrecordForm" name="MainrecordForm"
                                              enctype="multipart/form-data">
                                            <input type="hidden" class="form-control" id="KeyAddData" name="KeyAddData"
                                                   value="">
                                            <input type="hidden" id="img" name="img">
                                            <div class="modal-body">
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <label for="doc_id"
                                                                   class="control-label">เลขที่เอกสาร</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_id" name="doc_id"
                                                                   readonly="true"
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-2">
                                                            <label for="doc_date"
                                                                   class="control-label">วันที่</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_date"
                                                                   name="doc_date"
                                                                   required="required"
                                                                   readonly="true"
                                                                   value=""
                                                                   placeholder="">
                                                            <div class="input-group-addon">
                                                                <span class="glyphicon glyphicon-th"></span>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" class="form-control"
                                                               id="customer_id"
                                                               name="customer_id">
                                                        <div class="col-sm-6">
                                                            <label for="customer_name"
                                                                   class="control-label">ชื่อลูกค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="customer_name"
                                                                   name="customer_name"
                                                                   required="required"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <input type="hidden" id="myCheckValue"
                                                                   name="myCheckValue">
                                                            <input type="checkbox" id="myCheck"
                                                                   name="myCheck" value="N">
                                                            <label class="form-check-label"
                                                                   for="flexCheckChecked">ลูกค้าใหม่
                                                            </label>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="CusModal"
                                                                   class="control-label"> เลือกชื่อลูกค้า </label>
                                                            <a data-toggle="modal" href="#SearchCusModal"
                                                               class="btn btn-primary" id="btnSearchCus">
                                                                Click <i class="fa fa-search"
                                                                         aria-hidden="true"></i>
                                                            </a>
                                                        </div>

                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="province">จังหวัด :</label>
                                                            <select name="province" id="province" class="form-control"
                                                                    required>
                                                                <option value="-"
                                                                        selected>-
                                                                </option>
                                                                <?php foreach ($ProvinceRecords as $row) { ?>
                                                                    <option value="<?php echo $row["province"]; ?>">
                                                                        <?php echo $row["province"]; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="contact_name"
                                                                   class="control-label">ผู้ติดต่อ</label>
                                                            <input type="text" class="form-control"
                                                                   id="contact_name"
                                                                   name="contact_name"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="phone"
                                                                   class="control-label">หมายเลขโทรศัพท์</label>
                                                            <input type="text" class="form-control"
                                                                   id="phone"
                                                                   name="phone"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="purpose" class="form-label">วัตถุประสงค์</label>
                                                            <select id="purpose" name="purpose" class="form-control">
                                                                <option value="">เลือกวัตถุประสงค์</option>
                                                                <option value="แนะนำตัว (ลูกค้าใหม่)">แนะนำตัว
                                                                    (ลูกค้าใหม่)
                                                                </option>
                                                                <option value="เข้าพบลูกค้าประจำเดือน">
                                                                    เข้าพบลูกค้าประจำเดือน
                                                                </option>
                                                                <option value="เสนอโปรโมชั่นใหม่">เสนอโปรโมชั่นใหม่
                                                                </option>
                                                                <option value="เก็บเช็ค/หรือเก็บเอกสาร">
                                                                    เก็บเช็ค/หรือเก็บเอกสาร
                                                                </option>
                                                                <option value="ปิดตัวเลข">ปิดตัวเลข</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <label for="purpose_text"
                                                                   class="control-label">อื่นๆ</label>
                                                            <input type="text" class="form-control"
                                                                   id="purpose_text"
                                                                   name="purpose_text"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-12">
                                                            <label for="agent_status"
                                                                   class="control-label">สถานะ/ตัวแทนยี่ห้อ</label>
                                                            <input type="text" class="form-control"
                                                                   id="agent_status"
                                                                   name="agent_status"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                    </div>

                                                    <h4>สินค้าหลักในร้าน</h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <th>ประเภท</th>
                                                                <th>ยี่ห้อ</th>
                                                                <th>ปริมาณขายต่อเดือน (เฉลี่ย)</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control"
                                                                           name="tbr_quantity_name1"
                                                                           id="tbr_quantity_name1" value=""
                                                                           placeholder=""></td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity1"
                                                                           name="tbr_quantity1" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity_name2"
                                                                           name="tbr_quantity_name2" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity2"
                                                                           name="tbr_quantity2" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity_name3"
                                                                           name="tbr_quantity_name3" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity3"
                                                                           name="tbr_quantity3" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity_name4"
                                                                           name="tbr_quantity_name4" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity4"
                                                                           name="tbr_quantity4" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control" id="tbr_quantity_name_total
                                                                           name=" tbr_quantity_name_total"
                                                                    readonly="true"
                                                                    placeholder="รวม">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_quantity_total"
                                                                           name="tbr_quantity_total" readonly="true"
                                                                           value=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity_name1"
                                                                           name="tbb_quantity_name1" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity1"
                                                                           name="tbb_quantity1" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity_name2"
                                                                           name="tbb_quantity_name2" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity2"
                                                                           name="tbb_quantity2" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity_name3"
                                                                           name="tbb_quantity_name3" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity3"
                                                                           name="tbb_quantity3" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity_name4"
                                                                           name="tbb_quantity_name4" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity4"
                                                                           name="tbb_quantity4" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity_name_total"
                                                                           name="tbb_quantity_name_total"
                                                                           readonly="true"
                                                                           placeholder="รวม"></td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_quantity_total"
                                                                           name="tbb_quantity_total" readonly="true"
                                                                           placeholder=""></td>
                                                            </tr>

                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity_name1"
                                                                           name="ltr_quantity_name1" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity1"
                                                                           name="ltr_quantity1" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity_name2"
                                                                           name="ltr_quantity_name2" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity2"
                                                                           name="ltr_quantity2" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity_name3"
                                                                           name="ltr_quantity_name3" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity3"
                                                                           name="ltr_quantity3" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity_name4"
                                                                           name="ltr_quantity_name4" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity4"
                                                                           name="ltr_quantity4" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity_name_total"
                                                                           name="ltr_quantity_name_total"
                                                                           readonly="true"
                                                                           placeholder="รวม"></td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_quantity_total"
                                                                           name="ltr_quantity_total" readonly="true"
                                                                           value=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity_name1"
                                                                           name="ltl_quantity_name1" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity1"
                                                                           name="ltl_quantity1" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity_name2"
                                                                           name="ltl_quantity_name2" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity2"
                                                                           name="ltl_quantity2" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity_name3"
                                                                           name="ltl_quantity_name3" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity3"
                                                                           name="ltl_quantity3" value="" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity_name4"
                                                                           name="ltl_quantity_name4" value=""
                                                                           placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity4"
                                                                           name="ltl_quantity4" placeholder="">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity_name_total"
                                                                           name="ltl_quantity_name_total"
                                                                           readonly="true"
                                                                           placeholder="รวม"></td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_quantity_total"
                                                                           name="ltl_quantity_total" readonly="true"
                                                                           value=""></td>
                                                            </tr>
                                                            <!-- สามารถเพิ่มแถวเพิ่มเติมได้ -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <br>

                                                    <div class="form-group row">
                                                        <div class="col-sm-3">
                                                            <label for="buy_from1"
                                                                   class="form-label">ซื้อยางจากร้าน</label>
                                                            <input type="text" class="form-control" id="buy_from1"
                                                                   name="buy_from1">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="buy_from2"
                                                                   class="form-label">ซื้อยางจากร้าน</label>
                                                            <input type="text" class="form-control" id="buy_from2"
                                                                   name="buy_from2">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="buy_from3"
                                                                   class="form-label">ซื้อยางจากร้าน</label>
                                                            <input type="text" class="form-control" id="buy_from3"
                                                                   name="buy_from3">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="buy_from_sac" class="form-label">สัดส่วนยางที่ซื้อจากสงวนออโต้คาร์</label>
                                                            <input type="text" class="form-control" id="buy_from_sac"
                                                                   name="buy_from_sac">
                                                        </div>
                                                    </div>

                                                    <h4>คาดการณ์การขายในเดือน</h4>
                                                    <div class="form-group row">
                                                        <div class="col-sm-2">
                                                            <label for="forecast_month" class="form-label">เดือนที่คาดการณ์</label>
                                                            <input type="month" class="form-control" id="forecast_month"
                                                                   name="forecast_month">
                                                        </div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <th>ประเภท</th>
                                                                <th>ยี่ห้อ</th>
                                                                <th>จำนวนเส้น</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_forecast_name1"
                                                                           name="tbr_forecast_name1" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_forecast_quantity1"
                                                                           name="tbr_forecast_quantity1"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: red;">TBR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_forecast_name2"
                                                                           name="tbr_forecast_name2" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbr_forecast_quantity2"
                                                                           name="tbr_forecast_quantity2"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_forecast_name1"
                                                                           name="tbb_forecast_name1" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_forecast_quantity1"
                                                                           name="tbb_forecast_quantity1"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: blue;">TBB</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_forecast_name2"
                                                                           name="tbb_forecast_name2" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="tbb_forecast_quantity2"
                                                                           name="tbb_forecast_quantity2"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_forecast_name1"
                                                                           name="ltr_forecast_name1" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_forecast_quantity1"
                                                                           name="ltr_forecast_quantity1"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: green;">LTR</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_forecast_name2"
                                                                           name="ltr_forecast_name2" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltr_forecast_quantity2"
                                                                           name="ltr_forecast_quantity2"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_forecast_name1"
                                                                           name="ltl_forecast_name1" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_forecast_quantity1"
                                                                           name="ltl_forecast_quantity1"
                                                                           placeholder=""></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: orangered;">LTL</td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_forecast_name2"
                                                                           name="ltl_forecast_name2" placeholder="">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                           id="ltl_forecast_quantity2"
                                                                           name="ltl_forecast_quantity2"
                                                                           placeholder=""></td>
                                                            </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <br>
                                                    <h4>ข้อมูลอื่นๆ</h4>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="financial_status" class="form-label">สถานะการเงิน</label>
                                                            <input type="text" class="form-control"
                                                                   id="financial_status" name="financial_status"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="financial_status"
                                                                   class="form-label">เหตุผล</label>
                                                            <input type="text" class="form-control"
                                                                   id="financial_reason" name="financial_reason"
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="candidate"
                                                                   class="form-label">คู่แข่ง</label>
                                                            <input type="text" class="form-control"
                                                                   id="candidate" name="candidate"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="candidate_reason"
                                                                   class="form-label">เหตุผล</label>
                                                            <input type="text" class="form-control"
                                                                   id="candidate_reason" name="candidate_reason"
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="sale_out"
                                                                   class="form-label">การขายออกของร้านค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="sale_out" name="sale_out"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="sale_out_reason"
                                                                   class="form-label">เหตุผล</label>
                                                            <input type="text" class="form-control"
                                                                   id="sale_out_reason" name="sale_out_reason"
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="stock_shop"
                                                                   class="form-label">สต็อกของร้านค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="stock_shop" name="stock_shop"
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="stock_shop_reason"
                                                                   class="form-label">เหตุผล</label>
                                                            <input type="text" class="form-control"
                                                                   id="stock_shop_reason" name="stock_shop_reason"
                                                                   placeholder="">
                                                        </div>
                                                    </div>

                                                    <br>
                                                    <div class="form-group row">
                                                        <div class="col-sm-12">
                                                            <label for="remark"
                                                                   class="control-label">ข้อมูลเพิ่มเติม</label>
                                                            <textarea class="form-control" id="remark" name="remark"
                                                                      rows="4" placeholder=""></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-12">
                                                            <button id="getLocationBtn">Click เปิดพิกัดบนแผนที่</button>
                                                            Latitude_Longitude = <input type="text"
                                                                                        id="latitude_longitude"
                                                                                        name="latitude_longitude"
                                                                                        readonly="true">
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                            <div class="modal-footer">
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>
                                                <input type="hidden" name="action" id="action" value=""/>
                                                <button type="button" class="btn btn-primary"
                                                        id="btnSave">Save <i
                                                            class="fa fa-check"></i>
                                                </button>

                                                <button type="button" class="btn btn-danger"
                                                        id="btnClose">Close <i
                                                            class="fa fa-times"></i>
                                                </button>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="files" class="control-label">เลือก File รูปภาพ
                                                        JPG หรือ PNG ที่ต้องการ Upload</label>
                                                    <input type="file" class="form-control" id="images" name="images[]"
                                                           multiple accept="image/*">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <!-- พื้นที่สำหรับแสดงภาพ Preview -->
                                                    <div id="imagePreview"
                                                         style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                                                </div>
                                            </div>

                                            <div id="existingImages"></div>
                                            <input type="hidden" id="existing_images" name="existing_images" value="">
                                            <input type="hidden" id="remove_images" name="remove_images" value="">

                                        </form>

                                        <div class="modal fade" id="SearchCusModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableCusCRMList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสลูกค้า</th>
                                                                    <th>ชื่อลูกค้า</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสลูกค้า</th>
                                                                    <th>ชื่อลูกค้า</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">

                                                        <div class="form-group row">
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="KeyAddDetail"
                                                                       name="KeyAddDetail" value="">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_id_detail"
                                                                       name="doc_id_detail" value="">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_date_detail"
                                                                       name="doc_date_detail" value="">
                                                            </div>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group row">
                                                                    <div>
                                                                        <!--label for="faq_id"
                                                                               class="control-label">รหัสคำถาม</label-->
                                                                        <input type="hidden"
                                                                               class="form-control"
                                                                               id="faq_id" name="faq_id"
                                                                               readonly="true"
                                                                               placeholder="รหัสคำถาม">
                                                                    </div>
                                                                    <div class="col-sm-12">
                                                                        <label for="faq_desc"
                                                                               class="control-label">คำถาม</label>
                                                                        <input type="text" class="form-control"
                                                                               id="faq_desc"
                                                                               name="faq_desc"
                                                                               readonly="true"
                                                                               placeholder="คำถาม">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="faq_anwser"
                                                                               class="control-label">คำตอบ</label>
                                                                        <textarea class="form-control" id="faq_anwser"
                                                                                  name="faq_anwser"
                                                                                  required="required"
                                                                                  rows="4"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <input type="hidden" name="id" id="id"/>
                                                                <input type="hidden" name="detail_id"
                                                                       id="detail_id"/>
                                                                <input type="hidden" name="action_detail"
                                                                       id="action_detail" value=""/>
                                                                <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                                <button type="button" class="btn btn-danger"
                                                                        data-dismiss="modal">Close <i
                                                                            class="fa fa-times"></i>
                                                                </button>
                                                            </div>

                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <div id="result"></div>

                                    </section>

                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

                </div>

                <!---Container Fluid-->

            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>

        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!--script src="vendor/jquery/jquery.min.js"></script-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>


    <script src="js/modal/show_cust_crm_modal.js"></script>


    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="//github.com/fyneworks/multifile/blob/master/jquery.MultiFile.min.js" type="text/javascript"
            language="javascript"></script>

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
    </style>

    <script>
        $(document).ready(function () {
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date').val(doc_date);
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

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
            $("#btnClose").click(function () {
                if ($('#save_status').val() !== '') {
                    window.opener = self;
                    window.close();
                } else {
                    alertify.error("กรุณากด save อีกครั้ง");
                }
            });
        });
    </script>

    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            $('#save_status').val("before");

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");
            }

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");

                // Disable ปุ่ม BTNSave ถ้า action เป็น 'ADD'
                $('#btnSave').prop('disabled', false);
                $('#myCheck').prop('disabled', false);
                $('#btnUpload').prop('disabled', true);
                $('#btnSearchCus').css('opacity', '1'); // ลดความทึบของปุ่มให้ดูเหมือนถูกปิดใช้งาน
                $('#customer_name').prop('readonly', true);
            } else {
                // ถ้าไม่ใช่ 'ADD' ก็ให้ Enable ปุ่ม BTNSave
                $('#btnSave').prop('disabled', false);
                $('#myCheck').prop('disabled', true);
                $('#btnUpload').prop('disabled', false);
                $('#btnSearchCus').css('opacity', '0.5'); // ลดความทึบของปุ่มให้ดูเหมือนถูกปิดใช้งาน
                $('#customer_name').prop('readonly', true);
            }

            if (queryString["doc_id"] != null && queryString["customer_name"] != null) {
                $('#id').val(queryString["id"]);
                $('#doc_id').val(queryString["doc_id"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#customer_id').val(queryString["customer_id"]);
                $('#customer_name').val(queryString["customer_name"]);
                $('#purpose').val(queryString["purpose"]);
                $('#purpose_text').val(queryString["purpose_text"]);
                $('#province').val(queryString["province"]);
                $('#phone').val(queryString["phone"]);
                $('#contact_name').val(queryString["contact_name"]);
                $('#remark').val(queryString["remark"]);
                $('#img').val(queryString["picture"]);
                $('#latitude_longitude').val(queryString["latitude_longitude"]);

                ReloadData(queryString["id"]);

            }
        });
    </script>

    <script>
        function isGoogleChrome() {
            const userAgent = navigator.userAgent.toLowerCase();
            const vendor = navigator.vendor.toLowerCase();
            return vendor.includes("google") && userAgent.includes("chrome");
        }

        function getLocation(callback) {
            if (isGoogleChrome()) {
                console.log("เบราว์เซอร์คือ Google Chrome");
                // เพิ่มโค้ดการปลด security ที่เกี่ยวข้องถ้าจำเป็น
            } else {
                console.log("เบราว์เซอร์ไม่ใช่ Google Chrome");
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        callback(latitude, longitude); // ส่งค่าไปที่ callback
                    },
                    function (error) {
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                alertify.error("การเข้าถึงพิกัดถูกปฏิเสธ");
                                break;
                            case error.POSITION_UNAVAILABLE:
                                alertify.error("ไม่สามารถระบุตำแหน่งได้");
                                break;
                            case error.TIMEOUT:
                                alertify.error("หมดเวลารอการตอบสนอง");
                                break;
                            default:
                                alertify.error("เกิดข้อผิดพลาดในการดึงตำแหน่ง");
                        }
                        callback('0', '0'); // ส่งค่าเริ่มต้นในกรณีเกิดข้อผิดพลาด
                    }
                );
            } else {
                alertify.error("เบราว์เซอร์ของคุณไม่รองรับการดึงพิกัดตำแหน่ง");
                callback('0', '0'); // ส่งค่าเริ่มต้นในกรณีที่เบราว์เซอร์ไม่รองรับ
            }
        }
    </script>

    <script>
        document.getElementById('getLocationBtn').addEventListener('click', function () {
            // ดึงค่าจาก input ที่มี id = latitude_longitude
            let latitude_longitude = $('#latitude_longitude').val();

            // ตรวจสอบว่าค่าที่กรอกเป็นพิกัดที่ถูกต้องหรือไม่
            if (latitude_longitude && latitude_longitude !== '0,0') {
                const googleMapsUrl = `https://www.google.com/maps?q=${latitude_longitude}`;
                console.log('Google Maps URL:', googleMapsUrl);

                // ใช้ window.open เพื่อเปิด Google Maps ในแท็บใหม่
                window.open(googleMapsUrl, '_blank');
            } else {
                alertify.error("พิกัดไม่ถูกต้องหรือยังไม่ได้รับข้อมูล");
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnSave").on("click", function () {
                let doc_date = $('#doc_date').val();
                let customer_name = $('#customer_name').val();

                if (doc_date !== "" && customer_name !== "") {
                    $(this).prop("disabled", true); // ป้องกันการกดซ้ำ

                    getLocation(function (latitude, longitude) {
                        // หากไม่ได้ค่าพิกัด ให้ตั้งค่าเป็น 0
                        if (!latitude || !longitude) {
                            latitude = "0";
                            longitude = "0";
                        }

                        // เพิ่มพิกัดในฟอร์ม
                        let latitude_longitude = latitude + "," + longitude;
                        $('#latitude_longitude').val(latitude_longitude);

                        // สร้าง FormData
                        let form = $("#MainrecordForm")[0];
                        let formData = new FormData(form);

                        // ตรวจสอบว่า FormData มีข้อมูลหรือไม่
                        for (let pair of formData.entries()) {
                            console.log(pair[0] + ', ' + pair[1]);
                        }

                        // ส่งข้อมูลไปยังเซิร์ฟเวอร์
                        $.ajax({
                            url: 'model/manage_crm_sale_check_process.php',
                            method: "POST",
                            data: formData,
                            processData: false, // ต้องตั้งค่า false เพื่อส่งข้อมูลแบบ FormData
                            contentType: false, // ปิด Content-Type อัตโนมัติ
                            success: function (response) {
                                alertify.success("บันทึกข้อมูลสำเร็จ");
                                if ($("#KeyAddData").val() !== '') {
                                    // ใช้ค่าจาก queryString เพื่อสร้าง URL
                                    let url = `manage-crm-sale-check?m=${queryString["main_menu"]}&s=${queryString["sub_menu"]}`;
                                    window.location.href = url;
                                }
                                console.log(response); // ตรวจสอบ response จากเซิร์ฟเวอร์
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                alertify.alert("มีข้อผิดพลาด: " + textStatus + " - " + errorThrown);
                            },
                            complete: function () {
                                $("#btnSave").prop("disabled", false); // เปิดปุ่มอีกครั้ง
                            }
                        });
                    });
                } else {
                    alertify.alert("กรุณากรอกข้อมูลวันที่เอกสารและชื่อลูกค้าให้ครบถ้วน");
                }
            });
        });
    </script>


    <script>
        $(document).ready(function () {
            $('#myCheckValue').val('N');
            $('#myCheck').click(function () {
                if ($("#myCheck").is(":checked") == true) {
                    $('#myCheckValue').val('Y');
                    $('#btnSearchCus').css('pointer-events', 'none');
                    $('#btnSearchCus').css('opacity', '0.5'); // ลดความทึบของปุ่มให้ดูเหมือนถูกปิดใช้งาน
                    $('#customer_name').prop('readonly', false);
                } else {
                    $('#myCheckValue').val('Y');
                    // Enable ปุ่มโดยการตั้งค่า pointer-events เป็น 'auto'
                    $('#btnSearchCus').css('pointer-events', 'auto');
                    $('#btnSearchCus').css('opacity', '1'); // รีเซ็ตความทึบของปุ่มให้กลับมาเหมือนเดิม
                    $('#customer_name').prop('readonly', true);
                }
            });
        });
    </script>

    <script>
        $('#SearchCusModal').on('hidden.bs.modal', function () {
            const customer_id = $('#customer_id').val();

            // ตรวจสอบค่า customer_id
            console.log("Customer ID: " + customer_id);

            if (customer_id !== '') {
                let formData = {action: "GET_DATA", customer_id: customer_id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_customer_ar.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        // ตรวจสอบว่าได้รับข้อมูลจาก server หรือไม่
                        console.log(response);

                        if (response.length > 0) {
                            let contact_name = response[0].contact_name;
                            let phone = response[0].phone;
                            let province = response[0].province;
                            $('#contact_name').val(contact_name);
                            $('#phone').val(phone);
                            $('#province').val(province);
                        } else {
                            alertify.error("No customer data found.");
                        }
                    },
                    error: function (xhr, status, error) {
                        alertify.error("Error: " + xhr.responseText);
                    }
                });
            } else {
                alertify.error("Customer ID is empty.");
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#images").on("change", function () {
                let files = this.files;
                let previewContainer = $("#imagePreview");

                // ล้าง Preview เดิม
                previewContainer.empty();

                // ตรวจสอบว่าเลือกไฟล์
                if (files.length > 0) {
                    Array.from(files).forEach((file, index) => {
                        // ตรวจสอบประเภทไฟล์
                        if (file.type.startsWith("image/")) {
                            let reader = new FileReader();
                            reader.onload = function (e) {
                                // สร้าง element สำหรับแสดงภาพ
                                let imgElement = $(
                                    `<div style="position: relative; width: 100px; height: 100px;">
                                <img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; cursor: pointer; font-size: 14px;" data-index="${index}">×</span>
                            </div>`
                                );

                                // เพิ่มภาพเข้าไปใน Preview
                                previewContainer.append(imgElement);
                            };
                            reader.readAsDataURL(file);
                        } else {
                            alertify.error("กรุณาเลือกไฟล์รูปภาพเท่านั้น");
                        }
                    });
                }
            });

            // ลบภาพออกจาก Preview
            $("#imagePreview").on("click", "span", function () {
                let indexToRemove = $(this).data("index");
                let fileInput = document.getElementById("images");

                // ใช้ DataTransfer เพื่อแก้ไขรายการไฟล์
                let dataTransfer = new DataTransfer();
                Array.from(fileInput.files).forEach((file, index) => {
                    if (index !== indexToRemove) {
                        dataTransfer.items.add(file);
                    }
                });

                // อัปเดตรายการไฟล์
                fileInput.files = dataTransfer.files;

                // รีโหลด Preview
                $(fileInput).trigger("change");
            });
        });

    </script>

    <script>

        $(document).ready(function () {
            let existingImages = $('#img').val();
            if (existingImages) {
                let imageArray = existingImages.split(",");
                let imageContainer = $("#existingImages");

                imageArray.forEach(image => {
                    imageContainer.append(`
                <div class="image-wrapper" data-name="${image}">
                    Click ที่ภาพเพื่อขยาย
                    <a href="upload_img/${image}" target="_blank">
                        <img src="upload_img/${image}" width="200">
                    </a>
                    <button type="button" class="remove-image" data-name="${image}">ลบ</button>
                </div>
                <br>
            `);
                });

                // อัปเดต hidden input
                $("#existing_images").val(existingImages);
            }

            // ลบรูปภาพเดิม
            $(document).on("click", ".remove-image", function () {
                const imageName = $(this).data("name");

                // เพิ่มชื่อไฟล์ที่ต้องการลบใน hidden input
                let removedImages = $("#remove_images").val();
                removedImages = removedImages ? removedImages.split(",") : [];
                removedImages.push(imageName);
                $("#remove_images").val(removedImages.join(","));

                // ลบจากหน้าจอ
                $(this).closest(".image-wrapper").remove();

                // อัปเดต existing_images โดยลบชื่อไฟล์ออก
                let currentImages = $("#existing_images").val().split(",");
                currentImages = currentImages.filter(image => image !== imageName);
                $("#existing_images").val(currentImages.join(","));
            });

            // แสดงภาพ Preview ของไฟล์ใหม่
            $("#images").on("change", function () {
                let files = this.files;
                let previewContainer = $("#imagePreview");
                previewContainer.empty();

                Array.from(files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        previewContainer.append(`<img src="${e.target.result}" width="100" style="margin: 5px;">`);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });

    </script>

    <script>
        function ReloadData(id) {
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_crm_sale_check_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {

                        let agent_status = response[i].agent_status;
                        let buy_from1 = response[i].buy_from1;
                        let buy_from2 = response[i].buy_from2;
                        let buy_from3 = response[i].buy_from3;
                        let buy_from_sac = response[i].buy_from_sac;
                        let forecast_month = response[i].forecast_month;

                        let financial_status = response[i].financial_status;
                        let financial_reason = response[i].financial_reason;
                        let candidate = response[i].candidate;
                        let candidate_reason = response[i].candidate_reason;
                        let sale_out = response[i].sale_out;
                        let sale_out_reason = response[i].sale_out_reason;
                        let stock_shop = response[i].stock_shop;
                        let stock_shop_reason = response[i].stock_shop_reason;


                        let tbr_quantity_name1 = response[i].tbr_quantity_name1;
                        let tbr_quantity1 = response[i].tbr_quantity1;
                        let tbr_quantity_name2 = response[i].tbr_quantity_name2;
                        let tbr_quantity2 = response[i].tbr_quantity2;
                        let tbr_quantity_name3 = response[i].tbr_quantity_name3;
                        let tbr_quantity3 = response[i].tbr_quantity3;
                        let tbr_quantity_name4 = response[i].tbr_quantity_name4;
                        let tbr_quantity4 = response[i].tbr_quantity4;
                        let tbr_quantity_total = response[i].tbr_quantity_total;

                        let tbb_quantity_name1 = response[i].tbb_quantity_name1;
                        let tbb_quantity1 = response[i].tbb_quantity1;
                        let tbb_quantity_name2 = response[i].tbb_quantity_name2;
                        let tbb_quantity2 = response[i].tbb_quantity2;
                        let tbb_quantity_name3 = response[i].tbb_quantity_name3;
                        let tbb_quantity3 = response[i].tbb_quantity3;
                        let tbb_quantity_name4 = response[i].tbb_quantity_name4;
                        let tbb_quantity4 = response[i].tbb_quantity4;
                        let tbb_quantity_total = response[i].tbb_quantity_total;

                        let ltr_quantity_name1 = response[i].ltr_quantity_name1;
                        let ltr_quantity1 = response[i].ltr_quantity1;
                        let ltr_quantity_name2 = response[i].ltr_quantity_name2;
                        let ltr_quantity2 = response[i].ltr_quantity2;
                        let ltr_quantity_name3 = response[i].ltr_quantity_name3;
                        let ltr_quantity3 = response[i].ltr_quantity3;
                        let ltr_quantity_name4 = response[i].ltr_quantity_name4;
                        let ltr_quantity4 = response[i].ltr_quantity4;
                        let ltr_quantity_total = response[i].ltr_quantity_total;

                        let ltl_quantity_name1 = response[i].ltl_quantity_name1;
                        let ltl_quantity1 = response[i].ltl_quantity1;
                        let ltl_quantity_name2 = response[i].ltl_quantity_name2;
                        let ltl_quantity2 = response[i].ltl_quantity2;
                        let ltl_quantity_name3 = response[i].ltl_quantity_name3;
                        let ltl_quantity3 = response[i].ltl_quantity3;
                        let ltl_quantity_name4 = response[i].ltl_quantity_name4;
                        let ltl_quantity4 = response[i].ltl_quantity4;
                        let ltl_quantity_total = response[i].ltl_quantity_total;

                        let tbr_forecast_name1 = response[i].tbr_forecast_name1;
                        let tbr_forecast_quantity1 = response[i].tbr_forecast_quantity1;
                        let tbr_forecast_name2 = response[i].tbr_forecast_name2;
                        let tbr_forecast_quantity2 = response[i].tbr_forecast_quantity2;

                        let tbb_forecast_name1 = response[i].tbb_forecast_name1;
                        let tbb_forecast_quantity1 = response[i].tbb_forecast_quantity1;
                        let tbb_forecast_name2 = response[i].tbb_forecast_name2;
                        let tbb_forecast_quantity2 = response[i].tbb_forecast_quantity2;

                        let ltr_forecast_name1 = response[i].ltr_forecast_name1;
                        let ltr_forecast_quantity1 = response[i].ltr_forecast_quantity1;
                        let ltr_forecast_name2 = response[i].ltr_forecast_name2;
                        let ltr_forecast_quantity2 = response[i].ltr_forecast_quantity2;

                        let ltl_forecast_name1 = response[i].ltl_forecast_name1;
                        let ltl_forecast_quantity1 = response[i].ltl_forecast_quantity1;
                        let ltl_forecast_name2 = response[i].ltl_forecast_name2;
                        let ltl_forecast_quantity2 = response[i].ltl_forecast_quantity2;


                        $('#agent_status').val(agent_status);
                        $('#buy_from1').val(buy_from1);
                        $('#buy_from2').val(buy_from2);
                        $('#buy_from3').val(buy_from3);
                        $('#buy_from_sac').val(buy_from_sac);
                        $('#forecast_month').val(forecast_month);

                        $('#financial_status').val(financial_status);
                        $('#financial_reason').val(financial_reason);
                        $('#candidate').val(candidate);
                        $('#candidate_reason').val(candidate_reason);
                        $('#sale_out').val(sale_out);
                        $('#sale_out_reason').val(sale_out_reason);
                        $('#stock_shop').val(stock_shop);
                        $('#stock_shop_reason').val(stock_shop_reason);

                        $('#tbr_quantity_name1').val(tbr_quantity_name1);
                        $('#tbr_quantity1').val(tbr_quantity1);
                        $('#tbr_quantity_name2').val(tbr_quantity_name2);
                        $('#tbr_quantity2').val(tbr_quantity2);
                        $('#tbr_quantity_name3').val(tbr_quantity_name3);
                        $('#tbr_quantity3').val(tbr_quantity3);
                        $('#tbr_quantity_name4').val(tbr_quantity_name4);
                        $('#tbr_quantity4').val(tbr_quantity4);
                        $('#tbr_quantity_total').val(tbr_quantity_total);

                        $('#tbb_quantity_name1').val(tbb_quantity_name1);
                        $('#tbb_quantity1').val(tbb_quantity1);
                        $('#tbb_quantity_name2').val(tbb_quantity_name2);
                        $('#tbb_quantity2').val(tbb_quantity2);
                        $('#tbb_quantity_name3').val(tbb_quantity_name3);
                        $('#tbb_quantity3').val(tbb_quantity3);
                        $('#tbb_quantity_name4').val(tbb_quantity_name4);
                        $('#tbb_quantity4').val(tbb_quantity4);
                        $('#tbb_quantity_total').val(tbb_quantity_total);

                        $('#ltr_quantity_name1').val(ltr_quantity_name1);
                        $('#ltr_quantity1').val(ltr_quantity1);
                        $('#ltr_quantity_name2').val(ltr_quantity_name2);
                        $('#ltr_quantity2').val(ltr_quantity2);
                        $('#ltr_quantity_name3').val(ltr_quantity_name3);
                        $('#ltr_quantity3').val(ltr_quantity3);
                        $('#ltr_quantity_name4').val(ltr_quantity_name4);
                        $('#ltr_quantity4').val(ltr_quantity4);
                        $('#ltr_quantity_total').val(ltr_quantity_total);

                        $('#ltl_quantity_name1').val(ltl_quantity_name1);
                        $('#ltl_quantity1').val(ltl_quantity1);
                        $('#ltl_quantity_name2').val(ltl_quantity_name2);
                        $('#ltl_quantity2').val(ltl_quantity2);
                        $('#ltl_quantity_name3').val(ltl_quantity_name3);
                        $('#ltl_quantity3').val(ltl_quantity3);
                        $('#ltl_quantity_name4').val(ltl_quantity_name4);
                        $('#ltl_quantity4').val(ltl_quantity4);
                        $('#ltl_quantity_total').val(ltl_quantity_total);

                        $('#tbr_forecast_name1').val(tbr_forecast_name1);
                        $('#tbr_forecast_quantity1').val(tbr_forecast_quantity1);
                        $('#tbr_forecast_name2').val(tbr_forecast_name2);
                        $('#tbr_forecast_quantity2').val(tbr_forecast_quantity2);

                        $('#tbb_forecast_name1').val(tbb_forecast_name1);
                        $('#tbb_forecast_quantity1').val(tbb_forecast_quantity1);
                        $('#tbb_forecast_name2').val(tbb_forecast_name2);
                        $('#tbb_forecast_quantity2').val(tbb_forecast_quantity2);

                        $('#ltr_forecast_name1').val(ltr_forecast_name1);
                        $('#ltr_forecast_quantity1').val(ltr_forecast_quantity1);
                        $('#ltr_forecast_name2').val(ltr_forecast_name2);
                        $('#ltr_forecast_quantity2').val(ltr_forecast_quantity2);

                        $('#ltl_forecast_name1').val(ltl_forecast_name1);
                        $('#ltl_forecast_quantity1').val(ltl_forecast_quantity1);
                        $('#ltl_forecast_name2').val(ltl_forecast_name2);
                        $('#ltl_forecast_quantity2').val(ltl_forecast_quantity2);


                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            function calculateTotalQuantity(group) {
                // ดึงค่าจาก input fields ของกลุ่มที่กำหนดและแปลงเป็นตัวเลข
                const quantity1 = parseFloat($(`#${group}_quantity1`).val()) || 0;
                const quantity2 = parseFloat($(`#${group}_quantity2`).val()) || 0;
                const quantity3 = parseFloat($(`#${group}_quantity3`).val()) || 0;
                const quantity4 = parseFloat($(`#${group}_quantity4`).val()) || 0;

                // คำนวณผลรวม
                const total = quantity1 + quantity2 + quantity3 + quantity4;

                // แสดงผลลัพธ์ในฟิลด์รวมของกลุ่มที่กำหนด
                $(`#${group}_quantity_total`).val(total.toFixed(2)); // แสดงทศนิยม 2 ตำแหน่ง
            }

            // สร้าง array ของกลุ่มที่ต้องการใช้
            const groups = ['tbb', 'tbr', 'ltr', 'ltl'];

            // เพิ่ม event listener ให้กับ input fields ของแต่ละกลุ่ม
            groups.forEach(group => {
                $(`#${group}_quantity1, #${group}_quantity2, #${group}_quantity3, #${group}_quantity4`)
                    .on('blur', function () {
                        calculateTotalQuantity(group);
                    });
            });

            // คำนวณผลรวมอีกครั้งเมื่อกดปุ่ม Save
            $('#saveButton').on('click', function () {
                groups.forEach(group => {
                    calculateTotalQuantity(group);
                });

                // หลังคำนวณเสร็จ สามารถเพิ่มโค้ดอื่นๆ เช่น ส่งข้อมูลไปยัง server ได้ที่นี่
                alert('คำนวณผลรวมเสร็จสิ้นและบันทึกข้อมูล!');
            });
        });
    </script>


    </body>
    </html>

<?php } ?>