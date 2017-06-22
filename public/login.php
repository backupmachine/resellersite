<?php
include_once 'initrest.php';
include_once 'filters.php';

$error = '';

$args = filter_input_array(INPUT_POST, $login_filters);

if (isset($args['username']))
{
	#Process login attempt
	try {
		$username = $args['username'];
		$login_result = $rest_client->Login($args['username'], $args['password']);
		#We can look up the customer in our database using the following key: 
		#$login_result['customer_code'];
		header('Location: '.$login_result['login_url']);
		exit();
	}
	catch (RestErrorResponse $e) {
		#It doesn't matter whether it's a username or password issue here.  Tell the customer one of them is invalid.
		$error = "Your username or password is incorrect";
		$errordata = $e->data;
	}	
	catch (Exception $e)
	{
		#Something unexpected happened, handle this gracefully + log / notify admins $e
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