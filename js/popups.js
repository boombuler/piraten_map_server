function createPopup(infos) {
	infos = jQuery.parseJSON(infos);
	result  = '<div class="modal" style="position: relative; top: auto; left: auto; margin: 0 auto; z-index: 9001">';
	result += '		<div class="modal-header">';
	result += '			<h3>'+infos.tb+'</h3>';
	result += '			<a href="#" onclick="javascript:closeModal();" class="close">&times;</a>';
	result += '		</div>';
	result += '		<div class="modal-body">';
	result += '			<form>';
	if (infos.i != null && infos.i != "") {
		result += '			<div class="clearfix">';
		result += '				<label>Bild</label>';
		result += '				<div class="input">';
		result += '					<a target="_blank" href="'+infos.i+'"><img class="photo" src="'+infos.i+'"></a>';
		result += '				</div>';
		result += '			</div>';
	}
    if (auth.isLoggedIn) {
		result += '				<div class="clearfix">';
		result += '					<label for="typ['+infos.id+']">Marker</label>';
		result += '					<div class="input">';
		result += '						<select class="xlarge" id="typ[' + infos.id + ']" name="typ[' + infos.id + ']">';
		for (var key in posterFlags) {
		  result += '				      <option value="' + key + '"';
		  if (infos.t == key) {
			result += ' selected="selected"';
		  }
			result += '>' + posterFlags[key] + '</option>';
		}
		result += '						</select>';
		result += '					</div>';
		result += '				</div>';
		result += '				<div class="clearfix">';
		result += '					<label for="city['+infos.id+']">Stadt</label>';
		result += '					<div class="input">';
		result += '						<input type="text" size="30" class="xlarge" name="city['+infos.id+']" id="city['+infos.id+']" value="'+infos.ci+'" />';
		result += '					</div>';
		result += '				</div>';
		result += '				<div class="clearfix">';
		result += '					<label for="street['+infos.id+']">Straße</label>';
		result += '					<div class="input">';
		result += '						<input type="text" size="30" class="xlarge" name="street['+infos.id+']" id="street['+infos.id+']" value="'+infos.s+'" />';
		result += '					</div>';
		result += '				</div>';
		result += '				<div class="clearfix">';
    }
	result += '					<label for="comment['+infos.id+']">Beschreibung</label>';
	result += '					<div class="input">';
	result += '						<textarea rows="3" cols="30" class="xlarge" name="comment['+infos.id+']" id="comment['+infos.id+']">'+infos.c+'</textarea>';
	result += '					</div>';
	result += '				</div>';
	url = infos.i || '';
	if (auth.isLoggedIn) {
		result += '				<div class="clearfix">';
		result += '					<label for="image['+infos.id+']">Bild URL</label>';
		result += '						<div class="input">';
		result += '							<input type="text" size="30" class="xlarge" name="image['+infos.id+']" id="image['+infos.id+']" value="'+url+'" />';
		result += '						</div>';
		result += '				</div>';
	}
	result += '				<div class="clearfix">';
	result += '					<div class="input">';
	result += '						<small>Zuletzt ge&auml;ndert von <b>'+infos.u+'</b><br />am <b>'+infos.d+'</b></small>';
	result += '					</div>';
	result += '				</div>';
	result += '			</form>';
	result += '		</div>';
	if (auth.isLoggedIn) {
		result += '		<div class="modal-footer">';
		result += '			<input type="button" value="Speichern" class="btn primary" onclick="javascript:change('+infos.id+')">';
		result += '			<input type="button" value="L&ouml;schen" class="btn danger" onclick="javascript:delid('+infos.id+')">';
		result += '		</div>';
		result += '</div>';
	}
	return result;
}
