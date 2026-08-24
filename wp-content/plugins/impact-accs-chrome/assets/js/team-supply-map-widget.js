(function () {
	var root = document.getElementById('gt-map-widget-v1');
	if (!root || root.dataset.gtReady === '1') {
		return;
	}
	root.dataset.gtReady = '1';

	var canvasContainer = root.querySelector('.gt-canvas-container');
	var labelsContainer = root.querySelector('.gt-labels-container');
	var toggleBtn = root.querySelector('.gt-icon-btn');
	var iconEl = root.querySelector('.gt-btn-icon');
	var loadingEl = root.querySelector('.gt-loading');
	var isRu =
		document.documentElement.lang === 'ru' ||
		document.documentElement.classList.contains('iac-lang-ru') ||
		window.location.pathname.indexOf('/accounts/team-supply/') !== -1;

	function loadScript(src, testFn) {
		return new Promise(function (resolve, reject) {
			if (testFn && testFn()) {
				resolve();
				return;
			}

			var existing = Array.from(document.scripts).find(function (s) {
				return s.src === src;
			});

			if (existing) {
				existing.addEventListener('load', function () {
					resolve();
				});

				existing.addEventListener('error', function () {
					reject(new Error('Failed to load ' + src));
				});

				if (testFn && testFn()) {
					resolve();
				}
				return;
			}

			var script = document.createElement('script');
			script.src = src;
			script.async = false;
			script.onload = function () {
				resolve();
			};
			script.onerror = function () {
				reject(new Error('Failed to load ' + src));
			};
			document.head.appendChild(script);
		});
	}

	function getSize() {
		var width = Math.max(root.clientWidth || 320, 320);
		var height = Math.max(root.clientHeight || 320, 320);
		return { width: width, height: height };
	}

	function fallbackLandMask(lon, lat) {
		var inBox = function (lonMin, lonMax, latMin, latMax) {
			return lon >= lonMin && lon <= lonMax && lat >= latMin && lat <= latMax;
		};

		return (
			inBox(-168, -52, 8, 72) ||
			inBox(-82, -35, -56, 13) ||
			inBox(-18, 52, -35, 37) ||
			inBox(-12, 160, 35, 72) ||
			inBox(32, 122, 5, 34) ||
			inBox(112, 154, -44, -10) ||
			inBox(128, 146, 30, 46) ||
			inBox(95, 142, -10, 22) ||
			inBox(-10, 45, 36, 60)
		);
	}

	async function start() {
		try {
			await loadScript(
				'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
				function () {
					return !!window.THREE;
				}
			);

			await loadScript(
				'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js',
				function () {
					return !!(window.THREE && window.THREE.OrbitControls);
				}
			);

			await loadScript(
				'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js',
				function () {
					return !!window.gsap;
				}
			);

			await loadScript(
				'https://unpkg.com/lucide@latest',
				function () {
					return !!window.lucide;
				}
			);
		} catch (error) {
			if (loadingEl) {
				loadingEl.textContent = isRu ? 'Ошибка загрузки карты' : 'Library loading error';
			}
			console.error(error);
			return;
		}

		if (window.lucide) {
			window.lucide.createIcons();
		}

		var THREE = window.THREE;
		var gsap = window.gsap;

		var scene = new THREE.Scene();
		var initialSize = getSize();

		var camera = new THREE.PerspectiveCamera(
			75,
			initialSize.width / initialSize.height,
			0.1,
			1000
		);

		var renderer = new THREE.WebGLRenderer({
			antialias: true,
			alpha: true
		});

		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
		renderer.setSize(initialSize.width, initialSize.height);
		renderer.setClearColor(0x000000, 0);

		canvasContainer.innerHTML = '';
		canvasContainer.appendChild(renderer.domElement);

		var controls = new THREE.OrbitControls(camera, renderer.domElement);
		controls.enableDamping = true;
		controls.dampingFactor = 0.05;

		controls.enableZoom = false;
		controls.enablePan = true;
		controls.enableRotate = true;

		controls.minDistance = 18;
		controls.maxDistance = 18;

		camera.position.set(0, 0, 18);

		var particles = null;
		var isFlat = false;
		var animationFrameId = null;

		var dotCoords = [];
		var flatCoords = [];
		var markers = [];

		var markerData = isRu ? [
			{
				name: 'ПРОВЕРКА ДО ОПЛАТЫ',
				lat: 50.1,
				lon: 10.4,
				status: 'Сначала смотрите аккаунты',
				detail: 'Проверяете спенд, гео, валюту и доступ до оплаты.'
			},
			{
				name: 'ОБЪЁМ ПОД КОМАНДУ',
				lat: 37.0,
				lon: -95.7,
				status: 'Сотни аккаунтов под ваш темп',
				detail: 'Подбираем США, нужный спенд, валюту и вертикаль под запрос.'
			},
			{
				name: 'ЧАСТИЧНАЯ ПОСТАВКА',
				lat: 35.6,
				lon: 139.6,
				status: 'Если всего объёма нет сразу',
				detail: 'Фиксируем доступную часть сейчас, остальное быстро добираем.'
			},
			{
				name: 'ПРЯМОЙ КОНТАКТ',
				lat: 51.5,
				lon: -0.1,
				status: 'Один владелец ведёт поставку',
				detail: 'Подбор, передача, замены и повторные заказы — в одном диалоге.'
			},
			{
				name: 'БЫСТРАЯ ЗАМЕНА',
				lat: 25.2,
				lon: 55.3,
				status: 'Если позиция не подошла',
				detail: 'Меняем конкретный аккаунт без споров, если он не тронут.'
			},
			{
				name: 'ПОВТОРНЫЙ ЗАКАЗ',
				lat: 1.3,
				lon: 103.8,
				status: 'Следующая закупка быстрее',
				detail: 'Сохраняем параметры команды и заново подтверждаем актуальный список.'
			}
		] : [
			{
				name: 'EU Agency Batch',
				lat: 50.1,
				lon: 10.4,
				status: '50 accounts ready',
				detail: 'EU geo match verified · handoff today'
			},
			{
				name: 'GoogleAds Access',
				lat: 37.0,
				lon: -95.7,
				status: 'Volume tier pending sign-off',
				detail: 'Terms locked · buyer confirmation required'
			},
			{
				name: 'TikTok Access',
				lat: 35.6,
				lon: 139.6,
				status: 'Replacement policy confirmed',
				detail: 'Platform access queued for next batch'
			},
			{
				name: 'Terms Desk',
				lat: 51.5,
				lon: -0.1,
				status: 'Terms locked',
				detail: 'Repeat order channel ready'
			},
			{
				name: 'Direct Handoff',
				lat: 25.2,
				lon: 55.3,
				status: 'Direct channel today',
				detail: 'One desk contact for delivery and replacements'
			},
			{
				name: 'Supply Match',
				lat: 1.3,
				lon: 103.8,
				status: 'Agency sourcing active',
				detail: 'Geo, platform, and volume matched'
			}
		];

		function buildDotsFromImageData(data, canvasWidth, canvasHeight) {
			var positions = [];

			for (var y = 0; y < canvasHeight; y += 3) {
				for (var x = 0; x < canvasWidth; x += 3) {
					var i = (y * canvasWidth + x) * 4;

					if (data[i] > 65) {
						var lat = (y / canvasHeight) * Math.PI - Math.PI / 2;
						var lon = (x / canvasWidth) * 2 * Math.PI - Math.PI;
						var r = 10.5;

						var px = -r * Math.cos(lat) * Math.cos(lon);
						var py = -r * Math.sin(lat);
						var pz = r * Math.cos(lat) * Math.sin(lon);

						dotCoords.push(px, py, pz);
						flatCoords.push((x - canvasWidth / 2) / 30, (canvasHeight / 2 - y) / 30, 0);
						positions.push(px, py, pz);
					}
				}
			}

			return positions;
		}

		function buildFallbackDots() {
			var positions = [];
			var width = 1024;
			var height = 512;

			for (var y = 0; y < height; y += 4) {
				for (var x = 0; x < width; x += 4) {
					var lonDeg = (x / width) * 360 - 180;
					var latDeg = 90 - (y / height) * 180;

					if (fallbackLandMask(lonDeg, latDeg)) {
						var lat = (y / height) * Math.PI - Math.PI / 2;
						var lon = (x / width) * 2 * Math.PI - Math.PI;
						var r = 10.5;

						var px = -r * Math.cos(lat) * Math.cos(lon);
						var py = -r * Math.sin(lat);
						var pz = r * Math.cos(lat) * Math.sin(lon);

						dotCoords.push(px, py, pz);
						flatCoords.push((x - width / 2) / 30, (height / 2 - y) / 30, 0);
						positions.push(px, py, pz);
					}
				}
			}

			return positions;
		}

		function createParticles(positions) {
			var geom = new THREE.BufferGeometry();
			geom.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));

			var mat = new THREE.PointsMaterial({
				color: 0xff0027,
				size: 0.08,
				transparent: true,
				opacity: 0.6,
				depthWrite: false
			});

			particles = new THREE.Points(geom, mat);
			scene.add(particles);

			initMarkers();

			if (loadingEl) {
				loadingEl.style.display = 'none';
			}
		}

		function createDottedMap() {
			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d', { willReadFrequently: true });
			canvas.width = 1024;
			canvas.height = 512;

			var img = new Image();
			img.crossOrigin = 'anonymous';

			img.onload = function () {
				try {
					ctx.drawImage(img, 0, 0, 1024, 512);
					var imageData = ctx.getImageData(0, 0, 1024, 512).data;
					var positions = buildDotsFromImageData(imageData, 1024, 512);

					if (!positions.length) {
						createParticles(buildFallbackDots());
						return;
					}

					createParticles(positions);
				} catch (error) {
					console.warn('Earth texture blocked, fallback map used.', error);
					createParticles(buildFallbackDots());
				}
			};

			img.onerror = function () {
				console.warn('Earth texture failed, fallback map used.');
				createParticles(buildFallbackDots());
			};

			img.src = 'https://raw.githubusercontent.com/mrdoob/three.js/master/examples/textures/planets/earth_atmos_2048.jpg';
		}

		function initMarkers() {
			labelsContainer.innerHTML = '';

			markerData.forEach(function (m) {
				var marker = document.createElement('div');
				marker.className = 'gt-marker';

				marker.innerHTML =
					'<div class="gt-tooltip">' +
						'<span class="gt-country-title">' + m.name + '</span>' +
						'<span class="gt-traffic-val-container">' + m.status + '</span>' +
						'<span class="gt-tooltip-detail">' + m.detail + '</span>' +
					'</div>';

				labelsContainer.appendChild(marker);

				var phi = (90 - m.lat) * (Math.PI / 180);
				var theta = (m.lon + 180) * (Math.PI / 180);
				var r = 10.6;

				var sPos = new THREE.Vector3(
					-r * Math.sin(phi) * Math.cos(theta),
					r * Math.cos(phi),
					r * Math.sin(phi) * Math.sin(theta)
				);

				var fPos = new THREE.Vector3(
					(m.lon / 180) * 17.1,
					(m.lat / 90) * 8.5,
					0
				);

				markers.push({
					el: marker,
					sPos: sPos,
					fPos: fPos,
					cur: sPos.clone()
				});
			});
		}

		toggleBtn.addEventListener('click', function () {
			if (!particles) {
				return;
			}

			isFlat = !isFlat;

			var targetDots = isFlat ? flatCoords : dotCoords;
			var curDots = particles.geometry.attributes.position.array;

			iconEl.setAttribute('data-lucide', isFlat ? 'globe' : 'map');

			if (window.lucide) {
				window.lucide.createIcons();
			}

			gsap.to(curDots, {
				endArray: targetDots,
				duration: 2,
				ease: 'expo.inOut',
				onUpdate: function () {
					particles.geometry.attributes.position.needsUpdate = true;
				}
			});

			if (isFlat) {
				controls.enableRotate = false;

				controls.mouseButtons = {
					LEFT: THREE.MOUSE.PAN,
					MIDDLE: THREE.MOUSE.PAN,
					RIGHT: THREE.MOUSE.PAN
				};

				controls.touches = {
					ONE: THREE.TOUCH.PAN,
					TWO: THREE.TOUCH.PAN
				};

				gsap.to(camera.position, {
					x: 0,
					y: 0,
					z: 18,
					duration: 2,
					ease: 'expo.inOut'
				});

				gsap.to(controls.target, {
					x: 0,
					y: 0,
					z: 0,
					duration: 2,
					ease: 'expo.inOut'
				});

				gsap.to(particles.rotation, {
					x: 0,
					y: 0,
					z: 0,
					duration: 1.5,
					ease: 'expo.inOut'
				});
			} else {
				controls.enableRotate = true;

				controls.mouseButtons = {
					LEFT: THREE.MOUSE.ROTATE,
					MIDDLE: THREE.MOUSE.PAN,
					RIGHT: THREE.MOUSE.PAN
				};

				controls.touches = {
					ONE: THREE.TOUCH.ROTATE,
					TWO: THREE.TOUCH.PAN
				};

				gsap.to(camera.position, {
					x: 0,
					y: 0,
					z: 18,
					duration: 2,
					ease: 'expo.inOut'
				});
			}

			markers.forEach(function (m) {
				gsap.to(m.cur, {
					x: isFlat ? m.fPos.x : m.sPos.x,
					y: isFlat ? m.fPos.y : m.sPos.y,
					z: isFlat ? m.fPos.z : m.sPos.z,
					duration: 2,
					ease: 'expo.inOut'
				});
			});
		});

		function animate() {
			animationFrameId = requestAnimationFrame(animate);

			controls.update();

			if (particles && !isFlat) {
				particles.rotation.y += 0.001;
			}

			var size = getSize();
			var wH = size.width / 2;
			var hH = size.height / 2;

			markers.forEach(function (m) {
				if (!particles) {
					return;
				}

				var p = m.cur.clone();

				if (!isFlat) {
					p.applyQuaternion(particles.quaternion);
				}

				p.project(camera);

				m.el.style.display = (!isFlat && p.z > 1) ? 'none' : 'flex';
				m.el.style.left = ((p.x * wH) + wH) + 'px';
				m.el.style.top = (-(p.y * hH) + hH) + 'px';
			});

			renderer.render(scene, camera);
		}

		function resize() {
			var size = getSize();

			camera.aspect = size.width / size.height;
			camera.updateProjectionMatrix();

			renderer.setSize(size.width, size.height);
			renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
		}

		if ('ResizeObserver' in window) {
			var resizeObserver = new ResizeObserver(resize);
			resizeObserver.observe(root);
		} else {
			window.addEventListener('resize', resize);
		}

		createDottedMap();
		animate();

		window.addEventListener('beforeunload', function () {
			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();