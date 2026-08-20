(function (window, document) {
	'use strict';

	function isRu() {
		if (window.iacData && window.iacData.lang) return window.iacData.lang === 'ru';
		return document.documentElement.classList.contains('iac-lang-ru') || document.documentElement.lang === 'ru';
	}
	if (!isRu()) return;

	function norm(value) {
		return String(value || '').replace(/\s+/g, ' ').trim();
	}

	function has(value, needles) {
		value = norm(value);
		for (var i = 0; i < needles.length; i += 1) {
			if (value.indexOf(needles[i]) !== -1) return true;
		}
		return false;
	}

	function branchKind(root) {
		if (!root) return '';
		var text = norm(root.textContent);
		if (has(text, ['Команде нужен аккаунт под залив', 'Launch blocked'])) return 'red';
		if (has(text, ['Медиабаингу нужны трастовые аккаунты', 'Supply stable'])) return 'green';
		if (has(text, ['Команде нужен стабильный объём аккаунтов', 'Volume request — EU'])) return 'yellow';
		return '';
	}

	function directParagraphs(box) {
		var out = [];
		if (!box) return out;
		for (var i = 0; i < box.children.length; i += 1) {
			if (box.children[i].tagName === 'P') out.push(box.children[i]);
		}
		return out;
	}

	function findThreeLineCard(root, labels) {
		if (!root) return null;
		var ps = root.querySelectorAll('p');
		for (var i = 0; i < ps.length; i += 1) {
			if (!has(ps[i].textContent, labels)) continue;
			var box = ps[i].parentElement;
			if (directParagraphs(box).length >= 3) return box;
		}
		return null;
	}

	function setThreeLineCard(root, labels, lines) {
		var box = findThreeLineCard(root, labels);
		if (!box) return;
		var ps = directParagraphs(box);
		ps[0].textContent = lines[0];
		ps[1].textContent = lines[1];
		ps[2].textContent = lines[2];
	}

	function firstSlackContent(root) {
		if (!root) return null;
		return root.querySelector('[data-slot="content"]');
	}

	function patchResolved(root) {
		if (!root || root.getAttribute('data-iah-ru-resolved') !== '1') return;
		var kind = root.getAttribute('data-iah-ru-kind') || branchKind(root);
		if (!kind) return;
		root.setAttribute('data-iah-ru-kind', kind);

		if (kind === 'red') {
			setThreeLineCard(root, ['ПАРАМЕТРЫ АККАУНТА', 'Request status', 'УСЛОВИЯ ПОКУПКИ'], [
				'УСЛОВИЯ ПОКУПКИ',
				'Цена: $400 · Оплата: USDT TRC20 · по желанию через гаранта',
				'До ваших изменений в аккаунте за него отвечает impact.'
			]);
			return;
		}

		if (kind === 'green') {
			var content = firstSlackContent(root);
			if (content) {
				content.textContent = 'Личный канал с владельцем открыт\nПодбор, замена и следующие закупки — напрямую, 24/7.';
			}
			setThreeLineCard(root, ['ПАРАМЕТРЫ ПОДБОРА', 'Supply status', 'ДЛЯ МЕДИАБАИНГА'], [
				'ДЛЯ МЕДИАБАИНГА',
				'Один поставщик для десятков аккаунтов под залив.',
				'Понятные параметры, открытые цены, простая гарантия.'
			]);
			return;
		}

		if (kind === 'yellow') {
			setThreeLineCard(root, ['ПРЕДЛОЖЕНИЕ ДЛЯ КОМАНДЫ', 'Volume terms', 'РЕГУЛЯРНАЯ ПОСТАВКА'], [
				'РЕГУЛЯРНАЯ ПОСТАВКА',
				'200 аккаунтов / месяц · USA · параметры согласованы',
				'По всем закупкам на связи лично владелец, 24/7.'
			]);
		}
	}

	function markResolved(button) {
		var label = norm(button && button.textContent).toUpperCase();
		var kind = '';
		if (label.indexOf('ПОДТВЕРДИТЬ') !== -1 || label === 'SEND') kind = branchKind(button.closest('[data-interactive]'));
		if (label.indexOf('ЗАРЕЗЕРВИРОВАТЬ') !== -1) kind = 'green';
		if (label.indexOf('ЗАФИКСИРОВАТЬ') !== -1) kind = 'yellow';
		if (!kind) return;

		var root = button.closest('[data-interactive]');
		if (!root) return;
		root.setAttribute('data-iah-ru-kind', kind);
		root.setAttribute('data-iah-ru-resolved', '1');
		patchResolved(root);
		window.setTimeout(function () { patchResolved(root); }, 50);
		window.setTimeout(function () { patchResolved(root); }, 250);
		window.setTimeout(function () { patchResolved(root); }, 700);
	}

	function patchAllResolved() {
		var roots = document.querySelectorAll('[data-iah-ru-resolved="1"]');
		for (var i = 0; i < roots.length; i += 1) patchResolved(roots[i]);
	}

	function onClick(event) {
		var button = event.target && event.target.closest ? event.target.closest('button') : null;
		if (!button) return;
		var label = norm(button.textContent).toUpperCase();
		if (
			label.indexOf('ПОДТВЕРДИТЬ') === -1 &&
			label.indexOf('ЗАРЕЗЕРВИРОВАТЬ') === -1 &&
			label.indexOf('ЗАФИКСИРОВАТЬ') === -1 &&
			label !== 'SEND'
		) return;
		markResolved(button);
	}

	var queued = false;
	function queue() {
		if (queued) return;
		queued = true;
		window.requestAnimationFrame(function () {
			queued = false;
			patchAllResolved();
		});
	}

	function boot() {
		document.addEventListener('click', onClick, true);
		var observer = new MutationObserver(queue);
		observer.observe(document.body, { childList: true, subtree: true, characterData: true });
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})(window, document);
