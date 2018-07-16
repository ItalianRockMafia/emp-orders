<?php
session_start();
$date = new DateTime();
require_once '../global/functions/apicalls.php';
require_once '../global/functions/telegram.php';
$config = require_once "../config.php";
require_once '../global/functions/irm.php';
$tg_user = getTelegramUserData();

require_once '../global/functions/header.php';
require_once '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | new order";
$header = getHeader($options);
$footer = renderFooter();

echo $header;


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
		$alertText = urlencode("Hi. " . $tg_user['username'] . ' made a new order on EMP via you. <a href="' . $config->app_url . "emp/ordermgmt.php?order=" . $orderComplete . '">View it online</a>');
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $bsc_member . "&parse_mode=HTML&text=" . $alertText;		
		getCall($alertURL);
		header('Location: https://italianrockmafia.ch/emp?order=complete');
	} else {
		header('Location: https://italianrockmafia.ch/emp?order=failed');
	}
}

?>

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
	<small id="Order help" class="form-text text-muted">
For example: 1234,567,89,0923,5</small>
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



<?php

echo $footer;