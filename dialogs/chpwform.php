<div class="modal localmodaldlg" id="chpwform">
    <div class="modal-header">
		<h3><?php echo _('Change Password'); ?></h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formchpw" action="login.php">
            <input type="hidden" name="action" value="changepwd" />

            <div class="clearfix">
				<label for="password"><?php echo _('New Password'); ?></label>
                <div class="input">
                    <input type="password" size="30" class="xlarge" name="newpass" />
                </div>
            </div>

            <div class="clearfix">
				<label for="password"><?php echo _('Repeat New Password'); ?></label>
                <div class="input">
                    <input type="password" size="30" class="xlarge" name="passconfirm" />
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:auth.changepwd();"><?php echo _('Accept New Password'); ?></a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);"><?php echo _('Cancel'); ?></a>
    </div>
</div>