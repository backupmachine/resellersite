<?php

include_once 'initrest.php';
include_once 'filters.php';

$args = filter_input_array(INPUT_GET, $cancellation_request_filters);

function cancellation_request_valid($code)
{
	#Is customer code valid for this session / is this session still valid?
	#if ($cancellation_details['customer_code'] ... )
	
	#Is the IP address of our customer the same as Backup Machine saw? 
	#This could be considered sufficient to say that the customer is logged in.
	#They are paying for this customer's purchase, so they have little to gain from this...
	
	$envip = $_SERVER['REMOTE_ADDR'];
	if ($cancellation_details['client_ip'] != $client_ip)
	{
		#TODO: Log that this failed for debug purposes
		return False;
	}
	
	return True;
}

$error = '';

if (isset($args['pckey']))
{
	$cancellation_key = $args['pckey'];
	try 
	{
		$cancellation_details = $rest_client->GetCancellationRequestDetails($cancellation_key);
		
		if (!cancellation_request_valid($cancellation_details))
		{
			#Placeholder for cancellation request validation
			#Redirect customer through login page, and then back here?
			$error = "Invalid request";
		}
		
		$back_url = $cancellation_details['back_url'];
	}
	catch (Pest_NotFound $e)
	{
		$error = 'Invalid request';
	}
	catch (Exception $e)
	{
		#Log $e
		$error = 'An error occurred - cannot proceed';		
	} 

	#New purchase.  Store the request in our database, present the package selection and checkout options.
	#Here we'll just default to choosing a 'DAILY_BACKUP' option, and send the customer back to Backup Machine
	
	if (isset($_POST['submit']))
	{
		try
		{
			$response = $rest_client->ApproveCancellation($cancellation_key);
			#$response = $rest_client->DenyCancellation($cancellation_key);
			header('Location: '.$cancellation_details['forward_url']);
			die();
		}
		catch (Exception $e)
		{
			#Log $e
			$error = 'Unexpected error approving cancellation';
		}
	}
}
else
{
	$error = "Invalid request - please contact support";
}

include 'header.php';

if ($error)
{
	echo '<div class="error">'.htmlspecialchars($error).'</div>';
}

if ($cancellation_details)
{
?>

<h3>Cancel package <?= $cancellation_details['description'] ?>?</h3>

<p>Warning!  We will immediately remove your backup history for this package.</p>

<form method="post">
	<input type="submit" name="submit" value="Next &gt;&gt;">
</form>

<a href="<?= $back_url ?>">&lt;&lt; Back</a>

<div class="rawoutput">
<?= var_dump($cancellation_details); ?>
</div>

<?php
}

include 'footer.php';
?>