
<!DOCTYPE html>

<html>
<link href="<?echo base_url();?>css/template.css" rel="stylesheet" type="text/css"/>
	<head>
		<style>
			input {
				display: block;
			}
		</style>

	</head> 
<body> 
    <img src="<?= base_url()?>images/Connect_Four.gif"/> 
	<div class="register">
    <h3 align="center">Login</h3>
<?php 
	echo form_open('account/login');
	echo "<div>";
		echo form_label('Username'); 
		
		echo form_input('username',set_value('username'),"required title='only letters and numbers allowed' pattern='^[a-zA-Z0-9]+$'");
		echo form_label('Password'); 
		
		echo form_password('password','',"type='password' required");	
		echo "<div align='right'>";
			echo form_submit('submit', 'Login');	
		echo "</div>";
		if (isset($errorMsg)) {
			echo "<p>" . $errorMsg . "</p>";
		}
		echo form_error('username');
		echo form_error('password');
	echo "</div>";
	echo "<div align='center'><p>" . anchor('account/newForm','Create Account') . "</p>";
	echo "<p>" . anchor('account/recoverPasswordForm','Recover Password') . "</p></div>";	
	echo form_close();
?>	



</body>
</html>

