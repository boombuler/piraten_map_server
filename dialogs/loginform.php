<div class="modal localmodaldlg" id="loginform">
    <div class="modal-header">
		<h3><?php echo _('Login'); ?></h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formlogin" action="login.php" method="post">

            <div class="clearfix">
                <label for="username"><?php echo _('Username'); ?></label>
                <div class="input">
                    <input type="text" size="30" class="xlarge" name="username" id="username" />
                </div>
            </div>

            <div class="clearfix">
				<label for="password"><?php echo _('Password'); ?></label>
                <div class="input">
                    <input type="password" size="30" class="xlarge" name="password" id="password" />
<?php if (System::canSendMails()) { ?>
                    <span class="help-block">
						<a href="#" onclick="javascript:closeModalDlg(false, function() {showModalId('newpassform');});"><?php echo _('Send New Password'); ?></a>&nbsp;<?php echo _('Send New Password Hint'); ?>
                    </span>
<?php } ?>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
		<a href="#" class="btn primary" onclick="javascript:auth.login();"><?php echo _('Login'); ?></a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);"><?php echo _('Cancel'); ?></a>
    </div>
</div>