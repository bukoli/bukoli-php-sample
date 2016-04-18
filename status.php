<?php

require "vendor/autoload.php";

use Bukoli\Bukoli;
use Bukoli\Request\OrderStatusDetailGet;

$error = null;
$result = null;

$errors = [];

$requiredFields = [
    'CustomerPassword',
    'RequestOrderId',
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

        $orderStatusDetailGet = new OrderStatusDetailGet();
        $orderStatusDetailGet->setRequestOrderId($_POST['RequestOrderId']);

        try {
            $response = $orderStatusDetailGet->request();

            $result = "";
            $result .= 'JetonOrderId: ' . $response->getJetonOrderId() . '<br/>';
            $result .= 'RequestOrderID: ' . $response->getRequestOrderID() . '<br/>';
            $result .= 'TrackingNo: ' . $response->getTrackingNo() . '<br/>';
            $result .= 'Status: ' . $response->getStatus() . '<br/>';
            $result .= 'DeliveryDate: ' . $response->getDeliveryDate()->format('Y-m-d H:i:s') . '<br/>';
            $result .= 'PointCode: ' . $response->getPointCode() . '<br/>';
            $result .= 'PointName: ' . $response->getPointName() . '<br/>';
            $result .= 'PointAddress: ' . $response->getPointAddress() . '<br/>';
            $result .= 'NeighborhoodName: ' . $response->getNeighborhoodName() . '<br/>';
            $result .= 'BoroughName: ' . $response->getBoroughName() . '<br/>';
            $result .= 'CityName: ' . $response->getCityName() . '<br/>';
            $result .= 'BoxCount: ' . $response->getBoxCount() . '<br/>';
            $result .= 'DeciSum: ' . $response->getDeciSum() . '<br/>';
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
    <title>Bukoli Php Api Order Status Detail</title>

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
                <li><a href="index.php">Order Insert</a></li>
                <li class="active"><a href="javascript:;">Order Status Detail</a></li>
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
            <div class="col-sm-offset-3 col-sm-3">
                <input class="btn btn-bukoli" type="submit" name="submit" value="Submit">
            </div>
        </div>
    </form>

</div> <!-- /container -->


<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</body>
</html>
