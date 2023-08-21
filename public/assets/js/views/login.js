document.addEventListener('DOMContentLoaded', function () {
	let _save,
		response,
		form = '#login-form',
		_form = $fs(form),
		_fuseVerifyForm = $fs('#verify-picfuse-form'),
		_initPicfuseAuth = $fs('#start-picfuse-auth'),
		_messageWrapper = $fs(globalMessageTag).validator;

	_form.validator.initFormValidation({
		config: {
			validateEmail: true
		}
	}).upon('submit', function (e) {
		e.preventDefault();
		_form.handleFormSubmit({ uri: _form.action, method: _form.formMethod }).then(resp => {
			if (resp.JSON.status === 200) {
				response = resp;
				image.src = resp.JSON.fuse;
				image.style.objectFit = 'cover';

				setTimeout(() => _initPicfuseAuth.step('click'), 2500);
			}
		}).catch(e => console.log(e));
	});

	_fuseVerifyForm.upon('submit', function (e) {
		e.preventDefault();
		const formData = new FormData(_fuseVerifyForm[0]), data = { picfuseArray: fuseIdArray, user: response.JSON.user };
		Object.keys(data).forEach(key => formData.append(key, data[key]));

		fetchReq({
			uri: _fuseVerifyForm.action, method: _fuseVerifyForm.formMethod, data: formData, beforeSend: () => {
				_form.toggleSubmitButtonState();
			}, onError: (err, status) => {
				setTimeout(() => {
					_messageWrapper
						.renderMessage(fa_exc_c, alert_d, 'Server error occurred, please try again.', null, null, true)
						.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
					_form.toggleSubmitButtonState(true);
				}, 1500);
			}, onComplete: xhr => {
				let response = xhr.responseJSON,
					status = xhr.status;

				setTimeout(() => {
					if (status === 308)
						_messageWrapper
							.renderMessage(fa_check_c, alert_s, response.message, null, null, false, true)
							.slideInDown(800).then(() => setTimeout(() => location.href = response.redirect, 3500));
					else {
						_messageWrapper
							.renderMessage(fa_exc_c, alert_d, response.message, null, null, true)
							.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
						_form.toggleSubmitButtonState(true);
					}
				}, 1500);
			}
		});
	});

	_initPicfuseAuth.modal.onClickOpen({
		onComplete: (e) => {
			const _modal = e.response;
			cutImageUp(image, '#picfuse-wrapper').then(() => {
				_modal.onBSModalClose(e => {
					_save = $fs('button', e.currentTarget);

					if (!$fs('.close-modal', e.currentTarget).mouseIsOver()) {
						if (_save.mouseIsOver()) {
							if (fuseIdArray.length < 4) {
								e.preventDefault();
								_messageWrapper
									.renderMessage(fa_exc_c, alert_d, 'You must choose the sequence of 4 image tiles chosen during registration.', null, null, true)
									.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 3500));
							} else
								_fuseVerifyForm.step('submit');
						} else
							e.preventDefault();
					} else
						fuseIdArray = [];
				});
			});
			_form.toggleSubmitButtonState(true);
			response.messageTag.html.insert(null);
		}
	});
});
