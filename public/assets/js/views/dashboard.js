// Initialize Variables
let response,
	_fuseVerifyForm,
	_globalMessageWrapper = $fs(globalMessageTag).validator;

// On opening the Email verification Modal
$fs('.open-modal').modal.onClickOpen({
	onCompleted: (e) => {
		let initialXHR;
		const _modal = e.response, _verifyEmailForm = $fs('#verify-email-form', _modal), _OTPWrapper = $fs('#otp-wrapper');
		
		// Initialize For Validator for email form
		_verifyEmailForm.validator.initFormValidation({
			config: {
				validateEmail: true,
				validatePassword: true
			}
		}).upon('submit', function (e) { // On form submit
			e.preventDefault(); // Prevent default reloading action
			
			// Let the validator plugin handle the forms validation
			$fs(this).handleFormSubmit().then(response => {
				initialXHR = response;
				
				// Show the OTP form on success else display the error message
				response.JSON.status === 200 && $fs('#load-otp-form').step('click')
			}).catch(error => console.log(error));
		});
		
		// On show the OTP form
		$fs('#load-otp-form').upon('click', function (e) {
			e.preventDefault();
			// On show OTP form
			_OTPWrapper.loadPageData({uri: _OTPWrapper.dataAttribute('load-page')}).then(e => {
				_initPicfuseAuth = $fs('#start-picfuse-auth');
				_fuseVerifyForm = $fs('#verify-picfuse-form');
				
				// On submission of the selected image sequences
				_fuseVerifyForm.upon('submit', function (e) {
					e.preventDefault(); // Prevent default reloading action
					const formData = new FormData(_fuseVerifyForm[0]), data = {picfuseArray: fuseIdArray, user: response.responseJSON.user, other_actions: true}; // Prepare the data returned from selecting the sequences
					Object.keys(data).forEach(key => formData.append(key, data[key]));
					
					// Send the data to the back end via the Fetch API
					fetchReq({
						uri: _fuseVerifyForm.action, method: _fuseVerifyForm.formMethod, data: formData, beforeSend: () => {
						}, onError: () => {
							// Display error if there's any
							setTimeout(() => {
								_globalMessageWrapper
									.renderMessage(fa_exc_c, alert_d, 'Server error occurred, please try again.', null, null, true)
									.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
							}, 1500);
						}, onComplete: xhr => {
							let response = xhr.responseJSON,
								status = xhr.status;
							
							// On success
							setTimeout(() => {
								if (status === 201)
									_globalMessageWrapper.renderMessage(fa_check_d, alert_s, response.message, null, null, true).slideInDown(800)
										.then(_wrapper => setTimeout(() => {
											// Authorize final action [Email verification]
											const formData = new FormData(), data = {picfuseArray: fuseIdArray, email: initialXHR.JSON.email, action: 'complete-verify-email'};
											Object.keys(data).forEach(key => formData.append(key, data[key]));
											
											fetchReq({
												uri: _verifyEmailForm.action,
												method: _verifyEmailForm.formMethod,
												data: formData,
												onComplete: (resp, status) => {
													if (status === 308)
														_globalMessageWrapper.renderMessage(fa_exc_c, alert_i, response.message, null, null, true)
															.slideInDown(800)
															.then(_wrapper => setTimeout(() => location.href = resp.responseJSON.redirect, 5000));
													else
														_globalMessageWrapper
															.renderMessage(fa_exc_c, alert_d, resp.responseJSON.message, null, null, true)
															.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
												}
											});
											/*_wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null))*/
										}, 1500));
								else {
									_globalMessageWrapper
										.renderMessage(fa_exc_c, alert_d, response.message, null, null, true)
										.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
								}
							}, 1500);
						}
					});
				});
				
				_initPicfuseAuth.modal.onClickOpen({
					beforeOpen: (e) => console.log(`Init Opening ${e}`),
					onComplete: (e) => {
						console.log(`Opening ${e}`)
						const _modal = e.response;
						cutImageUp(image, '#picfuse-wrapper').then(() => {
							_modal.onBSModalClose(e => {
								const _save = $fs('button', e.currentTarget);
								
								if (!$fs('.close-modal', e.currentTarget).mouseIsOver()) {
									if (_save.mouseIsOver()) {
										if (fuseIdArray.length < 4) {
											e.preventDefault();
											_globalMessageWrapper
												.renderMessage(fa_exc_c, alert_d, 'You must choose the sequence of 4 image tiles chosen during registration.', null, null, true)
												.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 3500));
										} else {
											_fuseVerifyForm.step('submit');
											newBsModal(_modal[0]).dispose();
											$fs('#temp-modal-wrapper')[0].remove();
										}
									} else
										e.preventDefault();
								} else {
									fuseIdArray = [];
									newBsModal(_modal[0]).dispose();
									$fs('#temp-modal-wrapper')[0].remove();
								}
							});
						});
						setTimeout(() => $fs(globalMessageTag).slideOutUp(800).then(wrapper => wrapper.html.insert(null)), 4500);
					}
				});
				
				$fs('#resend-otp').upon('click', function (e) {
					e.preventDefault();
					const formData = new FormData(), data = {email: initialXHR.JSON.email, action: 'verify-email'};
					Object.keys(data).forEach(key => formData.append(key, data[key]));
					
					fetchReq({
						uri: _verifyEmailForm.action,
						method: _verifyEmailForm.formMethod,
						data: formData,
						beforeSend: () => {
							$fs('.pinlogin-field').disable()
							$fs('#resend-otp').disable()
						},
						onError: (e) => _globalMessageWrapper.renderMessage(fa_exc_c, alert_i, `An error occurred: ${e.message ?? 'Failed to resend. Please try again'}`)
							.slideInDown()
							.then(wrapper => setTimeout(() => wrapper.slideOutUp(), 3500)),
						onSuccess: (e) => {
							_globalMessageWrapper.renderMessage(fa_check_d, alert_s, `${e.responseJSON.message ?? 'OTP resent successfully'}`)
								.slideOutUp(0).then(wrapper => {
								wrapper.slideInDown(800).then(wrapper => setTimeout(() => wrapper.slideOutUp(), 4500));
								loadOTP()
							});
						},
						onComplete: () => $fs('#resend-otp').disable(true)
					});
				});
				
				setTimeout(() => $('.forms').animate({
					height: 'toggle',
					fade: 'toggle',
				}, () => {
					
					_verifyEmailForm.validator.messageTag.html.insert(null);
					_verifyEmailForm.toggleSubmitButtonState(true);
					
					$('#otp').pinlogin({
						fields: 4,
						reset: false,
						hideinput: false,
						complete: function (otp) {
							const formData = new FormData(), data = {otp: otp, email: initialXHR.JSON.email, action: 'verify-otp'};
							Object.keys(data).forEach(key => formData.append(key, data[key]));
							
							fetchReq({
								uri: _verifyEmailForm.action,
								method: _verifyEmailForm.formMethod,
								data: formData,
								beforeSend: () => {
									$fs('.pinlogin-field').disable()
									$fs('#resend-otp').disable()
								},
								onSuccess: (e) => {
									_globalMessageWrapper.renderMessage(fa_check_d, alert_s, `${e.responseJSON.message ?? 'Verification Successful. Please Wait'}`).slideInDown(800);
									
									if (e.status === 200)
										setTimeout(() => $fs(globalMessageTag).slideOutUp(800).then(wrapper => {
											location.reload();
											wrapper.html.insert(null)
										}), 4500);
									else {
										response = e;
										const tempWrapper = document.createElement('div')
										tempWrapper.id = 'temp-modal-wrapper';
										$fs('body')[0].append(tempWrapper);
										
										console.log(e)
										image.src = e.responseJSON.fuse;
										image.style.objectFit = 'cover';
										setTimeout(() => _initPicfuseAuth.step('click'), 1500);
									}
								},
								onComplete: (e, status) => {
									if (status === 422)
										_globalMessageWrapper.renderMessage(fa_exc_c, alert_i, `An error occurred: ${e.responseJSON.message ?? 'OTP incorrect or Server error. Please try again'}`)
											.slideInDown(800)
											.then(wrapper => setTimeout(() => wrapper.slideOutUp(800).then(wrapper => wrapper.html.insert(null)), 6000));
									
									$fs('.pinlogin-field').disable(true);
									$fs('#resend-otp').disable(true);
								}
							});
						}
					});
				}), 1500);
			});
			loadOTP();
		});
	}
});



