
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
	
	<div class="register">
	<h3>New Account</h3>
<?php 
	echo form_open('account/createNew');
	echo form_label('Username'); 
	
	echo form_input('username',set_value('username'),"required pattern='^[a-zA-Z0-9]+$'");
	echo form_label('Password'); 
	
	echo form_password('password','',"id='pass1' type='password' required");
	echo form_label('Password again'); 
	
	echo form_password('passconf','',"id='pass2' type='password' required oninput='checkPassword();'");
	echo form_label('First');
	
	echo form_input('first',set_value('first'),"required");
	echo form_label('Last');
	
	echo form_input('last',set_value('last'),"required");
	echo form_label('Email');
	
	echo form_input('email',set_value('email'),"  type='email' required");
	
	
	echo "<div align='center'>";	
	echo "<p>Please enter the content below</p>";
	echo form_input('verify',"", "id='verify' required");
		echo "<img id='captcha' src='".site_url('account/securimage');
	echo "' alt='Captcha Image'/>";
	echo "<a href='' onclick='document.getElementById(\'captcha\').src =
			\'/securimage/securimage_show.php?\'
			+ Math.random(); return false;'>Change</a>";
	echo "</div>";
	echo form_submit('submit', 'Register');
	echo form_close();
	echo "<p class='errMsg'>".form_error('username');
	echo form_error('password');
	echo form_error('passconf');
	echo form_error('first');
	echo form_error('last');
	echo form_error("verify");	
	echo form_error('email')."</p>";
	
?>	
</div>
