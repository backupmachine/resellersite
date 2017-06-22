<?php

include_once 'initrest.php';
include_once 'filters.php';

$args = filter_input_array(INPUT_GET, $purchase_request_filters);
$post_args = filter_input_array(INPUT_POST, $purchase_request_post_filters);

$definition_details = Array(
	'FREE_BACKUP' => '50MB of space, and a monthly schedule - $1000',
	'WEEKLY_BACKUP' => '2GB of space, and a weekly schedule - $100000',
	'DAILY_BACKUP' => '10GB of space, and a daily schedule - $10000000'
);

function purchase_request_valid($code)
{
	#Is customer code valid for this session / is this session still valid?
	#if ($purchase_details['customer_code'] ... )
	
	#Is the IP address of our customer the same as Backup Machine saw? 
	#This could be considered sufficient to say that the customer is logged in.
	#They are paying for this customer's purchase, so they have little to gain from this...
	
	$envip = $_SERVER['REMOTE_ADDR'];
	if ($purchase_details['client_ip'] != $client_ip)
	{
		#TODO: Log that this failed for debug purposes
		return False;
	}
	
	return True;
}


if (isset($args['prkey']))
{
	$purchase_key = $args['prkey'];
	$purchase_details = $rest_client->GetPurchaseRequestDetails($purchase_key);
	
	if (!purchase_request_valid($purchase_details))
	{
		#Placeholder for purchase request validation
		#Redirect customer through login page, and then back here
		echo "Invalid request";
		die();
	}
	
	$cancellation_url = $purchase_details['cancellation_url'];
	$available_codes = $purchase_details['available_package_definitions'];
	
	#Is this an upgrade request, or a purchase?
	if (isset($purchase_details['upgrade_purchase_code']))
	{
		#This is an upgrade of the package referenced by:
		# upgrade_purchase_code == the code supplied for the original purchase
		# upgrade_purchase_package_definition_code == the package definition code (e.g. WEEKLY_BACKUP) of the thing being upgraded 
		# upgrade_purchase_date == the date/time when the original purchase was made
		
		#TODO: Upgrade package
		echo "Not implemented yet";
		die();
	}
	else
	{
		#New purchase.  Store the request in our database, present the package selection and checkout options.
		#Here we'll just default to choosing a 'DAILY_BACKUP' option, and send the customer back to Backup Machine
		
		if (isset($post_args['selected_package_definition']))
		{
			#The purchase_code we specify here will come back if this package is upgraded.  It should mean something to you!
			$purchase_code = uniqid();
			#This is the selected package:
			$selected_package_definition = $post_args['selected_package_definition'];
			#If the payment has been successful, call approve, else call Deny
			try 
			{
				$response = $rest_client->ApprovePurchaseRequest($purchase_key, $selected_package_definition, $purchase_code);
				#$response = $rest_client->DenyPurchaseRequest($purchase_key, $selected_package_definition, $purchase_code);
				header('Location: '.$response['forward_url']);
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
		}
		else
		{
			?>
			<h3>Select a package for <?= $purchase_details['description'] ?>:</h3>
			<form method="post">
				<select name="selected_package_definition">
				<?php foreach ($purchase_details['available_package_definitions'] as $definition_code) { ?>
					<option value="<?= $definition_code ?>"><?= $definition_details[$definition_code] ?></option>
				<?php } ?>
				</select>
				<input type="submit" name="submit" value="Next &gt;&gt;">
			</form>
			
			<a href="<?= $purchase_details['cancellation_url'] ?>">Cancel purchase and return</a>
			<?php 
			var_dump($purchase_details);
		}
		
		exit();
	}
	
}
else
{
	#TODO: Something graceful here
	echo "Invalid request - please contact support";
}


?>