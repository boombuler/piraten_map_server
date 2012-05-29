<div class="modal localmodaldlg" id="loginform">
    <div class="modal-header">
        <h3>Anmelden</h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formlogin" action="<?php echo System::getConfig('url')?>login.php" method="post">

            <div class="clearfix">
                <label for="username">Benutzer</label>
                <div class="input">
                    <input type="text" size="30" class="xlarge" name="username" id="username" />
                </div>
            </div>

            <div class="clearfix">
                <label for="password">Passwort</label>
                <div class="input">
                    <input type="password" size="30" class="xlarge" name="password" id="password" />
                                            <?php if ($canSendMail) { ?>
                    <span class="help-block">
                        <a href="#" onclick="javascript:closeModalDlg(false, function() {showModalId('newpassform');});">Passwort vergessen?</a> (Nur für nicht Wiki-Benutzer)
                    </span>
                                            <?php } ?>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:auth.login();">Anmelden</a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
    </div>
</div>