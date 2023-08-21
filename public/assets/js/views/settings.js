document.addEventListener('DOMContentLoaded', function () {
    const otpWrapper = '#otp-wrapper';
    let initialXHR,
        form = '#update-account-form',
        _form = $fs(form),
        _deleteUserForm = $fs('#delete-user-account-form'),
        _initEmailOtp = $fs('#init-email-otp'),
        _globalMessageWrapper = $fs(globalMessageTag).validator;
    
    _form.validator.initFormValidation({
        config: {
            validateName: true,
            validateEmail: true,
            validatePhone: true,
        }
    }).upon('submit', function (e) {
        e.preventDefault();
        _form.handleFormSubmit({uri: _form.action, method: _form.formMethod, data: {picfuseArray: fuseIdArray}}).then(response => {
            console.log(response.JSON);
            if (response.JSON.status === 200)
                setTimeout(() => location.reload(), 1500);
            else {
                if (response.JSON.status !== 422)
                    setTimeout(() => {
                        initialXHR = response;
                        _form.validator.messageTag.html.insert(null);
                        _initEmailOtp.step('click')
                    }, 1500);
            }
        }).catch(e => console.log(e));
    });
    
    if (_deleteUserForm.length)
        _deleteUserForm.validator.initFormValidation({config: {useDefaultStyling: false}}).upon('submit', function (e) {
            e.preventDefault();
            _deleteUserForm.handleFormSubmit({uri: _deleteUserForm.action, method: _deleteUserForm.formMethod, data: {picfuseArray: fuseIdArray}}).then(response => {
                if (response.JSON.status !== 422)
                    setTimeout(() => {
                        initialXHR = response;
                        _deleteUserForm.validator.messageTag.html.insert(null);
                        _initEmailOtp.step('click')
                    }, 1500);
            }).catch(e => console.log(e));
        })
    
    _initEmailOtp.modal.onClickOpen({
        onComplete: (e) => {
            const _modal = e.response, _otpWrapper = $fs(otpWrapper, _modal);
            _otpWrapper.loadPageData({uri: _otpWrapper.dataAttribute('load-page')}).then(response => {
                _initPicfuseAuth = $fs('#start-picfuse-auth');
                _fuseVerifyForm = $fs('#verify-picfuse-form');
                
                $('#otp').pinlogin({
                    fields: 4,
                    reset: false,
                    hideinput: false,
                    complete: function (otp) {
                        const formData = new FormData(), data = {otp: otp, email: initialXHR.JSON.email, action: 'verify-otp'};
                        Object.keys(data).forEach(key => formData.append(key, data[key]));
                        
                        fetchReq({
                            uri: _form.action,
                            method: _form.formMethod,
                            data: formData,
                            beforeSend: () => {
                                $fs('.pinlogin-field').disable()
                                $fs('#resend-otp').disable()
                            },
                            onSuccess: (e) => {
                                console.log(e);
                                _globalMessageWrapper.renderMessage(fa_check_d, alert_s, `${e.responseJSON.message ?? 'Verification Successful. Please Wait..'}`).slideInDown(800);
                                
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
                                    
                                    image.src = e.responseJSON.fuse;
                                    image.style.objectFit = 'cover';
                                    setTimeout(() => _initPicfuseAuth.step('click'), 2500);
                                }
                            },
                            onComplete: (e, status) => {
                                console.log(e, status)
                                if (status === 422) {
                                    _globalMessageWrapper.renderMessage(fa_exc_c, alert_i, `An error occurred: ${e.responseJSON.message ?? 'OTP incorrect or Server error. Please try again'}`)
                                    .slideInDown(800)
                                    .then(wrapper => setTimeout(() => wrapper.slideOutUp(800).then(wrapper => wrapper.html.insert(null)), 6000));
                                }
                                
                                $fs('.pinlogin-field').disable(true);
                                $fs('#resend-otp').disable(true);
                            }
                        });
                    }
                });
                
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
                                        const formData = new FormData(), data = {picfuseArray: fuseIdArray, email: initialXHR.JSON.email, action: initialXHR.JSON.action};
                                        Object.keys(data).forEach(key => formData.append(key, data[key]));
                                        
                                        fetchReq({
                                            uri: _form.action,
                                            method: _form.formMethod,
                                            data: formData,
                                            onComplete: (resp, status) => {
                                                if (status === 308)
                                                    _globalMessageWrapper.renderMessage(fa_exc_c, alert_i, resp.responseJSON.message, null, null, true)
                                                    .slideInDown(800)
                                                    .then(_wrapper => setTimeout(() => location.href = resp.responseJSON.redirect, 6000));
                                                else
                                                    _globalMessageWrapper.renderMessage(fa_exc_c, alert_d, resp.responseJSON.message, null, null, true)
                                                    .slideInDown(800).then(_wrapper => {
                                                        loadOTP(true);
                                                        setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500)
                                                    });
                                            }
                                        });
                                    }, 1500));
                                else {
                                    loadOTP(true);
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
                    const formData = new FormData(), data = {email: initialXHR.JSON.email, action: 'resend-otp-email', email_title: initialXHR.JSON.email_title, email_body: initialXHR.JSON.email_body};
                    Object.keys(data).forEach(key => formData.append(key, data[key]));
                    
                    fetchReq({
                        uri: _form.action,
                        method: _form.formMethod,
                        data: formData,
                        beforeSend: () => {
                            $fs('.pinlogin-field').disable()
                            $fs('#resend-otp').disable()
                        },
                        onError: (e) => _globalMessageWrapper.renderMessage(fa_exc_c, alert_i, `An error occurred: ${e.message ?? 'Failed to resend. Please try again'}`)
                        .slideInDown()
                        .then(wrapper => setTimeout(() => wrapper.slideOutUp(), 2500)),
                        onSuccess: (e) => {
                            initialXHR = e;
                            _globalMessageWrapper.renderMessage(fa_check_d, alert_s, `${e.responseJSON.message ?? 'OTP resent successfully'}`)
                            .slideOutUp(0).then(wrapper => {
                                wrapper.slideInDown(800).then(wrapper => setTimeout(() => wrapper.slideOutUp(), 4500));
                                loadOTP()
                            });
                        },
                        onComplete: () => $fs('#resend-otp').disable(true)
                    });
                });
                loadOTP();
            }).catch(error => console.log(error));
        }
    });
});
