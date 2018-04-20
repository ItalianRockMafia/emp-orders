<?php
session_start();
$date = new DateTime();
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";
require '../global/functions/irm.php';
$tg_user = getTelegramUserData();

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
 	   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<link rel="stylesheet" href="../global/main.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://use.fontawesome.com/c414fc2c21.js"></script>
		<title>IRM - EMP orders</title>
	</head>
	<body>


	<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
	<a class="navbar-brand" href="https://italianrockmafia.ch">ItalianRockMafia</a>
	  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	  </button>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
		<li class="nav-item">
        				<a class="nav-link" href="https://italianrockmafia.ch/main.php">Home</a>
      				</li>
							<li class="nav-item">
        				<a class="nav-link" href="https://italianrockmafia.ch/settings.php">Settings</a>
      				</li>
			  <li class="nav-item">
				<a class="nav-link" href="https://italianrockmafia.ch/meetup">Events</a>
				</li>
				<li class="nav-item active">
				<a class="nav-link" href="https://italianrockmafia.ch/emp">EMP <span class="sr-only">(current)</span></a>
			  </li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
				<li class="nav-item">
        			<a class="nav-link" href="https://italianrockmafia.ch/login.php?logout=1">Logout</a>
      			</li>
		</ul>
	</div>
</nav>
<div class="topspacer"></div>
<main role="main">
	<div class="container">
	<?php

saveSessionArray($tg_user);
if ($tg_user !== false) {
	?>
<h1>EMP-Orders</h1>
<p class="desc">With this tool, you can order EMP-Products through a EMP-Backstage club memeber, wich means free shipping for you.</p>
<h2>Your orders <a href="new.php"><i class="fa fa-plus-circle righticon" aria-hidden="true"></i></a></h2>
<?php
	$my_orders = json_decode(getCall($config->api_url ."emp-orders?transform=1&filter=userIDFK,eq," . $_SESSION['irmID']), true);
	if(empty($my_orders['emp-orders'])){
			echo '<div class="alert alert-warning" role="alert">
			You have no orders.
		</div>';
	} else{
		echo '<div class="list-group">';
			foreach($my_orders['emp-orders'] as $my_order){
				$order = json_decode($my_order['products'],true);
				switch ($order['status']) {
					case 'New':
						$badge = '<span class="badge badge-primary">New</span>';
						break;
					case 'Complete':
						$badge = '<span class="badge badge-success">Complete</span>';
						break;
					case 'Processing':
						$badge = '<span class="badge badge-warning">Processing</span>';
						break;
					case 'Delivery':
						$badge = '<span class="badge badge-Info">Delivery Pending</span>';
						break;
						case 'Ordered':
						$badge = '<span class="badge badge-danger">Ordered</span>';
						break;
					default:
						$badge = '<span class="badge badge-dark">Unknown</span>';
					break;
				}
				echo '<a href="order.php?order=' . $my_order['empID'] . '" class="list-group-item list-group-item-action"> Order Nr. #' . $my_order['empID'] . ' '.
				$badge . '</a>';
			}
		echo '</div>';
	}
	$irmUser = json_decode(getCall($config->api_url . "users/" . $_SESSION['irmID'] . "?transfor=1"),true);
	if($irmUser['bsc'] == "1"){
		echo '<h1>Orders for you</h1>';
		$allOrders = json_decode(getCall($config->api_url . "emp-orders?transform=1"),true);
		foreach($allOrders['emp-orders'] as $order){
			$products = json_decode($order['products'], true);
			if($products['bsc-member'] == $tg_user['id']){
			echo '<div class="list-group">';
					switch ($products['status']) {
						case 'New':
							$badge = '<span class="badge badge-primary">New</span>';
							break;
						case 'Complete':
							$badge = '<span class="badge badge-success">Complete</span>';
							break;
						case 'Processing':
							$badge = '<span class="badge badge-warning">Processing</span>';
							break;
						case 'Delivery':
							$badge = '<span class="badge badge-Info">Delivery Pending</span>';
							break;
							case 'Ordered':
							$badge = '<span class="badge badge-danger">Ordered</span>';
							break;
						default:
							$badge = '<span class="badge badge-dark">Unknown</span>';
						break;
					}
					echo '<a href="order.php?order=' . $order['empID'] . '" class="list-group-item list-group-item-action"> Order Nr. #' . $order['empID'] . ' '.
					$badge . '</a>';	
			}	
			
			
		}
		echo '</div>';
	}
} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}
?>

<!-- Modal coming soon -->
<div class="modal fade" id="comingSoon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Coming Soon</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        This feature is still in development.
      </div>
      <div class="modal-footer">
			<button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>




	</div>
			</main>
			<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
				</body>
			</html>
