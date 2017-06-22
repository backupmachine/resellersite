<?php
include_once 'initrest.php';
include_once 'filters.php';

$error = '';

$args = filter_input_array(INPUT_POST, $signup_filters);

if (isset($args['email']))
{
	#We'll use the customer's email address as their username
	$args['brand'] = 'DEFAULT';
	
	#Here we should add this customer to our own database so that we can track payments against this guy in future.
	#Once we've got an ID, we can tie it together with the Backup Machine record by specifying a code.
	#Here, we'll just use a UUID instead.
	$args['code'] = uniqid('', true);
	
	#$args['first_name'] = utf8_encode($args['first_name']);
	
	#Process signup attempt
	try {
		$login_result = $rest_client->Signup($args);
		header('Location: login.php?mode=signup&username='.$args['username']);
		exit();
	}
	catch (RestErrorResponse $e) {
		$error = "Please correct the highlighted fields";
		$errordata = $e->data;
		/*foreach ($errordata as $key=>$value) {
			echo "$key -&gt; $value<br/>";
		}*/
	}	
	catch (Exception $e)
	{
		#Something unexpected happened, handle this gracefully + log / notify admins $e
		$error = "An unexpected error occurred: ".$e;
	}
}

include 'header.php';

?>

<?php 
if ($error)
{
	echo '<div class="error">'.htmlspecialchars($error).'</div>';
}

function render_field($errordata, $key, $description, $field_type)
{
	global $args;
?>
	<div <?php if (isset($errordata->$key)) echo 'class="error"'; ?>>
	<?= $description ?>: <input type="<?= $field_type ?>" name="<?= $key ?>" value="<?php if (isset($args[$key])) echo $args[$key]; ?>" />
	<?php 
	if (isset($errordata->$key)) { 
		$errors = $errordata->$key; 
		echo htmlspecialchars($errors[0]); 
	} 
	?>
	</div>
<?php 
}
?>

<form method="post">
<?php 
render_field($errordata, 'first_name', 'First Name', 'text');
render_field($errordata, 'last_name', 'Last Name', 'text');
render_field($errordata, 'email', 'E-mail', 'text');
render_field($errordata, 'username', 'Username', 'text');
render_field($errordata, 'password', 'Password', 'password');
?>
<div><input type="submit" value="Login" /></div>
</form>

<?php 
include 'footer.php';
?>