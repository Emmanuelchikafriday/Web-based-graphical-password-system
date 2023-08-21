document.addEventListener('DOMContentLoaded', function () {
	let form = '#register-form',
		_form = $fs(form),
		_messageWrapper = $fs(globalMessageTag).validator;

	_form.validator.initFormValidation({
		config: {
			validateName: true,
			validateEmail: true,
			validatePhone: false,
		}
	}).upon('submit', function (e) {
		e.preventDefault();
		if ($fs('input#accept').property('checked'))
			if (currentImage && currentImage.length)
				_form.handleFormSubmit({ uri: _form.action, method: _form.formMethod, data: { picfuseArray: fuseIdArray } }).then(response => {
					console.log(response)
				}).catch(e => console.log(e));
			else
				_picfuseAuthValid.validator.renderMessage(fa_exc_c, alert_d, 'Please Select an Image', null, null, true);
		else
			_messageWrapper
				.renderMessage(fa_exc_c, alert_d, 'You must Accept our TOS and Privacy Policy.', null, null, true)
				.slideInDown(1500).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(1500).then(_wrapper => _wrapper.html.insert(null)), 4500));
	});
	initNewImageCutupSequence();
});
