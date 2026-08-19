(function () {
	"use strict";

	if (!document.body.classList.contains("iac-chrome-blog")) {
		return;
	}

	var cfg = window.iacGridConfig || {};
	var noiseUrl = cfg.noiseUrl || "";
	var blueUrl = cfg.blueUrl || "";

	var CELL = 20;
	var MAJOR = 7;
	var LINE_W = 2;
	var MAJOR_W = 2;
	var LINE_COLOR = [0x5c / 255, 0x5c / 255, 0x5c / 255];
	var HIGHLIGHT = [0x69 / 255, 0x69 / 255, 0x69 / 255];
	var LINE_OP = 0.02;
	var MAJOR_OP = 0.02;
	var SCROLL = 0.5;
	var HI_SPEED = 0.005;
	var HI_INT = 0.02;

	var VERT = [
		"attribute vec2 aPos;",
		"void main(){",
		"  gl_Position=vec4(aPos,0.0,1.0);",
		"}",
	].join("\n");

	var FRAG = [
		"precision highp float;",
		"uniform vec2 uResolution;",
		"uniform float uCellSize;",
		"uniform float uMajorMul;",
		"uniform float uLineWidth;",
		"uniform float uMajorWidth;",
		"uniform vec3 uLineColor;",
		"uniform vec3 uHighlight;",
		"uniform float uLineOpacity;",
		"uniform float uMajorOpacity;",
		"uniform vec2 uScroll;",
		"uniform float uTime;",
		"uniform float uHiSpeed;",
		"uniform float uHiInt;",
		"uniform float uGlobal;",
		"uniform sampler2D uNoise;",
		"uniform sampler2D uBlue;",
		"void main(){",
		"  vec2 e=gl_FragCoord.xy+uScroll;",
		"  float cs=uCellSize;",
		"  vec2 s=e/cs;",
		"  vec2 f=abs(fract(s-0.5)-0.5);",
		"  float hw=uLineWidth/cs*0.5;",
		"  float minor=max(smoothstep(hw,0.0,f.x),smoothstep(hw,0.0,f.y));",
		"  float majorSize=cs*uMajorMul;",
		"  vec2 my=e/majorSize;",
		"  vec2 mf=abs(fract(my-0.5)-0.5);",
		"  float mhw=uMajorWidth/majorSize*0.5;",
		"  float major=max(smoothstep(mhw,0.0,mf.x),smoothstep(mhw,0.0,mf.y));",
		"  vec2 cell=floor(e/majorSize);",
		"  float h=dot(cell,vec2(0.7137,0.9721));",
		"  vec2 g=cell*(1.0/64.0);",
		"  vec2 ns=g+vec2(uTime*uHiSpeed*0.05+h,uTime*uHiSpeed*0.035+h*1.37);",
		"  float n1=texture2D(uNoise,ns).r;",
		"  float n2=texture2D(uNoise,ns*2.37+vec2(1.73,2.19)).r;",
		"  float nb=n1*0.65+n2*0.35;",
		"  float hi=smoothstep(0.7,1.0,nb)*uHiInt;",
		"  float lines=clamp(minor*uLineOpacity+major*uMajorOpacity,0.0,1.0);",
		"  float alpha=lines+hi*(1.0-lines);",
		"  float mixT=clamp(lines/max(alpha,0.001),0.0,1.0);",
		"  vec3 rgb=mix(uHighlight,uLineColor,mixT);",
		"  float dither=(texture2D(uBlue,e/512.0+uTime*0.1).r-0.5)*(1.0/255.0);",
		"  alpha=clamp(alpha+dither,0.0,1.0);",
		"  gl_FragColor=vec4(rgb,alpha*uGlobal);",
		"}",
	].join("\n");

	function compile(gl, type, src) {
		var sh = gl.createShader(type);
		gl.shaderSource(sh, src);
		gl.compileShader(sh);
		if (!gl.getShaderParameter(sh, gl.COMPILE_STATUS)) {
			console.error("[iac-grid]", gl.getShaderInfoLog(sh));
			return null;
		}
		return sh;
	}

	function link(gl, vs, fs) {
		var prog = gl.createProgram();
		gl.attachShader(prog, vs);
		gl.attachShader(prog, fs);
		gl.linkProgram(prog);
		if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
			console.error("[iac-grid]", gl.getProgramInfoLog(prog));
			return null;
		}
		return prog;
	}

	function loadImage(url) {
		return new Promise(function (resolve, reject) {
			var img = new Image();
			img.crossOrigin = "anonymous";
			img.onload = function () {
				resolve(img);
			};
			img.onerror = reject;
			img.src = url;
		});
	}

	function texFromImage(gl, img) {
		var tex = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, tex);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.REPEAT);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.REPEAT);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
		return tex;
	}

	function makeFallbackTex(gl, value) {
		var c = document.createElement("canvas");
		c.width = c.height = 4;
		var ctx = c.getContext("2d");
		ctx.fillStyle = "rgb(" + value + "," + value + "," + value + ")";
		ctx.fillRect(0, 0, 4, 4);
		return texFromImage(gl, c);
	}

	var canvas = document.querySelector(".iac-grid-canvas");
	if (!canvas) {
		return;
	}

	var gl = canvas.getContext("webgl", { alpha: false, antialias: false, depth: false });
	if (!gl) {
		return;
	}

	var vs = compile(gl, gl.VERTEX_SHADER, VERT);
	var fs = compile(gl, gl.FRAGMENT_SHADER, FRAG);
	if (!vs || !fs) {
		return;
	}
	var prog = link(gl, vs, fs);
	if (!prog) {
		return;
	}

	var buf = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, buf);
	gl.bufferData(
		gl.ARRAY_BUFFER,
		new Float32Array([-1, -1, 3, -1, -1, 3]),
		gl.STATIC_DRAW
	);

	var loc = {
		res: gl.getUniformLocation(prog, "uResolution"),
		cell: gl.getUniformLocation(prog, "uCellSize"),
		major: gl.getUniformLocation(prog, "uMajorMul"),
		lw: gl.getUniformLocation(prog, "uLineWidth"),
		mw: gl.getUniformLocation(prog, "uMajorWidth"),
		lc: gl.getUniformLocation(prog, "uLineColor"),
		hi: gl.getUniformLocation(prog, "uHighlight"),
		lo: gl.getUniformLocation(prog, "uLineOpacity"),
		mo: gl.getUniformLocation(prog, "uMajorOpacity"),
		scroll: gl.getUniformLocation(prog, "uScroll"),
		time: gl.getUniformLocation(prog, "uTime"),
		hs: gl.getUniformLocation(prog, "uHiSpeed"),
		hiI: gl.getUniformLocation(prog, "uHiInt"),
		global: gl.getUniformLocation(prog, "uGlobal"),
		noise: gl.getUniformLocation(prog, "uNoise"),
		blue: gl.getUniformLocation(prog, "uBlue"),
	};
	var aPos = gl.getAttribLocation(prog, "aPos");

	var dpr = 1;
	var global = 0;
	var start = performance.now();
	var time = 0;
	var texNoise = null;
	var texBlue = null;

	function resize() {
		dpr = Math.min(window.devicePixelRatio || 1, 2);
		var w = Math.floor(window.innerWidth * dpr);
		var h = Math.floor(window.innerHeight * dpr);
		if (canvas.width !== w || canvas.height !== h) {
			canvas.width = w;
			canvas.height = h;
			canvas.style.width = window.innerWidth + "px";
			canvas.style.height = window.innerHeight + "px";
			gl.viewport(0, 0, w, h);
		}
	}

	function draw() {
		resize();
		var elapsed = (performance.now() - start) / 1000;
		time += 1 / 60;
		global = Math.min(1, elapsed);

		gl.clearColor(0, 0, 0, 1);
		gl.clear(gl.COLOR_BUFFER_BIT);
		gl.useProgram(prog);
		gl.bindBuffer(gl.ARRAY_BUFFER, buf);
		gl.enableVertexAttribArray(aPos);
		gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);

		gl.uniform2f(loc.res, canvas.width, canvas.height);
		gl.uniform1f(loc.cell, CELL * dpr);
		gl.uniform1f(loc.major, MAJOR);
		gl.uniform1f(loc.lw, LINE_W * dpr);
		gl.uniform1f(loc.mw, MAJOR_W * dpr);
		gl.uniform3fv(loc.lc, LINE_COLOR);
		gl.uniform3fv(loc.hi, HIGHLIGHT);
		gl.uniform1f(loc.lo, LINE_OP);
		gl.uniform1f(loc.mo, MAJOR_OP);
		gl.uniform2f(
			loc.scroll,
			window.scrollX * dpr,
			-window.scrollY * dpr * SCROLL
		);
		gl.uniform1f(loc.time, time);
		gl.uniform1f(loc.hs, HI_SPEED);
		gl.uniform1f(loc.hiI, HI_INT);
		gl.uniform1f(loc.global, global);

		gl.activeTexture(gl.TEXTURE0);
		gl.bindTexture(gl.TEXTURE_2D, texNoise);
		gl.uniform1i(loc.noise, 0);
		gl.activeTexture(gl.TEXTURE1);
		gl.bindTexture(gl.TEXTURE_2D, texBlue);
		gl.uniform1i(loc.blue, 1);

		gl.enable(gl.BLEND);
		gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
		gl.drawArrays(gl.TRIANGLES, 0, 3);
		requestAnimationFrame(draw);
	}

	Promise.all([
		noiseUrl ? loadImage(noiseUrl) : Promise.reject(),
		blueUrl ? loadImage(blueUrl) : Promise.reject(),
	])
		.then(function (imgs) {
			texNoise = texFromImage(gl, imgs[0]);
			texBlue = texFromImage(gl, imgs[1]);
		})
		.catch(function () {
			texNoise = makeFallbackTex(gl, 128);
			texBlue = makeFallbackTex(gl, 128);
		})
		.finally(function () {
			requestAnimationFrame(draw);
		});

	window.addEventListener("resize", resize);
})();
