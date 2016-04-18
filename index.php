<?php

require "vendor/autoload.php";

use Bukoli\Bukoli;
use Bukoli\Model\IntegrationEndUserInfo;
use Bukoli\Model\IntegrationOrderDetailInfo;
use Bukoli\Model\IntegrationOrderInfo;
use Bukoli\Request\OrderInsert;

$error = null;
$result = null;

$errors = [];

$requiredFields = [
    'CustomerPassword',
    'RequestOrderId',
    'BukoliPoint',
    'IrsaliyeNo',
    'EndUserCode',
    'EndUserFirstName',
    'EndUserLastName',
    'EndUserEmail',
];

if (isset($_POST['submit'])) {
    foreach ($requiredFields as $requiredField) {
        if (empty($_POST[$requiredField])) {
            $errors[] = $requiredField . ' is required.';
        }
    }

    if (count($errors) > 0) {
        $error = "<ul><li>" . implode('</li><li>', $errors) . "</li></ul>";
    }

    if (!$error) {
        // Initialize
        Bukoli::init($_POST['CustomerPassword']);

        $orderInsert = new OrderInsert();

        $orderInfo = new IntegrationOrderInfo();
        // Required
        $orderInfo->setRequestOrderId($_POST['RequestOrderId']);
        $orderInfo->setSelectedJetonPointCode($_POST['BukoliPoint']);
        $orderInfo->setIrsaliyeNo($_POST['IrsaliyeNo']);
        $orderInfo->setOrderDate($_POST['OrderDate']);

        // Optional
        $orderInfo->setParentRequestOrderId($_POST['ParentRequestOrderId']);
        $orderInfo->setInvoiceNo($_POST['InvoiceNo']);

        /*
         *  End User
         */
        $endUser = new IntegrationEndUserInfo();
        // Required
        $endUser->setEndUserCode($_POST['EndUserCode']);
        $endUser->setFirstName($_POST['EndUserFirstName']);
        $endUser->setLastName($_POST['EndUserLastName']);
        $endUser->setEmail($_POST['EndUserEmail']);

        // Optional
        $endUser->setPhone($_POST['EndUserPhone']);
        $endUser->setTcIdentityNo($_POST['EndUserTcIdentityNo']);
        //$endUser->setAddress($_POST['EndUserAddress']);
        $endUser->setJob($_POST['EndUserJob']);

        try {
            $endUser->setBirthDate(new DateTime($_POST['EndUserBirthDate'], Bukoli::getDateTimeZone()));
        } catch (\Exception $e) {
            $endUser->setBirthDate(new DateTime(date('Y-m-d'), Bukoli::getDateTimeZone()));
        }

        // Add End User to Order Info
        $orderInfo->setEndUserData($endUser);

        /*
         *  Koli
         */
        if ($_POST['KoliDeci'] && $_POST['KoliInfo'] && $_POST['KoliBarcode']) {
            $orderDetail = new IntegrationOrderDetailInfo();
            $orderDetail->setDeci($_POST['KoliDeci']);
            $orderDetail->setInfo($_POST['KoliInfo']);
            $orderDetail->setBarcode($_POST['KoliBarcode']);

            $orderInfo->setIntegrationOrderDetailInfoArr([
                $orderDetail
            ]);
        }

        // Add Order Info to request
        $orderInsert->setIntegrationOrderInfo($orderInfo);

        try {
            $response = $orderInsert->request();
            if ($response->getStatus() == 1) {
                // Success
                $result = "";
                $result .= 'Status: ' . $response->getStatus() . '<br/>';
                $result .= 'Message: ' . $response->getMessage() . '<br/>';
                $result .= 'JetonOrderId: ' . $response->getJetonOrderId() . '<br/>';
                $result .= 'TrackingNo: ' . $response->getTrackingNo() . '<br/>';
            } else {
                // Fail
                $error = "";
                $error .= 'Status: ' . $response->getStatus() . '<br/>';
                $error .= 'Message: ' . $response->getMessage() . '<br/>';
                $error .= 'JetonOrderId: ' . $response->getJetonOrderId() . '<br/>';
                $error .= 'TrackingNo: ' . $response->getTrackingNo() . '<br/>';
            }
        } catch (SoapFault $e) {
            // Soap Exception
            $error = str_replace(PHP_EOL, '<br/>', $e->getMessage());
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Bukoli Php Api - Order Insert</title>

    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/bukoli.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<!-- Static navbar -->
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Bukoli Php Api Sample</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="javascript:;">Order Insert</a></li>
                <li><a href="status.php">Order Status Detail</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container">
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert" data-dismiss="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?php echo $error ?>
        </div>
    <?php elseif ($result): ?>
    <div class="alert alert-success" role="alert" data-dismiss="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $result ?>
        </div>
    <?php endif; ?>
    <form class="form-horizontal" method="POST">
        <div class="form-group required">
            <label for="CustomerPassword" class="col-sm-3 control-label">CustomerPassword</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="CustomerPassword" id="CustomerPassword" placeholder="CustomerPassword"
                       value="<?php echo isset($_POST['CustomerPassword']) ? $_POST['CustomerPassword'] : '' ?>">
            </div>
        </div>
        <div class="form-group required">
            <label for="RequestOrderId" class="col-sm-3 control-label">RequestOrderId</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="RequestOrderId" id="RequestOrderId" placeholder="RequestOrderId"
                       value="<?php echo isset($_POST['RequestOrderId']) ? $_POST['RequestOrderId'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="ParentRequestOrderId" class="col-sm-3 control-label">ParentRequestOrderId</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="ParentRequestOrderId" id="ParentRequestOrderId" placeholder="ParentRequestOrderId"
                       value="<?php echo isset($_POST['ParentRequestOrderId']) ? $_POST['ParentRequestOrderId'] : '' ?>">
            </div>
        </div>
        <div class="form-group required">
            <label class="col-sm-3 control-label">Bukoli Point</label>
            <div class="col-sm-9">
                <input type="hidden" name="BukoliPoint" id="BukoliPoint">
                <div id="selectBukoliPoint">
                    <a href="javascript:CargoChanged('Bukoli')" class="btn btn-bukoli">Select Bukoli Point</a>
                    <div id="dvMapContainer">
                        <div id="jetonDiv" style="z-index:100; width:100%; height:400px; display:none; position:absolute ; ">
                        </div>
                    </div>
                </div>
                <div id="selectedBukoliPoint" style="display: none">
                    <span id="PointCode"></span><br/>
                    <span id="PointName"></span><br/>
                    <span id="PointAddress"></span>
                </div>
            </div>
        </div>
        <div class="form-group required">
            <label for="IrsaliyeNo" class="col-sm-3 control-label">OrderDate</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="OrderDate" id="OrderDate" placeholder="OrderDate"
                       value="<?php echo isset($_POST['OrderDate']) ? $_POST['OrderDate'] : date('YmdHis') ?>">
            </div>
        </div>
        <div class="form-group required">
            <label for="IrsaliyeNo" class="col-sm-3 control-label">IrsaliyeNo</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="IrsaliyeNo" id="IrsaliyeNo" placeholder="IrsaliyeNo"
                       value="<?php echo isset($_POST['IrsaliyeNo']) ? $_POST['IrsaliyeNo'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="InvoiceNo" class="col-sm-3 control-label">InvoiceNo</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="InvoiceNo" id="InvoiceNo" placeholder="InvoiceNo"
                       value="<?php echo isset($_POST['InvoiceNo']) ? $_POST['InvoiceNo'] : '' ?>">
            </div>
        </div>
        <hr/>
        <div class="form-group required">
            <label for="EndUserCode" class="col-sm-3 control-label">EndUserCode</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserCode" id="EndUserCode" placeholder="EndUserCode"
                       value="<?php echo isset($_POST['EndUserCode']) ? $_POST['EndUserCode'] : '' ?>">
            </div>
        </div>
        <div class="form-group required">
            <label for="EndUserFirstName" class="col-sm-3 control-label">EndUserFirstName</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserFirstName" id="EndUserFirstName" placeholder="EndUserFirstName"
                       value="<?php echo isset($_POST['EndUserFirstName']) ? $_POST['EndUserFirstName'] : '' ?>">
            </div>
        </div>
        <div class="form-group required">
            <label for="EndUserLastName" class="col-sm-3 control-label">EndUserLastName</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserLastName" id="EndUserLastName" placeholder="EndUserLastName"
                       value="<?php echo isset($_POST['EndUserLastName']) ? $_POST['EndUserLastName'] : '' ?>">
            </div>
        </div>
        <div class="form-group required">
            <label for="EndUserEmail" class="col-sm-3 control-label">EndUserEmail</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserEmail" id="EndUserEmail" placeholder="EndUserEmail"
                       value="<?php echo isset($_POST['EndUserEmail']) ? $_POST['EndUserEmail'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="EndUserPhone" class="col-sm-3 control-label">EndUserPhone</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserPhone" id="EndUserPhone" placeholder="EndUserPhone"
                       value="<?php echo isset($_POST['EndUserPhone']) ? $_POST['EndUserPhone'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="EndUserBirthDate" class="col-sm-3 control-label">EndUserBirthDate</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserBirthDate" id="EndUserBirthDate" placeholder="EndUserBirthDate"
                       value="<?php echo isset($_POST['EndUserBirthDate']) ? $_POST['EndUserBirthDate'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="EndUserGender" class="col-sm-3 control-label">EndUserGender</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserGender" id="EndUserGender" placeholder="EndUserGender"
                       value="<?php echo isset($_POST['EndUserGender']) ? $_POST['EndUserGender'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="EndUserMartialStatus" class="col-sm-3 control-label">EndUserMartialStatus</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserMartialStatus" id="EndUserMartialStatus" placeholder="EndUserMartialStatus"
                       value="<?php echo isset($_POST['EndUserMartialStatus']) ? $_POST['EndUserMartialStatus'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="EndUserTcIdentityNo" class="col-sm-3 control-label">EndUserTcIdentityNo</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserTcIdentityNo" id="EndUserTcIdentityNo" placeholder="EndUserTcIdentityNo"
                       value="<?php echo isset($_POST['EndUserTcIdentityNo']) ? $_POST['EndUserTcIdentityNo'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="EndUserJob" class="col-sm-3 control-label">EndUserJob</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="EndUserJob" id="EndUserJob" placeholder="EndUserJob"
                       value="<?php echo isset($_POST['EndUserJob']) ? $_POST['EndUserJob'] : '' ?>">
            </div>
        </div>
        <hr/>
        <div class="form-group">
            <label for="KoliDeci" class="col-sm-3 control-label">KoliDeci</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="KoliDeci" id="KoliDeci" placeholder="KoliDeci"
                       value="<?php echo isset($_POST['KoliDeci']) ? $_POST['KoliDeci'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="KoliInfo" class="col-sm-3 control-label">KoliInfo</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="KoliInfo" id="KoliInfo" placeholder="KoliInfo"
                       value="<?php echo isset($_POST['KoliInfo']) ? $_POST['KoliInfo'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="KoliBarcode" class="col-sm-3 control-label">KoliBarcode</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="KoliBarcode" id="KoliBarcode" placeholder="KoliBarcode"
                       value="<?php echo isset($_POST['KoliBarcode']) ? $_POST['KoliBarcode'] : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-3">
                <input class="btn btn-default btn-bukoli" type="submit" name="submit" value="Submit">
            </div>
        </div>
    </form>

</div> <!-- /container -->


<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="https://bukoli.borusan.com/JetonAPI/jeton.load.api-min.js"></script>
<script>
    var jetonOptions;
    var jeton;

    $(document).ready(function () {
        jetonOptions = {
            targetDiv: document.getElementById("jetonDiv"), //zorunlu
            callbackFunc: JetonPointSelected, //mandatory
            height: 200
        };
        jeton = new Jeton(jetonOptions);
        jeton.Init();
    });

    //this method should be called when cargo type changed at the cargo selecting step
    function CargoChanged(cargotype) {
        console.log(cargotype);
        if (cargotype == "Bukoli") {
            document.getElementById("jetonDiv").style.display = "block";
            jeton.Refresh();
        } else {
            document.getElementById("jetonDiv").style.display = "none";
        }
    }

    function JetonPointSelected(jetonPoint) {
        document.getElementById("jetonDiv").style.display = "none";
        document.getElementById("selectedBukoliPoint").style.display = "block";
        document.getElementById("BukoliPoint").value = jetonPoint.PointCode;
        document.getElementById("PointCode").innerText = jetonPoint.PointCode;
        document.getElementById("PointName").innerText = jetonPoint.PointName;
        document.getElementById("PointAddress").innerText = jetonPoint.PointAddress;

    }
</script>
</body>
</html>
