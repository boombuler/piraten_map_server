<div class="modal localmodaldlg" id="uploadimg">
    <div class="modal-header">
        <h3>Bild hochladen</h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form enctype="multipart/form-data" method="post" id="formimgup" action="image.php">
            <div class="clearfix">
                <label for="image">Bild hochladen</label>
                <div class="input">
                    <input type="file" id="image" name="image" class="xlarge" />
                    <input type="hidden" name="completed" value="1" />
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:document.forms['formimgup'].submit();">Hochladen</a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
    </div>
</div>