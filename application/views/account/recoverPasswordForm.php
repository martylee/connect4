<div class="register">
	<h3>Recover Password</h3>
		<?php 
			echo form_open('account/recoverPassword');
			echo form_label('Email');
			echo form_input('email',set_value('email'),"type='email',title='format must be a@b.com' required");
			echo "<br/><br/>";
			echo form_error('email');
			if (isset($errorMsg)) {
				echo "<p>" . $errorMsg . "</p>";
			}
			echo form_submit('submit', 'Recover Password');
			echo form_close();
		?>	
</div>

