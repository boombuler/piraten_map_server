<div class="modal localmodaldlg" id="registerform">
    <div class="modal-header">
        <h3><?php echo _('Register'); ?></h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formregister" action="login.php">
            <input type="hidden" name="action" value="register" />
            <div class="clearfix">
                <label for="username"><?php echo _('Username'); ?></label>
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
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:auth.register();"><?php echo _('Register'); ?></a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);"><?php echo _('Cancel'); ?></a>
    </div>
</div>