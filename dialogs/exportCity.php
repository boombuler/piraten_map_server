<div class="modal" style="position: relative; top: auto; left: auto; margin: 0 auto; display:none;" id="exportCity">
    <div class="modal-header">
        <h3>Plakate exportieren</h3>
        <a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
    </div>
    <div class="modal-body">
        <form enctype="multipart/form-data" method="get" id="formexpup" action="export.php">
            <div class="clearfix">
                <label for="image">Welche Stadt?</label>
                <div class="input">
                    <select id="city" name="city"><?php
$cities = System::query("SELECT DISTINCT city FROM " . System::getConfig('tbl_prefix') . "felder WHERE city is not null and city <> ''");
if ($cities) {
while ($row = $cities->fetch())
    print '<option>' . $row->city . '</option>';
}
                    ?></select>
                    <input type="hidden" name="completed" value="1" />
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn primary" onclick="javascript:document.forms['formexpup'].submit();">Download</a>
        <a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
    </div>
</div>