document.addEventListener('DOMContentLoaded', function () {
	let form = '#recreate-pattern-form',
		_form = $fs(form);
	
	_form.validator.initFormValidation({config: {useDefaultStyling: false}}).upon('submit', function (e) {
		e.preventDefault();
		if (currentImage && currentImage.length)
			_form.handleFormSubmit({uri: _form.action, method: _form.formMethod, data: {picfuseArray: fuseIdArray}}).then(response => {
				console.log(response)
			}).catch(e => console.log(e));
		else
			_picfuseAuthValid.validator.renderMessage(fa_exc_c, alert_d, 'Please Select an Image', null, null, true);
	});
	initNewImageCutupSequence();
});
