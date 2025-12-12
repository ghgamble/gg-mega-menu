document.addEventListener("DOMContentLoaded", function () {
	/**
	 * GG Mega Menu (plugin)
	 * - Works with theme nav markup: header.site-header .header-nav > ul.menu
	 * - Keeps the same hover + delay + ESC + click-outside behavior as your client theme
	 * - Fixes "page content showing under dropdown" by toggling body class
	 */

	const header = document.querySelector("header.site-header");
	const nav = header ? header.querySelector(".header-nav") : null;

	// Target mega-enabled top-level items inside the header nav
	const topItems = nav
		? nav.querySelectorAll(
				":scope > ul.menu > li.menu-item-depth-0.menu-item-has-mega"
		  )
		: [];

	if (!topItems.length) return;

	function setOpenState(isOpen) {
		document.body.classList.toggle("ggmm-mega-open", !!isOpen);
		if (header) header.classList.toggle("mega-menu-open", !!isOpen);
	}

	function anyMegaOpen() {
		return !!(
			nav &&
			nav.querySelector(
				":scope > ul.menu > li.menu-item-depth-0.menu-item-has-mega.open"
			)
		);
	}

	function closeAllMegaMenus() {
		topItems.forEach((i) => i.classList.remove("open"));
		setOpenState(false);
	}

	topItems.forEach((item) => {
		const link = item.querySelector(":scope > a");
		const submenu = item.querySelector(":scope > .mega-menu");

		if (!link || !submenu) return;

		let closeTimer = null;

		function openMenu() {
			// close any other open mega menus
			topItems.forEach((i) => {
				if (i !== item) i.classList.remove("open");
			});

			// open this one
			item.classList.add("open");
			setOpenState(true);
		}

		function startCloseTimer() {
			if (closeTimer) clearTimeout(closeTimer);

			closeTimer = setTimeout(function () {
				item.classList.remove("open");

				// if none open anymore, clear body/header state
				if (!anyMegaOpen()) {
					setOpenState(false);
				}
			}, 200);
		}

		function cancelCloseTimer() {
			if (closeTimer) {
				clearTimeout(closeTimer);
				closeTimer = null;
			}
		}

		// Hover open/close (same as your working client theme)
		link.addEventListener("mouseenter", function () {
			cancelCloseTimer();
			openMenu();
		});

		link.addEventListener("mouseleave", function () {
			startCloseTimer();
		});

		submenu.addEventListener("mouseenter", function () {
			cancelCloseTimer();
			openMenu();
		});

		submenu.addEventListener("mouseleave", function () {
			startCloseTimer();
		});

		// keyboard friendliness (does not interfere with opening in new tab)
		link.addEventListener("focus", function () {
			cancelCloseTimer();
			openMenu();
		});
		submenu.addEventListener("focusin", function () {
			cancelCloseTimer();
			openMenu();
		});
		submenu.addEventListener("focusout", function () {
			startCloseTimer();
		});
	});

	// Close all with Escape
	document.addEventListener("keyup", function (e) {
		if (e.key === "Escape") {
			closeAllMegaMenus();
		}
	});

	// Close when clicking outside the mega parents
	document.addEventListener("click", function (e) {
		const inside = e.target.closest(
			"header.site-header .header-nav > ul.menu > li.menu-item-depth-0.menu-item-has-mega"
		);

		if (!inside) {
			closeAllMegaMenus();
		}
	});
});
