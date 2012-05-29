<div class="modal localmodaldlg" id="newpassform">
    <div class="modal-header">
        <h3>Neues Passwort</h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form id="formnewpass" action="<?php echo System::getConfig('url')?>register.php" method="post">
            <input type="hidden" name="action" value="resetpw" />
            <div class="clearfix">
                <label for="username">Benutzer</label>
                <div class="input">
                    <input type="text" size="30" class="xlarge" name="username" />
                </div>
            </div>

            <div class="clearfix">
                <label for="password">EMail Adresse</label>
                <div class="input">
                    <input type="text" size="30" class="xlarge" name="email"/>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:document.forms['formnewpass'].submit();">Passwort anfordern</a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
    </div>
</div>