(function () {
	'use strict';

	function onSubmit(btn, busyText) {
		if (!btn) {
			return;
		}
		var form = btn.closest('form');
		if (!form) {
			return;
		}
		form.addEventListener('submit', function () {
			btn.disabled = true;
			btn.textContent = busyText;
		});
	}

	onSubmit(document.getElementById('iasm-export-btn'), 'Создаём пакет… не закрывайте вкладку');
	onSubmit(document.getElementById('iasm-upload-btn'), 'Загружаем и распаковываем…');
})();
