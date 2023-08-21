// On Document Load
document.addEventListener('DOMContentLoaded', function () {
	// Initialize Variables
	let _save,
		response,
		form = '#make-transaction-form',
		_form = $fs(form),
		_fuseVerifyForm = $fs('#verify-picfuse-form'),
		_initPicfuseAuth = $fs('#start-picfuse-auth'),
		_globalMessageWrapper = $fs(globalMessageTag).validator;
	
	if (_form.length) {
		// Initialize For Validator for deposit and withdrawal form
		_form.validator.initFormValidation({
			config: {
				validateEmail: true
			}
		}).upon({
			// Prevent inputs that are not numeric [For deposit and withdrawal Forms]
			keypress: function (e) {
				const keyMatch = e.key.match(/[\Wa-zA-Z]/gi);
				(keyMatch && keyMatch.length) && e.preventDefault();
			},
			// On form submit
			submit: function (e) {
				e.preventDefault(); // Prevent default reloading action
				
				// Let the validator plugin handle the forms validation
				_form.handleFormSubmit({uri: _form.action, method: _form.formMethod}).then(resp => {
					if (resp.JSON.status === 200) {
						response = resp;
						image.src = resp.JSON.fuse; // Grab the Users' uploaded authentication image
						image.style.objectFit = 'cover'; // Style the image
						
						// Programmatically click on the button to open the Authentication modal
						setTimeout(() => _initPicfuseAuth.step('click'), 1500);
					}
				}).catch(e => console.log(e));
			}
		});
		
		// On submission of the selected image sequences
		_fuseVerifyForm.upon('submit', function (e) {
			e.preventDefault(); // Prevent default reloading action
			const formData = new FormData(_fuseVerifyForm[0]), data = {picfuseArray: fuseIdArray, user: response.JSON.user, other_actions: true}; // Prepare the data returned from selecting the sequences
			Object.keys(data).forEach(key => formData.append(key, data[key]));
			
			// Send the data to the back end via the Fetch API
			fetchReq({
				uri: _fuseVerifyForm.action, method: _fuseVerifyForm.formMethod, data: formData, beforeSend: () => {
					_form.toggleSubmitButtonState();
				}, onError: (err, status) => {
					// Display error if there's any
					setTimeout(() => {
						_globalMessageWrapper
							.renderMessage(fa_exc_c, alert_d, 'Server error occurred, please try again.', null, null, true)
							.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
						_form.toggleSubmitButtonState(true);
					}, 1500);
				}, onComplete: xhr => {
					let response = xhr.responseJSON,
						status = xhr.status;
					
					setTimeout(() => {
						// On success
						if (status === 201)
							_globalMessageWrapper.renderMessage(fa_check_c, alert_s, response.message, null, null, false, true).slideInDown(800)
								.then(() => setTimeout(() => {
									// Authorize final action [Deposit or Withdrawal]
									const formData = new FormData(), data = {action: _form.dataAttribute('final-action')};
									Object.keys(data).forEach(key => formData.append(key, data[key]));
									
									// Send request to backend to complete the requested action [Deposit or Withdrawal]
									fetchReq({
										uri: _form.action,
										method: _form.formMethod,
										data: formData,
										onError: resp => _globalMessageWrapper
											.renderMessage(fa_wifi_s, alert_d, 'Server Error occurred', null, null, true)
											.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800)
												.then(_wrapper => {
													_wrapper.html.insert(null);
													_form.toggleSubmitButtonState(true);
												}), 4500)),
										onComplete: (resp, status) => {
											// On complete
											if (status === 308)
												// Display success message and redirect to designated page
												_globalMessageWrapper.renderMessage(fa_exc_c, alert_i, resp.responseJSON.message, null, null, true)
													.slideInDown(800)
													.then(_wrapper => setTimeout(() => location.href = resp.responseJSON.redirect, 5000));
											else
												// Display error if any
												_globalMessageWrapper
													.renderMessage(fa_exc_c, alert_d, resp.responseJSON.message, null, null, true)
													.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800)
													.then(_wrapper => {
														_wrapper.html.insert(null);
														_form.toggleSubmitButtonState(true);
													}), 4500));
										}
									});
									// location.href = response.redirect
								}, 2500));
						else {
							// Display error message if any
							_globalMessageWrapper
								.renderMessage(fa_exc_c, alert_d, response.message, null, null, true)
								.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
							_form.toggleSubmitButtonState(true);
						}
					}, 1500);
				}
			});
		});
		
		// Open the Authentication modal
		_initPicfuseAuth.modal.onClickOpen({
			onComplete: (e) => {
				const _modal = e.response;
				// Call the function to segment the images
				cutImageUp(image, '#picfuse-wrapper').then(() => {
					_modal.onBSModalClose(e => {
						_save = $fs('button', e.currentTarget);
						
						// If the close button is not clicked
						if (!$fs('.close-modal', e.currentTarget).mouseIsOver()) {
							// If the save button is clicked
							if (_save.mouseIsOver()) {
								// If selected segments not upto 4
								if (fuseIdArray.length < 4) {
									e.preventDefault(); // Prevent close action for the modal
									//Display error message: Tell the user 4 segments must be selected
									_globalMessageWrapper
										.renderMessage(fa_exc_c, alert_d, 'You must choose the sequence of 4 image tiles chosen during registration.', null, null, true)
										.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 3500));
								} else
									// Submit the selected segments and close the modal
									_fuseVerifyForm.step('submit');
							} else
								e.preventDefault(); // Prevent close action for the modal
						} else
							fuseIdArray = []; // Close the modal and clear any selected segments
					});
				});
				// Remove the forms' loading state
				_form.toggleSubmitButtonState(true);
				response.messageTag.html.insert(null);
			}
		});
	}
});
