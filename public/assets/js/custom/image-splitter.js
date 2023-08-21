const image = new Image(), imageMargin = 2;
// const acceptedImageTypes = ['image/webp', 'image/jpeg', 'image/png', 'image/bmp'];

let fuseIdArray = [],
	numColsToCut = 3,
	numRowsToCut = 3;

async function cutImageUp (currentImage, wrapper) {
	const images = [], imagePieces = [], _picFuseWrapper = $fs(`${wrapper}`);
	let picfuseIdArray = [],
		imgWidth = currentImage.naturalWidth,
		imgHeight = currentImage.naturalHeight,
		widthOfOnePiece = imgWidth / numColsToCut,
		heightOfOnePiece = imgHeight / numRowsToCut;

	for (let x = 0;x < numColsToCut;++x) {
		for (let y = 0;y < numRowsToCut;++y) {
			const canvas = document.createElement('canvas');
			canvas.width = widthOfOnePiece;
			canvas.height = heightOfOnePiece;
			const context = canvas.getContext('2d');
			context.drawImage(currentImage, x * widthOfOnePiece, y * heightOfOnePiece, widthOfOnePiece, heightOfOnePiece, 0, 0, canvas.width, canvas.height);
			imagePieces.push(canvas.toDataURL());
		}
	} // imagePieces[] now contains data urls of all the pieces of the image

	imagePieces.forEach((piece, idx) => {
		const imgWrapper = document.createElement('div');
		const imgIndicator = document.createElement('span');
		const imgElement = document.createElement('img');

		imgIndicator.dataset.id = (idx + 1).toString();
		imgWrapper.classList.add('picfuse-img');
		imgWrapper.append(imgElement, imgIndicator)

		$fs(imgElement)
			.touchStyle({ objectFit: 'cover' })
			.touchDataAttribute('id', (idx + 1).toString())
			.touchAttribute({ src: piece, alt: `image-${(idx + 1).toString()}` });

		$fs(imgWrapper).touchStyle({
			margin: `${imageMargin * 2}px`,
			width: `calc(250px / ${numRowsToCut})`,
			height: `calc(250px / ${numColsToCut})`
		});
		images.push(imgWrapper);
	});
	// using Array map and Math.random
	const shuffledImages = shuffled(images);

	_picFuseWrapper.touchStyle({
		width: `calc(250px + ${(numRowsToCut + imageMargin) * (imageMargin * 3)}px + ${imageMargin * 2}px)`,
		maxHeight: `calc(250px + ${(numColsToCut + imageMargin) * (imageMargin * 3)}px)`,
	});

	_picFuseWrapper.children().target.length && _picFuseWrapper.children().target.forEach(child => _picFuseWrapper[0].removeChild(child));
	shuffledImages.forEach(img => _picFuseWrapper[0].append(img));

	await $fs(`${wrapper} img, ${wrapper} span`).upon('click', function () {
		let _target = $fs(this),
			imgId = _target.dataAttribute('id');

		if (_target[0].tagName.toLowerCase() === 'span')
			_target = $fs(`${wrapper} img[data-id="${imgId}"]`);

		const elementClassList = _target.classlist;

		if (picfuseIdArray.length < 4) {
			!picfuseIdArray.contains(imgId) && picfuseIdArray.push(imgId);
			!elementClassList.includes('clicked') ? elementClassList.put('clicked') : elementClassList.remove('clicked');
			picfuseIdArray = (!elementClassList.includes('clicked') && picfuseIdArray.contains(imgId)) ? picfuseIdArray.filter(val => val !== imgId) : picfuseIdArray;
		} else {
			elementClassList.remove('clicked');
			picfuseIdArray = picfuseIdArray.filter(val => val !== imgId);
		}

		picfuseIdArray.length ? picfuseIdArray.forEach((id, idx) => {
			$fs(`${wrapper} span[data-id="${id}"]`).html.insert((idx + 1));
			$fs(`${wrapper} img:not(.clicked) ~ span`).html.insert(null);
		}) : $fs(`${wrapper} span`).html.insert(null);

		fuseIdArray = picfuseIdArray;
	});
}
