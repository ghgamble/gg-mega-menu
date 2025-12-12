document.addEventListener("DOMContentLoaded", function () {
	/**
	 * GG Mega Menu (theme-agnostic-ish)
	 * - Avoids hardcoding #menu-main-menu
	 * - Scopes to header nav structure
	 * - Keeps your existing hover + delay + ESC + click-outside behavior
	 */

	const header = document.querySelector("header.site-header");
	const nav = header ? header.querySelector(".header-nav") : null;

	// Only target mega-enabled top-level items inside the header nav
	const topItems = nav
		? nav.querySelectorAll(
				":scope > ul.menu > li.menu-item-depth-0.menu-item-has-mega"
		  )
		: [];

	if (!topItems.length) return;

	function closeAllMegaMenus() {
		topItems.forEach((i) => i.classList.remove("open"));
		if (header) header.classList.remove("mega-menu-open");
	}

	function anyMegaOpen() {
		return !!(nav && nav.querySelector(":scope > ul.menu > li.menu-item-depth-0.menu-item-has-mega.open"));
	}

	topItems.forEach((item) => {
		const link = item.querySelector(":scope > a");
		const submenu = item.querySelector(":scope > .mega-menu");

		if (!link || !submenu) return;

		let closeTimer = null;

		function openMenu() {
			// Close other mega menus
			topItems.forEach((i) => {
				if (i !== item) i.classList.remove("open");
			});

			// Open this one
			item.classList.add("open");

			// Header state
			if (header) header.classList.add("mega-menu-open");
		}

		function startCloseTimer() {
			if (closeTimer) clearTimeout(closeTimer);

			closeTimer = setTimeout(function () {
				item.classList.remove("open");

				// If none are open anymore, clear header state
				if (!anyMegaOpen() && header) {
					header.classList.remove("mega-menu-open");
				}
			}, 200);
		}

		function cancelCloseTimer() {
			if (closeTimer) {
				clearTimeout(closeTimer);
				closeTimer = null;
			}
		}

		// Hover open/close (top-level link)
		link.addEventListener("mouseenter", function () {
			cancelCloseTimer();
			openMenu();
		});

		link.addEventListener("mouseleave", function () {
			startCloseTimer();
		});

		// Hover open/close (panel)
		submenu.addEventListener("mouseenter", function () {
			cancelCloseTimer();
			openMenu();
		});

		submenu.addEventListener("mouseleave", function () {
			startCloseTimer();
		});

		// Optional: focus accessibility (keeps it open when tabbing into it)
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

	// Close with Escape key
	document.addEventListener("keyup", function (e) {
		if (e.key === "Escape") {
			closeAllMegaMenus();
		}
	});

	// Close when clicking outside the mega menu parents
	document.addEventListener("click", function (e) {
		const inside = e.target.closest(
			"header.site-header .header-nav > ul.menu > li.menu-item-depth-0.menu-item-has-mega"
		);

		if (!inside) {
			closeAllMegaMenus();
		}
	});
});
