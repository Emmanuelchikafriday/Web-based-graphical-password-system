let sidebarWidth = 0,
	sidebarIsMobileExpanded = false,
	_sidebar = $fs('.sidebar'),
	_overlay = $fs('.overlay');

const adjustSideBar = () => {
	sidebarWidth = _sidebar[0].getBoundingClientRect().width;
	if (innerWidth > 991.99) {
		_sidebar.touchStyle({left: 0}).classlist.put('expanded');
		_overlay.fadeout().then(overlay => overlay.touchStyle({display: 'none'}));
	} else {
		if (sidebarIsMobileExpanded) {
			_sidebar.touchStyle({left: 0}).classlist.put('expanded');
			_overlay.fadein().then(overlay => overlay.touchStyle({display: 'block'}));
		} else
			_sidebar.touchStyle({left: `-${sidebarWidth}px`}).classlist.remove('expanded');
	}
}

const initSideBar = () => {
	const _sidebarToggler = $fs('.sidebar-toggler');
	sidebarWidth = _sidebar[0].getBoundingClientRect().width;
	
	_sidebarToggler.upon('click', function (e) {
		e.preventDefault();
		const keyframes = [{left: `-${sidebarWidth}px`}, {left: `-${(sidebarWidth / 2)}px`}, {left: 0}], keyframesRule = {iterations: 1};
		_sidebar[0].animate(keyframes, keyframesRule).finished.then(() => {
			_sidebar.touchStyle({left: 0}).classlist.put('expanded');
			_overlay.fadein().then(overlay => overlay.touchStyle({display: 'block'}));
			sidebarIsMobileExpanded = true;
		});
	});
	
	$fs('html').upon('click', function (e) {
		if (!_sidebar.mouseIsOver() && _sidebar.classlist.includes('expanded') && innerWidth < 992) {
			const sidebarWidth = _sidebar[0].getBoundingClientRect().width;
			const keyframes = [{left: 0}, {left: `-${(sidebarWidth / 2)}px`}, {left: `-${sidebarWidth}px`}], keyframesRule = {iterations: 1};
			_sidebar[0].animate(keyframes, keyframesRule).finished.then(() => {
				_sidebar.touchStyle({left: `-${sidebarWidth}px`}).classlist.remove('expanded');
				_overlay.fadeout().then(overlay => overlay.touchStyle({display: 'none'}));
				sidebarIsMobileExpanded = false;
			});
		}
	});
	adjustSideBar();
}
