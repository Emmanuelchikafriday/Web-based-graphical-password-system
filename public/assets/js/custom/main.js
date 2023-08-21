let currentImage,
	innerWidth = 0,
	_picfuseAuth = $fs('#picfuse_auth'),
	_picfuseAuthValid = $fs('#picfuse_authValid'),
	_initPicfuseAuth = $fs('#start-picfuse-auth'),
	_messageWrapper = $fs(globalMessageTag).validator;

const acceptedImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

const shuffled = array => array.map(a => ({ sort: Math.random(), value: a })).sort((a, b) => a.sort - b.sort).map(a => a.value);
const alignBodyToNav = () => $fs('nav').length && $fs('main.body').touchStyle({ marginTop: `${$fs('nav')[0].getBoundingClientRect().height}px` });
const alignSidebar = () => $fs('.sidebar').length && $fs('.sidebar').touchStyle({ height: `calc(100% - ${$fs('nav')[0].getBoundingClientRect().height}px)` });

// Function for the OTP form
function loadOTP (resend = false) {
	const _timeWrapperId = '#time';

	const toggleResend = () =>
		$fs(`${_timeWrapperId} > span:first-child`).fadeout().then(time => {
			time.touchStyle({ display: 'none' });
			$fs(`${_timeWrapperId} > span:last-child`).fadein().then(retry => retry.touchStyle({ display: 'block' }))
		});

	if (resend)
		toggleResend()
	else {
		const timer = new CountDownTimer((5 * 60)), timerObj = timer.parse((5 * 60)); // Set countdown timer to 5 minutes for OTP expiry
		timer.start();// Start the timer
		timer.onTick(formatTimer); // Call the function to display the timer to the user

		// Function to format the time and display timer to the user
		function formatTimer (minutes, seconds) {
			minutes = minutes < 10 ? `0${minutes}` : minutes;
			seconds = seconds < 10 ? `0${seconds}` : seconds;

			// If the time has expired
			if (timer.expired())
				// Display the resend otp button
				toggleResend();
			else
				// Else continue updating the timer
				$fs(`${_timeWrapperId} > span:first-child span`).html.insert(`${minutes}:${seconds}`);
		}

		// Display the timer by default each time the OTP form loads
		$fs(`${_timeWrapperId} > span:first-child`).fadein().then(time => {
			time.touchStyle({ display: 'block' });
			$fs(`${_timeWrapperId} > span:last-child`).fadeout().then(retry => retry.touchStyle({ display: 'none' }));
		});

		formatTimer(timerObj.minutes, timerObj.seconds);
	}
	// Enable the OTP input fields
	$fs('.pinlogin-field').disable(true);
}

function initNewImageCutupSequence () {
	const _clearPicfuseDropify = $fs('.dropify-clear');

	_initPicfuseAuth.modal.onClickOpen({
		onComplete: (e) => {
			if (e.status === 'success')
				if (currentImage[0].type && acceptedImageTypes.includes(currentImage[0].type.toLowerCase())) {
					const _modal = e.response, reader = new FileReader();
					reader.readAsDataURL(currentImage[0]);
					reader.onload = function (e) {
						image.src = (reader.result).toString();
						image.style.objectFit = 'cover';

						cutImageUp(image, '#picfuse-wrapper').then(() => {
							_modal.onBSModalClose(e => {
								if (!$fs('.close-modal', e.currentTarget).mouseIsOver()) {
									if ($fs('button', e.currentTarget).mouseIsOver()) {
										if (fuseIdArray.length < 4) {
											e.preventDefault();
											_messageWrapper
												.renderMessage(fa_exc_c, alert_d, 'You must choose a sequence of 4 image tiles to continue.', null, null, true)
												.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 4500));
										} else
											_picfuseAuthValid.html.insert(null);
									} else
										e.preventDefault();
								} else
									_clearPicfuseDropify.step('click');
							});
						});
					}
				} else {
					e.target.preventDefault();
					_messageWrapper
						.renderMessage(fa_exc_c, alert_d, 'Please upload a valid image type (png or jpg).', null, null, true)
						.slideInDown(800).then(_wrapper => setTimeout(() => _wrapper.slideOutUp(800).then(_wrapper => _wrapper.html.insert(null)), 3500));
					_clearPicfuseDropify.step('click');
				}
			else
				console.error(e.response);
		}
	});
	_picfuseAuth.upon('change', function (e) {
		currentImage = this.files;
		_initPicfuseAuth.step('click');
	});

	_clearPicfuseDropify.upon('click', () => {
		fuseIdArray = [];
		currentImage = _picfuseAuth[0].files;
	});
}

document.addEventListener('DOMContentLoaded', function () {
	innerWidth = window.innerWidth
	alignBodyToNav();
	_sidebar.length && initSideBar();
	// alignSidebar();
});

window.onresize = function () {
	innerWidth = window.innerWidth;
	alignBodyToNav();
	_sidebar.length && adjustSideBar();
	// alignSidebar();
}
