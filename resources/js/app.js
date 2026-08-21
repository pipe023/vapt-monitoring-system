

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const showPageLoader = () => {
	document.getElementById('page-loader')?.classList.remove('hidden');
};

document.addEventListener('click', (event) => {
	const link = event.target.closest('a');

	if (!link || link.target === '_blank' || link.hasAttribute('download') || link.origin !== window.location.origin) {
		return;
	}

	const url = new URL(link.href);

	if (url.pathname !== window.location.pathname || url.search !== window.location.search) {
		showPageLoader();
	}
});

document.addEventListener('submit', (event) => {
	if (!event.defaultPrevented) {
		showPageLoader();
	}
});

window.addEventListener('pageshow', () => {
	document.getElementById('page-loader')?.classList.add('hidden');
});
