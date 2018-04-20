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
		$order = json_decode(getCall($config->api_url . "emp-orders/" . $orderID . "?transform=1"), true);
		$customer = json_decode(getCall($config->api_url . "users/" . $order['userIDFK'] . "?transform=1"),true);
		$alertText = urlencode("Hi. " . $tg_user['username'] . ' made a new comment on your <a href="' . $config->app_url . "emp/order.php?order=" . $orderID . '"> order #'. $orderID . '</a>:' .chr(10) . $comment);
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $customer['telegramID'] . "&parse_mode=HTML&text=" . $alertText;		
		getCall($alertURL);
	}
	header('Location: ' . $config->app_url . 'emp/ordermgmt.php?order=' . $orderID);
}

if(isset($_GET['status'])){
	$status = $_POST['status'];
	$order = json_decode(getCall($config->api_url . "emp-orders/" . $orderID . "?transform=1"), true);
	$customer = json_decode(getCall($config->api_url . "users/" . $order['userIDFK'] . "?transform=1"),true);
	$orderArr = json_decode($order['products'], true);
	$orderArr['status'] = $status;
	$newJson = addslashes(json_encode($orderArr));
	$postfields = "{\n \t \"products\": \"$newJson\" \n }";
	$result = putCall($config->api_url . "emp-orders/" . $orderID, $postfields);
	$alertText = urlencode("Hi " . $customer['tgusername'] . chr(10) . '<a href="' . $config->app_url . "emp/order.php?order=" . $orderID . '">Your order #'. $orderID . '</a> has been updated.
		New status is: ' . $status);
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $customer['telegramID'] . "&parse_mode=HTML&text=" . $alertText;		
		getCall($alertURL);
	header('Location: ' . $config->app_url . 'emp/ordermgmt.php?order=' . $orderID);
	
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
	

$order = json_decode(getCall($config->api_url . "emp-orders/" . $orderID . "?transform=1"), true);
	
		$products = json_decode($order['products'], true);
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
					$badge = '<span class="badge badge-info">Delivery Pending</span>';
					break;
					case 'Ordered':
					$badge = '<span class="badge badge-danger">Ordered</span>';
					break;
				default:
					$badge = '<span class="badge badge-dark">Unknown</span>';
				break;
			}
		echo '<h1>Order #' . $orderID . ' ' .  $badge . '</h1>';
		$customer = json_decode(getCall($config->api_url . "users/" . $order['userIDFK'] . "?transform=1"),true);
		echo '<p class="desc">by <a href="tg://user?id=' . $customer['telegramID'] . '">' . $customer['tgusername'] . '</a></p>';
	
		echo '<ul>';
		foreach($products['products'] as $product){
			echo '<li><a href="https://emp-online.ch/search?q=' . $product .'" target="_blank"> Product #' . $product . '</a>';
		}
		echo '</ul>';
?> 
		<h2>Update Status</h2> 
		<form method="POST" action="?status=1&order=<?php echo $orderID;?>"> 
			<div class="form-group">
    			<label for="status">New status:</label>
    			<select class="form-control" id="status" name="status">
     	 			<option value="Processing">Processing</option>
					<option value="Ordered">Ordered</option>
      				<option value="Delivery">Delivery Pending</option>
      				<option value="Complete">Complete</option>
    			</select>
			  </div>
			  <button type="submit" class="btn btn-success">Submit</button>
		</form>
<?php

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
		?> 
		<h3>New comment</h3>
		<form action="?addcomment=1&order=<?php echo $orderID;?>" method="POST">
			<div class="form-group">
    			<label for="Newcomment">Your comment</label>
    			<textarea class="form-control" id="Newcomment" name="Newcomment" rows="3"></textarea>
			  </div>
			  <button type="submit" class="btn btn-success">Submit</button>

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

</div>
</main>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>