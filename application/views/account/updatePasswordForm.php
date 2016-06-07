		<script>
			function checkPassword() {
				var p1 = $("#pass1"); 
				var p2 = $("#pass2");
				
				if (p1.val() == p2.val()) {
					p1.get(0).setCustomValidity("");  // All is well, clear error message
					return true;
				}	
				else	 {
					p1.get(0).setCustomValidity("Passwords do not match");
					return false;
				}
			}
		</script>

	<h3>Change Password</h3>
<div class="register">
<?php 
	
	echo form_open('account/updatePassword');
	echo form_label('Old Password'); 	
	echo form_password('oldPassword',set_value('oldPassword'),"type='password' required");
	
	echo form_label('New Password'); 
	echo form_password('newPassword','',"id='pass1' type='password' required");
	
	echo form_label('Password again'); 
	echo form_password('passconf','',"id='pass2' type='password' required oninput='checkPassword();'");
	
	echo "<br/>";
	echo form_submit('submit', 'Change Password');
	echo form_error('oldPassword');
	echo form_error('newPassword');
	echo form_error('passconf');
	if (isset($errorMsg)) {
		echo "<p>" . $errorMsg . "</p>";
	}
	
	echo form_close();
?>	
</div>

