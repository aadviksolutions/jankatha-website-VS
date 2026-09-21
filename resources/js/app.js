const drawer = document.querySelector('[data-mobile-drawer]');
const toggle = document.querySelector('[data-menu-toggle]');
const closeButtons = document.querySelectorAll('[data-menu-close]');

const closeDrawer = () => {
	if (!drawer || !toggle) return;
	drawer.classList.remove('is-open');
	drawer.setAttribute('aria-hidden', 'true');
	toggle.setAttribute('aria-expanded', 'false');
	document.body.classList.remove('drawer-open');
};

if (drawer && toggle) {
	toggle.addEventListener('click', () => {
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		toggle.setAttribute('aria-expanded', 'true');
		document.body.classList.add('drawer-open');
	});
	closeButtons.forEach((button) => button.addEventListener('click', closeDrawer));
	drawer.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeDrawer));
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') closeDrawer();
	});
}
