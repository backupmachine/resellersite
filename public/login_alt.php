<?php
include_once 'initrest.php';
include_once 'filters.php';

$error = '';

$args = filter_input_array(INPUT_POST, $login_filters);

function get_customer_code($username, $password)
{
	return "EXAMPLE_CUSTOMER_CODE";
}

if (isset($args['username']))
{
	$username = $args['username'];
	$password = $args['password'];
	
	#The following function does something appropriate if the username/password is incorrect
	$customer_code = get_customer_code($username, $password);
	
	#Process login attempt
	try {
		$login_result = $rest_client->LoginCustomerByCode($customer_code);
		#We can look up the customer in our database using the following key: 
		#$login_result['customer_code'];
		header('Location: '.$login_result['login_url']);
		exit();
	}
	catch (Pest_NotFound $e)
	{
		#This means the customer code is not recognised by Backup Machine.
		$error = "Account not recognised";
	}
	catch (Exception $e)
	{
		#Something unexpected happened, handle this gracefully + log var_dump($e->data) / notify admins
		var_dump($e->data);
		$error = "An unexpected error occurred";
	}
}
else {
	if (isset($_GET['username']))
	{
		$username = $_GET['username'];
	}
}

include 'header.php';

?>

<?php 
if ($error)
{
	echo '<div class="error">'.htmlspecialchars($error).'</div>';
}
if (isset($_GET['mode']))
{
	$mode = $_GET['mode'];
	if ($mode == 'signup')
	{
		?>
		<h3>Signup successful.  Please log in.</h3>
		<?php 
	}
}
?>

<form method="post">
<div>Username: <input type="text" name="username" value="<?= htmlspecialchars($username) ?>"/></div>
<div>Password: <input type="password" name="password" /></div>
<div><input type="submit" value="Login" /></div>
</form>

<?php 
include 'footer.php';
?>