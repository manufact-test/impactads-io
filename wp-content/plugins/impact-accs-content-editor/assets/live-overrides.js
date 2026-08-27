(function () {
	'use strict';

	/*
	 * Legacy live DOM overrides are intentionally retired.
	 * Public copy/link overrides are applied server-side by IACCE_Applier before
	 * the response is sent. Dynamic RU presentation copy is selected by each UI
	 * owner from the server-provided iacData payload before DOM insertion.
	 *
	 * Keep this inert file for compatibility with cached HTML that may still
	 * reference the previous asset URL. Do not reintroduce MutationObserver,
	 * TreeWalker, polling timers, or broad textContent replacement here.
	 */
	window.iacceApplyOverrides = function () {};
})();
