<div class="modal localmodaldlg" id="newpassform">
    <div class="modal-header">
        <h3><?php echo _('Reset Password'); ?></h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formnewpass" action="login.php">
			<fieldset>
				<input type="hidden" name="action" value="resetpwd" />
				<div class="clearfix">
					<label for="username"><?php echo  _('Username'); ?></label>
					<div class="input">
						<input type="text" size="30" class="xlarge" name="username" />
					</div>
				</div>

				<div class="clearfix">
					<label for="password"><?php echo _('E-Mail Address'); ?></label>
					<div class="input">
						<input type="text" size="30" class="xlarge" name="email"/>
					</div>
				</div>
			</fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:auth.resetpwd();"><?php echo _('Reset Password'); ?></a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);"><?php echo _('Cancel'); ?></a>
    </div>
</div>