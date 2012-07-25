<div class="modal localmodaldlg" id="uploadimg">
    <div class="modal-header">
        <h3><?php echo _('Upload Image'); ?></h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form enctype="multipart/form-data" method="post" id="formimgup" action="image.php">
            <div class="clearfix">
                <label for="image"><?php echo _('Image'); ?></label>
                <div class="input">
                    <input type="file" id="image" name="image" class="xlarge" />
                    <input type="hidden" name="completed" value="1" />
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:document.forms['formimgup'].submit();"><?php echo _('Upload'); ?></a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);"><?php echo _('Cancel'); ?></a>
    </div>
</div>