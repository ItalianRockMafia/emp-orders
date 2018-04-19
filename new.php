<?php
session_start();
$date = new DateTime();
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";
require '../global/functions/irm.php';


if(isset($_GET['order'])){
	$order = $_POST['orders'];
	$bsc_member = $_POST['bsc-member'];
	$order_array['products'] = str_getcsv($order, ',');
	$order_array['bsc-member'] = $bsc_member;
	$order_array['status'] = "New";
	$order_json = addslashes(json_encode($order_array));
	$irmID = $_SESSION['irmID'];
	$postfields = "{\n \t \"userIDFK\": \"$irmID\", \n \t \"products\": \"$order_json\" \n }";
	$orderComplete = postCall($config->api_url . "emp-orders", $postfields);
	if(is_numeric($orderComplete)){
		header('Location: ' . $config->app_url . "emp?order=complete");
	} else {
		header('Location: ' . $config->app_url . "emp?order=failed");
	}
}

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
 	   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<link rel="stylesheet" href="../global/main.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://use.fontawesome.com/c414fc2c21.js"></script>
		<title>IRM - Meetup planer</title>
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
	$bsc_members = json_decode(getCall($config->api_url . "users?transform=1&filter=bsc,eq,1"), true);

	?>
	<h1>Enter your order</h1>
	<form method="POST" action="?order=1">
  <div class="form-group">
  <label for="orders">Enter article numbers, seperated by a comma (",")</label>
    <textarea class="form-control" id="orders" name="orders" rows="2"></textarea>
  </div>
  <div class="form-group">
	  <label for="bsc-member">Select a BSC member to order the products for you</label>
      <select id="bsc-member" name="bsc-member" class="form-control">
		<?php
			foreach($bsc_members['users'] as $bsc_member){
				echo '<option value=' . $bsc_member['telegramID'] . '>' . $bsc_member['tgusername'] . '</option>';
			}
		?>
      </select>
	</div>
	<button type="submit" class="btn btn-success">Order now</button>
	<small id="submitHelp" class="form-text text-muted">Your order is binding. If the order is not yet ordered at EMP you can canel it.</small>


</form>

<?php
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
			