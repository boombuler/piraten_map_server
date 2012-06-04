<div class="modal localmodaldlg" id="chpwform">
    <div class="modal-header">
        <h3>Passwort ändern</h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formchpw" action="<?php echo System::getConfig('url')?>login.php">
            <input type="hidden" name="action" value="changepwd" />

            <div class="clearfix">
                <label for="password">Neues Passwort</label>
                <div class="input">
                    <input type="password" size="30" class="xlarge" name="newpass" />
                </div>
            </div>

            <div class="clearfix">
                <label for="password">Neues Passwort wiederholen</label>
                <div class="input">
                    <input type="password" size="30" class="xlarge" name="passconfirm" />
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:auth.changepwd();">Passwort ändern</a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
    </div>
</div>