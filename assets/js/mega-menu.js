document.addEventListener("DOMContentLoaded", function () {
	// Top-level items that own a mega menu
	const topItems = document.querySelectorAll(
		"#menu-main-menu > li.menu-item-depth-0.menu-item-has-mega"
	);

	const pageHeader = document.querySelector(".site-header");

	if (!topItems.length) return;

	function closeAllMegaMenus() {
		topItems.forEach((i) => i.classList.remove("open"));
		if (pageHeader) {
			pageHeader.classList.remove("mega-menu-open");
		}
	}

	topItems.forEach((item) => {
		const link = item.querySelector(":scope > a");
		const submenu = item.querySelector(":scope > .mega-menu");

		if (!link || !submenu) return;

		let closeTimer = null;

		function openMenu() {
			topItems.forEach((i) => {
				if (i !== item) {
					i.classList.remove("open");
				}
			});

			item.classList.add("open");

			if (pageHeader) {
				pageHeader.classList.add("mega-menu-open");
			}
		}

		function startCloseTimer() {
			if (closeTimer) {
				clearTimeout(closeTimer);
			}
			closeTimer = setTimeout(function () {
				item.classList.remove("open");

				const anyOpen = document.querySelector(
					"#menu-main-menu > li.menu-item-depth-0.menu-item-has-mega.open"
				);
				if (!anyOpen && pageHeader) {
					pageHeader.classList.remove("mega-menu-open");
				}
			}, 200);
		}

		function cancelCloseTimer() {
			if (closeTimer) {
				clearTimeout(closeTimer);
				closeTimer = null;
			}
		}

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
	});

	document.addEventListener("keyup", function (e) {
		if (e.key === "Escape") {
			closeAllMegaMenus();
		}
	});

	document.addEventListener("click", function (e) {
		const inside = e.target.closest(
			"#menu-main-menu > li.menu-item-depth-0.menu-item-has-mega"
		);
		if (!inside) {
			closeAllMegaMenus();
		}
	});
});
