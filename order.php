<?php
session_start();
$orderID = $_GET['order'];
require_once '../global/functions/apicalls.php';
require_once '../global/functions/telegram.php';
require_once '../global/functions/irm.php';
$config = require_once "../config.php";
$tg_user = getTelegramUserData();
saveSessionArray($tg_user);

require_once '../global/functions/header.php';
require_once '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | order";
$header = getHeader($options);
$footer = renderFooter();

echo $header;


if(isset($_GET['addcomment'])){
	$comment = $_POST['Newcomment'];
	$irmID = $_SESSION['irmID'];
	$commentPost = "{\n \t \"comment\": \"$comment\", \n \t \"authorIDFK\": \"$irmID\" \n }";
	$newComID = postCall($config->api_url . "comments", $commentPost);
	if(is_numeric($newComID)){
		$empPost = "{\n \t \"empIDFK\": \"$orderID\", \n \t \"commentIDFK\": \"$newComID\" \n }";
		postCall($config->api_url . "empComments", $empPost);
		$order = json_decode(getCall($config->api_url . "emp-orders/" . $orderID . "?transform=1"),true);
		$orderArr = json_decode($order['products'],true);
		$bsc = $orderArr['bsc-member'];
		$alertText = urlencode("Hi. " . $tg_user['username'] . ' made a new comment on your <a href="' . $config->app_url . "emp/ordermgmt.php?order=" . $orderID . '"> assignment #'. $orderID . '</a>:' . chr(10) . $comment);
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $bsc . "&parse_mode=HTML&text=" . $alertText;		
		getCall($alertURL);
	}
	header('Location: https://italianrockmafia.ch/emp/order.php?order=' . $orderID);
}

?>

<div class="topspacer"></div>
<main role="main">
	<div class="container">

<?php

saveSessionArray($tg_user);
$access = $_SESSION['access'];

if ($tg_user !== false) {
	if($access >= "2"){

	$order = json_decode(getCall($config->api_url . "emp-orders/" . $orderID . "?transform=1"), true);
	if($order["userIDFK"] != $_SESSION['irmID']){
		echo '<div class="alert alert-danger" role="alert">
		<strong>Error!</strong> You do\'t have access to this order
	  </div>';
	} else {
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
	
		echo '<ul>';
		foreach($products['products'] as $product){
			echo '<li><a href="https://emp-online.ch/search?q=' . $product .'" target="_blank"> Product #' . $product . '</a>';
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
	echo '<div class="alert alert-warning" role="alert">
	<strong>Warning.</strong> You need don\'t have access to this event.
	</div>';
}
} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}

echo $footer;
?>
