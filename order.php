<?php
session_start();
$orderID = $_GET['order'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
require '../global/functions/irm.php';
$config = require "../config.php";
$tg_user = getTelegramUserData();
saveSessionArray($tg_user);

if(isset($_GET['addcomment'])){
	$comment = $_POST['Newcomment'];
	$irmID = $_SESSION['irmID'];
	$commentPost = "{\n \t \"comment\": \"$comment\", \n \t \"authorIDFK\": \"$irmID\" \n }";
	$newComID = postCall($config->api_url . "comments", $commentPost);
	if(is_numeric($newComID)){
		$empPost = "{\n \t \"empIDFK\": \"$orderID\", \n \t \"commentIDFK\": \"$newComID\" \n }";
		postCall($config->api_url . "empComments", $empPost);
	}
	header('Location: ' . $config->app_url . 'emp/order.php?order=' . $orderID);
}

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
 	   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<link rel="stylesheet" href="../global/main.css">
			<link rel="stylesheet" href="travel.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://use.fontawesome.com/c414fc2c21.js"></script>
		<title>IRM - My order</title>
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
	$order = json_decode(getCall($config->api_url . "emp-orders/" . $orderID . "?transform=1"), true);
	if($order["userIDFK"] != $_SESSION['irmID']){
		echo '<div class="alert alert-danger" role="alert">
		<strong>Error!</strong> You do\'t have access to this order
	  </div>';
	} else {
		$products = json_decode($order['products'], true);
		switch ($products['status']) {
			case 'New':
				$badge = '<span class="badge badge-danger">New</span>';
				break;
			case 'New':
				$badge = '<span class="badge badge-success">Complete</span>';
				break;
			case 'Ordered':
				$badge = '<span class="badge badge-warning">Complete</span>';
				break;
			default:
				$badge = '<span class="badge badge-dark">Unknown</span>';
			break;
		}
		echo '<h1>Order #' . $orderID . ' ' .  $badge . '</h1>';
	
		echo '<ul>';
		foreach($products['products'] as $product){
			echo '<li><a href="https://emp-online.ch/search?q=' . $product .'"> Product #' . $product . '</a>';
		}
		echo '</ul>';
		echo '<h2>Comments</h2>';
		$ordercommentsID = json_decode(getCall($config->api_url . "empComments?transform=1&filter=empIDFK,eq," . $orderID), true);
		foreach($ordercommentsID['empComments'] as $commentIDs){
			$commRecs[] = $commentIDs['commentIDFK'];
		}
		if(!empty($commRecs)){

		
		$qrystr = "";
		foreach($commRecs as $commID){
			$qrystr .= $commID . ",";
		}
		$qrystr = rtrim($qrystr,",");
		$comments = json_decode(getCall($config->api_url . "comments/" . $qrystr . "?transform=1&order=commentID,asc"), true);
		if (!isset($comments[0])) $comments=[$comments];
 
		foreach($comments as $comment){
			$author = json_decode(getCall($config->api_url . "users/" . $comment['authorIDFK'] . "?transform=1"), true);
			echo '<div class="card">
			<div class="card-body">
			 '. $comment['comment'] .'
			 <footer class="blockquote-footer">'. $author['tgusername'].'</footer>			 
			</div>
		  </div>';
		}
	}else {
		echo '<div class="alert alert-warning" role="alert">
		No comments.
	  </div>';
	}
		?> <h3>New comment</h3>
		<form action="?addcomment=1&order=<?php echo $orderID;?>" method="POST">
			<div class="form-group">
    			<label for="Newcomment">Your comment</label>
    			<textarea class="form-control" id="Newcomment" name="Newcomment" rows="3"></textarea>
			  </div>
			  <button type="submit" class="btn btn-success">Submit</button>

		</form>

		
		<?php 




	}
	
} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}
?>

</div>
</main>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>