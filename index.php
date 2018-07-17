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
$options['title'] = "IRM | EMP orders";
$header = getHeader($options);
$footer = renderFooter();

echo $header;
?>

<div class="topspacer"></div>
<main role="main">
	<div class="container">
	<?php

saveSessionArray($tg_user);
$access = $_SESSION['access'];
if ($tg_user !== false) {
	if($access >= "2"){
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
						$badge = '<span class="badge badge-info">Delivery Pending</span>';
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
							$badge = '<span class="badge badge-info">Delivery Pending</span>';
							break;
							case 'Ordered':
							$badge = '<span class="badge badge-danger">Ordered</span>';
							break;
						default:
							$badge = '<span class="badge badge-dark">Unknown</span>';
						break;
					}
					echo '<a href="ordermgmt.php?order=' . $order['empID'] . '" class="list-group-item list-group-item-action"> Order Nr. #' . $order['empID'] . ' '.
					$badge . '</a>';	
			}	
			
			
		}
		echo '</div>';
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