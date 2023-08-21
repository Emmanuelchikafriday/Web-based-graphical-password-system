document.addEventListener('DOMContentLoaded', function () {
    let initialXHR,
        initForm = '#init-forgot-password-form',
        sectionHeader = '.title',
        sectionPart = '.body',
        currentSectionHeader = '.title.current',
        currentSectionPart = '.body.current',
        canChangeStep = false,
        currentContext,
        _initForgotPasswordForm,
        _picfuseAuth,
        _picfuseAuthValid,
        _initPicfuseAuth,
        _messageWrapper,
        _clearPicfuseDropify,
        _globalMessageWrapper = $fs(globalMessageTag).validator;
    
    $('#recovery-wizard').steps({
        headerTag: 'h5',
        bodyTag: 'section',
        contentMode: 'async',
        autoFocus: true,
        next: 'Continue',
        enableFinishButton: false,
        titleTemplate: 'file:///home/fusion/Downloads/nexus.sql<span class="title">#title#<\/span>',
        onInit: function (e, currentIndex) {
            currentContext = initForm;
            _initForgotPasswordForm = $fs(initForm);
            _picfuseAuth = $fs('#picfuse_auth');
            _picfuseAuthValid = $fs('#picfuse_authValid');
            _initPicfuseAuth = $fs('#start-picfuse-auth');
            _messageWrapper = $fs(globalMessageTag).validator;
            _clearPicfuseDropify = $fs('.dropify-clear', currentContext);
            
            $fs('.steps', e.currentTarget).touchStyle({display: 'none'});
            $fs(sectionHeader, e.currentTarget).touchStyle({display: 'none'});
            $fs(currentSectionHeader, e.currentTarget).touchStyle({display: 'block'});
            $fs('a[href="#next"]').attribute({form: _initForgotPasswordForm.attribute('id')});
            
            _initForgotPasswordForm.validator.initFormValidation({config: {validateEmail: true,}}).upon('submit', function (e) {
                e.preventDefault();
                _initForgotPasswordForm.handleFormSubmit({uri: _initForgotPasswordForm.action, method: _initForgotPasswordForm.formMethod, data: {picfuseArray: fuseIdArray}}).then(response => {
                    if (response.JSON.status === 200) {
                        initialXHR = response;
                        setTimeout(() => {
                            canChangeStep = true;
                            $('#recovery-wizard').steps('next');
                        }, 2500);
                    }
                }).catch(e => console.log(e));
            });
            $fs('a[href="#next"]').upon('click', () => _initForgotPasswordForm.step('submit'));
        },
        onStepChanging: function (e, currentIndex) {
            if (currentIndex === 0) {
                if (canChangeStep) {
                    canChangeStep = false;
                    return true;
                }
            } else {
                return true;
            }
        },
        onStepChanged: function (e, currentIndex, newIndex) {
            $fs(sectionHeader, e.currentTarget).touchStyle({display: 'none'});
            $fs(currentSectionHeader, e.currentTarget).touchStyle({display: 'block'});
            
            if (currentIndex === 1) {
                _initForgotPasswordForm.step('reset');
                _initForgotPasswordForm.validator.messageTag.html.insert(null);
                
                $fs('#resend-otp').upon('click', function (e) {
                    e.preventDefault();
                    const formData = new FormData(), data = {email: initialXHR.JSON.email, action: 'initiate-recovery'};
                    Object.keys(data).forEach(key => formData.append(key, data[key]));
                    
                    fetchReq({
                        uri: _initForgotPasswordForm.action,
                        method: _initForgotPasswordForm.formMethod,
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
                                loadOTP();
                            });
                        },
                        onComplete: () => $fs('#resend-otp').disable(true)
                    });
                });
                
                $('#otp').pinlogin({
                    fields: 4,
                    reset: false,
                    hideinput: false,
                    complete: function (otp) {
                        const formData = new FormData(), data = {otp: otp, email: initialXHR.JSON.email, action: 'verify-otp'};
                        Object.keys(data).forEach(key => formData.append(key, data[key]));
                        
                        fetchReq({
                            uri: _initForgotPasswordForm.action,
                            method: _initForgotPasswordForm.formMethod,
                            data: formData,
                            beforeSend: () => {
                                $fs('.pinlogin-field').disable()
                                $fs('#resend-otp').disable()
                            },
                            onSuccess: (e) => {
                                _globalMessageWrapper.renderMessage(fa_check_d, alert_s, `${e.responseJSON.message ?? 'Verification Successful. Please Wait'}`).slideInDown(800);
                                setTimeout(() => $fs(globalMessageTag).slideOutUp(800).then(wrapper => {
                                    location.href = e.responseJSON.redirect;
                                    wrapper.html.insert(null)
                                }), 4500);
                                
                            },
                            onComplete: (e, status) => {
                                if (status === 422)
                                    _globalMessageWrapper.renderMessage(fa_exc_c, alert_i, `An error occurred: ${e.responseJSON.message ?? 'OTP incorrect or Server error. Please try again'}`)
                                    .slideInDown(800)
                                    .then(wrapper => setTimeout(() => wrapper.slideOutUp(800).then(wrapper => wrapper.html.insert(null)), 5000));
                                
                                $fs('.pinlogin-field').disable(true);
                                $fs('#resend-otp').disable(true);
                            }
                        });
                    }
                });
                loadOTP();
            }
        }
    });
    
    _clearPicfuseDropify.upon('click', () => {
        fuseIdArray = [];
        currentImage = _picfuseAuth[0].files;
    });
    
});
