var AuraThree = (function() {
    'use strict';

    var scenes = [];
    var animating = true;
    var mouseX = 0, mouseY = 0;
    var mouseTargetX = 0, mouseTargetY = 0;

    document.addEventListener('mousemove', function(e) {
        mouseTargetX = (e.clientX / window.innerWidth) * 2 - 1;
        mouseTargetY = -(e.clientY / window.innerHeight) * 2 + 1;
    });

    function lerp(a, b, t) { return a + (b - a) * t; }

    function AuraScene(container, opts) {
        this.container = container;
        this.opts = Object.assign({
            alpha: true,
            antialias: true,
            pixelRatio: Math.min(window.devicePixelRatio, 2),
            fogColor: 0xfcfbfa,
            fogNear: 10,
            fogFar: 50
        }, opts);

        this.scene = new THREE.Scene();
        if (this.opts.fogColor !== undefined) {
            this.scene.fog = new THREE.FogExp2(this.opts.fogColor, 0.025);
        }

        var w = container.clientWidth || window.innerWidth;
        var h = container.clientHeight || window.innerHeight;

        this.camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 1000);
        this.camera.position.set(0, 0, 20);

        this.renderer = new THREE.WebGLRenderer({
            alpha: this.opts.alpha,
            antialias: this.opts.antialias,
            powerPreference: 'high-performance'
        });
        this.renderer.setSize(w, h);
        this.renderer.setPixelRatio(this.opts.pixelRatio);
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.2;
        this.renderer.outputColorSpace = THREE.SRGBColorSpace;
        container.appendChild(this.renderer.domElement);

        this.objects = [];
        this.clock = new THREE.Clock();
        this.id = scenes.length;
        scenes.push(this);

        this._onResize = this._handleResize.bind(this);
        window.addEventListener('resize', this._onResize);

        if (!AuraScene._mainLoopStarted) {
            AuraScene._mainLoopStarted = true;
            function mainLoop() {
                if (animating) {
                    mouseX = lerp(mouseX, mouseTargetX, 0.05);
                    mouseY = lerp(mouseY, mouseTargetY, 0.05);
                    scenes.forEach(function(s) { s._render(); });
                }
                requestAnimationFrame(mainLoop);
            }
            requestAnimationFrame(mainLoop);
        }
    }

    AuraScene.prototype._handleResize = function() {
        var w = this.container.clientWidth || window.innerWidth;
        var h = this.container.clientHeight || window.innerHeight;
        this.camera.aspect = w / h;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(w, h);
    };

    AuraScene.prototype._render = function() {
        var delta = this.clock.getDelta();
        var elapsed = this.clock.elapsedTime;
        if (this.onUpdate) this.onUpdate(delta, elapsed, mouseX, mouseY);
        this.renderer.render(this.scene, this.camera);
    };

    AuraScene.prototype.add = function(obj) {
        this.scene.add(obj);
        this.objects.push(obj);
    };

    AuraScene.prototype.remove = function(obj) {
        this.scene.remove(obj);
        var idx = this.objects.indexOf(obj);
        if (idx >= 0) this.objects.splice(idx, 1);
    };

    AuraScene.prototype.dispose = function() {
        window.removeEventListener('resize', this._onResize);
        scenes.splice(scenes.indexOf(this), 1);
        this.objects.forEach(function(obj) {
            if (obj.geometry) obj.geometry.dispose();
            if (obj.material) obj.material.dispose();
        });
        this.renderer.dispose();
        if (this.container.contains(this.renderer.domElement)) {
            this.container.removeChild(this.renderer.domElement);
        }
    };

    AuraScene.pauseAll = function() { animating = false; };
    AuraScene.resumeAll = function() { animating = true; };
    AuraScene._mainLoopStarted = false;

    // ── Hero: Realistic Architectural Sculpture ──
    AuraScene.createParticleField = function(container, opts) {
        opts = Object.assign({
            autoRotateSpeed: 0.12
        }, opts);

        var scene = new AuraScene(container, {
            alpha: true,
            antialias: true,
            fogColor: 0xfcfbfa,
            fogNear: 12,
            fogFar: 35
        });

        var sculptureGroup = new THREE.Group();
        var orbiters = [];

        var goldMat = new THREE.MeshPhysicalMaterial({
            color: 0x8c7b6c,
            roughness: 0.15,
            metalness: 0.9,
            clearcoat: 0.6,
            clearcoatRoughness: 0.15,
            envMapIntensity: 1.5
        });
        var darkMat = new THREE.MeshPhysicalMaterial({
            color: 0x3a322c,
            roughness: 0.2,
            metalness: 0.85,
            clearcoat: 0.4,
            clearcoatRoughness: 0.2
        });
        var glassMat = new THREE.MeshPhysicalMaterial({
            color: 0xd6cec5,
            roughness: 0.05,
            metalness: 0.3,
            transmission: 0.6,
            thickness: 0.5,
            clearcoat: 1.0,
            clearcoatRoughness: 0.1,
            transparent: true,
            opacity: 0.85
        });
        var lightMat = new THREE.MeshPhysicalMaterial({
            color: 0xc4b8ab,
            roughness: 0.25,
            metalness: 0.7,
            clearcoat: 0.5,
            clearcoatRoughness: 0.2
        });

        var ringGeo = new THREE.TorusGeometry(1.4, 0.06, 24, 64);
        var ring = new THREE.Mesh(ringGeo, goldMat);
        sculptureGroup.add(ring);

        var ring2Geo = new THREE.TorusGeometry(1.1, 0.04, 20, 48);
        var ring2 = new THREE.Mesh(ring2Geo, darkMat);
        ring2.rotation.x = Math.PI / 2.5;
        ring2.rotation.z = Math.PI / 4;
        sculptureGroup.add(ring2);

        var coreGeo = new THREE.IcosahedronGeometry(0.35, 1);
        var core = new THREE.Mesh(coreGeo, glassMat);
        sculptureGroup.add(core);

        var shapes = [
            { geo: new THREE.OctahedronGeometry(0.18, 0), mat: goldMat, orbit: 1.8, speed: 0.3, phase: 0, floatAmp: 0.15, floatSpeed: 0.6 },
            { geo: new THREE.TetrahedronGeometry(0.15, 0), mat: darkMat, orbit: 2.0, speed: 0.22, phase: Math.PI * 0.4, floatAmp: 0.12, floatSpeed: 0.8 },
            { geo: new THREE.IcosahedronGeometry(0.14, 0), mat: lightMat, orbit: 2.2, speed: 0.18, phase: Math.PI * 0.8, floatAmp: 0.18, floatSpeed: 0.5 },
            { geo: new THREE.OctahedronGeometry(0.12, 0), mat: glassMat, orbit: 1.6, speed: 0.35, phase: Math.PI * 1.2, floatAmp: 0.1, floatSpeed: 0.7 },
            { geo: new THREE.DodecahedronGeometry(0.13, 0), mat: goldMat, orbit: 2.4, speed: 0.15, phase: Math.PI * 1.6, floatAmp: 0.14, floatSpeed: 0.45 }
        ];

        shapes.forEach(function(s) {
            var mesh = new THREE.Mesh(s.geo, s.mat);
            mesh.userData = {
                orbitRadius: s.orbit,
                orbitSpeed: s.speed,
                orbitPhase: s.phase,
                floatAmp: s.floatAmp,
                floatSpeed: s.floatSpeed,
                rotSpeed: (Math.random() - 0.5) * 0.015
            };
            sculptureGroup.add(mesh);
            orbiters.push(mesh);
        });

        var dustCount = 300;
        var dustGeo = new THREE.BufferGeometry();
        var dustPos = new Float32Array(dustCount * 3);
        var dustSizes = new Float32Array(dustCount);
        for (var i = 0; i < dustCount; i++) {
            dustPos[i * 3] = (Math.random() - 0.5) * 14;
            dustPos[i * 3 + 1] = (Math.random() - 0.5) * 10;
            dustPos[i * 3 + 2] = (Math.random() - 0.5) * 10;
            dustSizes[i] = 0.02 + Math.random() * 0.04;
        }
        dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
        var dustMat = new THREE.PointsMaterial({
            color: 0xc4b8ab,
            size: 0.04,
            transparent: true,
            opacity: 0.35,
            depthWrite: false,
            sizeAttenuation: true
        });
        var dust = new THREE.Points(dustGeo, dustMat);
        scene.add(dust);

        scene.add(sculptureGroup);

        var ambientLight = new THREE.AmbientLight(0xfcfbfa, 0.5);
        scene.add(ambientLight);
        var keyLight = new THREE.DirectionalLight(0xffffff, 1.3);
        keyLight.position.set(4, 8, 5);
        scene.add(keyLight);
        var fillLight = new THREE.DirectionalLight(0x8c7b6c, 0.4);
        fillLight.position.set(-5, 3, -3);
        scene.add(fillLight);
        var rimLight = new THREE.DirectionalLight(0xd6cec5, 0.6);
        rimLight.position.set(-2, -4, -6);
        scene.add(rimLight);
        var topLight = new THREE.PointLight(0xfff8ed, 0.5, 20);
        topLight.position.set(0, 6, 0);
        scene.add(topLight);

        scene.camera.position.set(2.5, 1.2, 4.5);
        scene.camera.lookAt(0, 0, 0);

        scene.onUpdate = function(delta, elapsed, mx, my) {
            sculptureGroup.rotation.y += delta * opts.autoRotateSpeed;
            sculptureGroup.rotation.x = Math.sin(elapsed * 0.06) * 0.04 + my * 0.03;

            core.rotation.x = elapsed * 0.15;
            core.rotation.y = elapsed * 0.2;

            ring.rotation.z = elapsed * 0.04;
            ring2.rotation.y = elapsed * 0.06;

            orbiters.forEach(function(mesh) {
                var ud = mesh.userData;
                var angle = ud.orbitPhase + elapsed * ud.orbitSpeed;
                mesh.position.x = Math.cos(angle) * ud.orbitRadius;
                mesh.position.z = Math.sin(angle) * ud.orbitRadius;
                mesh.position.y = Math.sin(elapsed * ud.floatSpeed + ud.orbitPhase) * ud.floatAmp;
                mesh.rotation.x += ud.rotSpeed;
                mesh.rotation.y += ud.rotSpeed * 1.3;
            });

            dust.rotation.y += delta * 0.003;
            dust.rotation.x = Math.sin(elapsed * 0.04) * 0.01;
            var dPos = dustGeo.attributes.position.array;
            for (var i = 0; i < dustCount; i++) {
                dPos[i * 3 + 1] += Math.sin(elapsed * 0.3 + i) * 0.0005;
            }
            dustGeo.attributes.position.needsUpdate = true;
        };

        return scene;
    };

    // ── 3D Procedural City Skyline (Realistic) ──
    AuraScene.createBuildingSkyline = function(container, opts) {
        opts = Object.assign({
            autoRotateSpeed: 0.1,
            buildingCount: 35
        }, opts);

        var scene = new AuraScene(container, {
            alpha: true,
            fogColor: 0xf2efe9,
            fogNear: 8,
            fogFar: 30
        });

        var cityGroup = new THREE.Group();

        var palette = [
            { color: 0x8c7b6c, roughness: 0.2, metalness: 0.8 },
            { color: 0xa09385, roughness: 0.25, metalness: 0.75 },
            { color: 0x6f5f51, roughness: 0.15, metalness: 0.85 },
            { color: 0xc4b8ab, roughness: 0.3, metalness: 0.6 },
            { color: 0x555049, roughness: 0.18, metalness: 0.9 }
        ];

        var glassPalette = [0x88aacc, 0x99bbdd, 0x7799bb, 0xaabbcc];

        var gridCols = 8;
        var gridRows = 5;
        var spacingX = 0.75;
        var spacingZ = 0.65;
        var idx = 0;

        for (var row = 0; row < gridRows; row++) {
            for (var col = 0; col < gridCols; col++) {
                if (idx >= opts.buildingCount) break;

                var p = palette[idx % palette.length];
                var distFromCenter = Math.sqrt(
                    Math.pow((col - gridCols / 2) / gridCols, 2) +
                    Math.pow((row - gridRows / 2) / gridRows, 2)
                );
                var heightBias = 1 - distFromCenter * 0.6;
                var h = 0.3 + Math.pow(Math.random(), 1.5) * 2.8 * heightBias + 0.2;
                var w = 0.2 + Math.random() * 0.3;
                var d = 0.2 + Math.random() * 0.25;

                var isTower = h > 1.8;
                var isSkyscraper = h > 2.4;

                var buildingMat = new THREE.MeshPhysicalMaterial({
                    color: p.color,
                    roughness: p.roughness,
                    metalness: p.metalness,
                    clearcoat: 0.3,
                    clearcoatRoughness: 0.3
                });

                var geo, mesh;
                if (isTower && Math.random() > 0.4) {
                    var radius = 0.1 + Math.random() * 0.12;
                    geo = new THREE.CylinderGeometry(radius, radius * 1.02, h, 12);
                    mesh = new THREE.Mesh(geo, buildingMat);
                } else {
                    geo = new THREE.BoxGeometry(w, h, d);
                    mesh = new THREE.Mesh(geo, buildingMat);
                }

                var x = (col - gridCols / 2 + 0.5) * spacingX + (Math.random() - 0.5) * 0.12;
                var z = (row - gridRows / 2 + 0.5) * spacingZ + (Math.random() - 0.5) * 0.1;
                mesh.position.set(x, h / 2 - 0.6, z);
                mesh.castShadow = true;
                mesh.receiveShadow = true;
                cityGroup.add(mesh);

                if (isSkyscraper) {
                    var spireGeo = new THREE.ConeGeometry(0.02, 0.5, 6);
                    var spireMat = new THREE.MeshStandardMaterial({ color: 0x999999, roughness: 0.2, metalness: 0.9 });
                    var spire = new THREE.Mesh(spireGeo, spireMat);
                    spire.position.set(x, h - 0.6 + 0.25, z);
                    cityGroup.add(spire);
                } else if (isTower && Math.random() > 0.5) {
                    var capGeo = new THREE.ConeGeometry(0.08 + Math.random() * 0.06, 0.2, 8);
                    var capMat = new THREE.MeshStandardMaterial({ color: 0xd6cec5, roughness: 0.2, metalness: 0.8 });
                    var cap = new THREE.Mesh(capGeo, capMat);
                    cap.position.set(x, h - 0.6 + 0.1, z);
                    cityGroup.add(cap);
                }

                var glassColor = glassPalette[Math.floor(Math.random() * glassPalette.length)];
                var winRows = Math.floor(h * 4);
                var winCols = isTower ? 4 : Math.max(2, Math.floor(w * 6));
                for (var wr = 0; wr < winRows; wr++) {
                    for (var wc = 0; wc < winCols; wc++) {
                        if (Math.random() > 0.65) continue;
                        var winGeo = new THREE.PlaneGeometry(0.025, 0.02);
                        var lit = Math.random() > 0.4;
                        var winMat = new THREE.MeshPhysicalMaterial({
                            color: lit ? 0xfff3dc : glassColor,
                            roughness: 0.05,
                            metalness: 0.4,
                            transparent: true,
                            opacity: lit ? 0.7 + Math.random() * 0.3 : 0.3 + Math.random() * 0.2,
                            emissive: lit ? 0xfff3dc : 0x000000,
                            emissiveIntensity: lit ? 0.15 : 0
                        });
                        var win = new THREE.Mesh(winGeo, winMat);

                        var faceIdx = Math.floor(Math.random() * 4);
                        var wx, wz, ry;
                        if (faceIdx === 0) { wx = x + w / 2 + 0.003; wz = z + (wc / (winCols - 1) - 0.5) * d * 0.8; ry = 0; }
                        else if (faceIdx === 1) { wx = x - w / 2 - 0.003; wz = z + (wc / (winCols - 1) - 0.5) * d * 0.8; ry = Math.PI; }
                        else if (faceIdx === 2) { wx = x + (wc / (winCols - 1) - 0.5) * w * 0.8; wz = z + d / 2 + 0.003; ry = Math.PI / 2; }
                        else { wx = x + (wc / (winCols - 1) - 0.5) * w * 0.8; wz = z - d / 2 - 0.003; ry = -Math.PI / 2; }
                        win.position.set(wx, 0.05 + wr * 0.1 - 0.6, wz);
                        win.rotation.y = ry;
                        cityGroup.add(win);
                    }
                }

                idx++;
            }
        }

        var groundMat = new THREE.MeshStandardMaterial({
            color: 0xe8e4de,
            roughness: 0.85,
            metalness: 0
        });
        var ground = new THREE.Mesh(new THREE.PlaneGeometry(12, 8), groundMat);
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = -0.6;
        ground.receiveShadow = true;
        cityGroup.add(ground);

        var gridHelper = new THREE.GridHelper(12, 24, 0xd6cec5, 0xe8e4de);
        gridHelper.position.y = -0.59;
        gridHelper.material.transparent = true;
        gridHelper.material.opacity = 0.15;
        cityGroup.add(gridHelper);

        scene.add(cityGroup);

        var ambientLight = new THREE.AmbientLight(0xf5f0ea, 0.6);
        scene.add(ambientLight);
        var sunLight = new THREE.DirectionalLight(0xfff5e6, 1.4);
        sunLight.position.set(5, 12, 6);
        sunLight.castShadow = true;
        sunLight.shadow.mapSize.set(1024, 1024);
        scene.add(sunLight);
        var fillLight = new THREE.DirectionalLight(0x8c7b6c, 0.3);
        fillLight.position.set(-5, 4, -4);
        scene.add(fillLight);
        var bounceLight = new THREE.DirectionalLight(0xf5f3f0, 0.25);
        bounceLight.position.set(0, -3, -5);
        scene.add(bounceLight);

        scene.camera.position.set(3, 2.2, 4.5);
        scene.camera.lookAt(0, 0.3, 0);

        scene.onUpdate = function(delta, elapsed, mx, my) {
            cityGroup.rotation.y += delta * opts.autoRotateSpeed;
            cityGroup.rotation.x = Math.sin(elapsed * 0.018) * 0.012 + my * 0.012;
            cityGroup.position.y = Math.sin(elapsed * 0.08) * 0.01;
        };

        return scene;
    };

    // ── Floating Geometry Sculpture ──
    AuraScene.createFloatingSculpture = function(container, opts) {
        opts = Object.assign({
            count: 5,
            spread: 8,
            colors: [0x8c7b6c, 0xd6cec5, 0x555555, 0x888888]
        }, opts);

        var scene = new AuraScene(container, {
            alpha: true,
            fogColor: 0xf5f3f0,
            fogNear: 5,
            fogFar: 35
        });

        var group = new THREE.Group();
        var shapes = [];

        var geometries = [
            new THREE.TorusKnotGeometry(0.4, 0.15, 64, 8),
            new THREE.IcosahedronGeometry(0.5, 0),
            new THREE.OctahedronGeometry(0.5, 0),
            new THREE.TorusGeometry(0.5, 0.2, 16, 32),
            new THREE.DodecahedronGeometry(0.4, 0),
            new THREE.ConeGeometry(0.4, 0.8, 6)
        ];

        for (var i = 0; i < opts.count; i++) {
            var geo = geometries[i % geometries.length];
            var color = opts.colors[Math.floor(Math.random() * opts.colors.length)];
            var mat = new THREE.MeshPhysicalMaterial({
                color: color,
                roughness: 0.2 + Math.random() * 0.3,
                metalness: 0.6 + Math.random() * 0.4,
                clearcoat: 0.3,
                clearcoatRoughness: 0.4,
                transparent: true,
                opacity: 0.7 + Math.random() * 0.3,
                envMapIntensity: 1.0
            });
            var mesh = new THREE.Mesh(geo.clone(), mat);
            var angle = (i / opts.count) * Math.PI * 2;
            var radius = 1.5 + Math.random() * (opts.spread * 0.5);
            mesh.position.set(
                Math.cos(angle) * radius,
                (Math.random() - 0.5) * opts.spread * 0.4,
                Math.sin(angle) * radius
            );
            mesh.scale.setScalar(0.6 + Math.random() * 0.8);
            mesh.userData = {
                rotSpeedX: (Math.random() - 0.5) * 0.02,
                rotSpeedY: (Math.random() - 0.5) * 0.02,
                rotSpeedZ: (Math.random() - 0.5) * 0.02,
                floatSpeed: 0.2 + Math.random() * 0.4,
                floatPhase: Math.random() * Math.PI * 2,
                floatAmp: 0.2 + Math.random() * 0.3,
                orbitSpeed: 0.05 + Math.random() * 0.1,
                orbitRadius: radius,
                startAngle: angle
            };
            group.add(mesh);
            shapes.push(mesh);
        }

        scene.add(group);

        var ambient = new THREE.AmbientLight(0xfcfbfa, 0.6);
        scene.add(ambient);
        var keyLight = new THREE.DirectionalLight(0xffffff, 1.2);
        keyLight.position.set(3, 8, 5);
        scene.add(keyLight);
        var rimLight = new THREE.DirectionalLight(0x8c7b6c, 0.5);
        rimLight.position.set(-3, -2, -5);
        scene.add(rimLight);
        var spotLight = new THREE.SpotLight(0xffffff, 0.5);
        spotLight.position.set(0, 5, 0);
        scene.add(spotLight);

        var particleGeo = new THREE.BufferGeometry();
        var pCount = 500;
        var pPos = new Float32Array(pCount * 3);
        for (var j = 0; j < pCount; j++) {
            pPos[j * 3] = (Math.random() - 0.5) * 20;
            pPos[j * 3 + 1] = (Math.random() - 0.5) * 15;
            pPos[j * 3 + 2] = (Math.random() - 0.5) * 20;
        }
        particleGeo.setAttribute('position', new THREE.BufferAttribute(pPos, 3));
        var particleMat = new THREE.PointsMaterial({
            color: 0x9a9086,
            size: 0.03,
            transparent: true,
            opacity: 0.3,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        });
        var particles = new THREE.Points(particleGeo, particleMat);
        scene.add(particles);

        scene.camera.position.set(0, 0, 12);

        scene.onUpdate = function(delta, elapsed, mx, my) {
            shapes.forEach(function(mesh) {
                mesh.rotation.x += mesh.userData.rotSpeedX;
                mesh.rotation.y += mesh.userData.rotSpeedY;
                mesh.rotation.z += mesh.userData.rotSpeedZ;
                mesh.position.y += Math.sin(elapsed * mesh.userData.floatSpeed + mesh.userData.floatPhase) * 0.003;
                var angle = mesh.userData.startAngle + elapsed * mesh.userData.orbitSpeed;
                var r = mesh.userData.orbitRadius;
                mesh.position.x = Math.cos(angle) * r + mx * 0.5;
                mesh.position.z = Math.sin(angle) * r + my * 0.5;
            });
            group.rotation.y += delta * 0.02;
            particles.rotation.y += delta * 0.005;
        };

        return scene;
    };

    // ── 3D Property Viewer ──
    AuraScene.createPropertyViewer = function(container, opts) {
        opts = Object.assign({
            backgroundColor: 0xf5f3f0,
            autoRotate: true
        }, opts);

        var scene = new AuraScene(container, {
            alpha: false,
            antialias: true,
            fogColor: undefined,
            fogNear: undefined,
            fogFar: undefined
        });
        scene.renderer.setClearColor(opts.backgroundColor);
        scene.renderer.shadowMap.enabled = true;
        scene.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        var roomGroup = new THREE.Group();

        var floorMat = new THREE.MeshStandardMaterial({
            color: 0xe8e6e1,
            roughness: 0.6,
            metalness: 0.1
        });
        var floor = new THREE.Mesh(new THREE.PlaneGeometry(6, 4), floorMat);
        floor.rotation.x = -Math.PI / 2;
        floor.position.y = -1.5;
        floor.receiveShadow = true;
        roomGroup.add(floor);

        var wallMat = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            roughness: 0.9,
            metalness: 0,
            side: THREE.DoubleSide
        });
        var backWall = new THREE.Mesh(new THREE.PlaneGeometry(6, 3), wallMat);
        backWall.position.set(0, 0, -2);
        backWall.receiveShadow = true;
        roomGroup.add(backWall);

        var leftWall = new THREE.Mesh(new THREE.PlaneGeometry(4, 3), wallMat);
        leftWall.position.set(-3, 0, 0);
        leftWall.rotation.y = Math.PI / 2;
        roomGroup.add(leftWall);

        var rightWall = new THREE.Mesh(new THREE.PlaneGeometry(4, 3), wallMat);
        rightWall.position.set(3, 0, 0);
        rightWall.rotation.y = -Math.PI / 2;
        roomGroup.add(rightWall);

        var sofaMat = new THREE.MeshStandardMaterial({
            color: 0x8c7b6c,
            roughness: 0.8,
            metalness: 0.2
        });
        var sofa = new THREE.Mesh(new THREE.BoxGeometry(2, 0.5, 0.7), sofaMat);
        sofa.position.set(-0.8, -1.25, -0.3);
        sofa.castShadow = true;
        roomGroup.add(sofa);

        var cushionMat = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            roughness: 0.6,
            metalness: 0.1
        });
        for (var ci = 0; ci < 3; ci++) {
            var cushion = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.12, 0.5), cushionMat);
            cushion.position.set(-0.8 + (ci - 1) * 0.55, -0.98, -0.3);
            roomGroup.add(cushion);
        }

        var tableMat = new THREE.MeshStandardMaterial({
            color: 0x6f5f51,
            roughness: 0.2,
            metalness: 0.8
        });
        var table = new THREE.Mesh(new THREE.CylinderGeometry(0.1, 0.1, 0.7, 8), tableMat);
        table.position.set(1.0, -1.15, 0.5);
        table.castShadow = true;
        roomGroup.add(table);
        var tableTop = new THREE.Mesh(new THREE.CylinderGeometry(0.45, 0.45, 0.04, 16), tableMat);
        tableTop.position.set(1.0, -0.78, 0.5);
        tableTop.castShadow = true;
        roomGroup.add(tableTop);

        var lampMat = new THREE.MeshStandardMaterial({
            color: 0x5c5349,
            roughness: 0.4,
            metalness: 0.6
        });
        var lampBase = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.15, 0.6, 12), lampMat);
        lampBase.position.set(-1.8, -1.2, -1.0);
        lampBase.castShadow = true;
        roomGroup.add(lampBase);

        var lampshadeMat = new THREE.MeshStandardMaterial({
            color: 0x8c7b6c,
            roughness: 0.9,
            metalness: 0,
            emissive: 0xfff8ed,
            emissiveIntensity: 0.2
        });
        var lampshade = new THREE.Mesh(new THREE.ConeGeometry(0.25, 0.15, 12), lampshadeMat);
        lampshade.position.set(-1.8, -0.7, -1.0);
        roomGroup.add(lampshade);

        var artMat = new THREE.MeshStandardMaterial({
            color: 0x8c7b6c,
            roughness: 0.3,
            metalness: 0.5
        });
        var art = new THREE.Mesh(new THREE.PlaneGeometry(1.0, 0.7), artMat);
        art.position.set(0, 0.2, -1.99);
        roomGroup.add(art);

        scene.add(roomGroup);

        var ambient = new THREE.AmbientLight(0xfcfbfa, 0.7);
        scene.add(ambient);
        var mainLight = new THREE.DirectionalLight(0xffffff, 0.9);
        mainLight.position.set(3, 6, 4);
        mainLight.castShadow = true;
        scene.add(mainLight);
        var fillLight2 = new THREE.DirectionalLight(0xe8e5db, 0.4);
        fillLight2.position.set(-3, 3, -2);
        scene.add(fillLight2);

        scene.camera.position.set(4, 1.5, 5);
        scene.camera.lookAt(0, -0.5, 0);

        var rotSpeed = opts.autoRotate ? 0.15 : 0;
        var targetRot = 0;

        scene.onUpdate = function(delta, elapsed) {
            if (opts.autoRotate) {
                roomGroup.rotation.y += delta * rotSpeed;
            }
            lampshadeMat.emissiveIntensity = 0.12 + 0.05 * Math.sin(elapsed * 0.5);
        };

        scene.setAutoRotate = function(val) { opts.autoRotate = val; };
        scene.orbitLeft = function() { roomGroup.rotation.y += 0.5; };
        scene.orbitRight = function() { roomGroup.rotation.y -= 0.5; };

        return scene;
    };

    // ── 3D Floor Plan ──
    AuraScene.createFloorPlan = function(container, opts) {
        opts = Object.assign({
            rooms: [
                { w: 3, h: 0.05, d: 2.5, color: 0xe8e6e1, x: 0, z: 0, label: 'Living' },
                { w: 2, h: 0.05, d: 2, color: 0xf0eee9, x: -2.8, z: 0, label: 'Kitchen' },
                { w: 3, h: 0.05, d: 2, color: 0xf0eee9, x: 0, z: 2.8, label: 'Bedroom' }
            ],
            wallColor: 0x8c7b6c,
            wallHeight: 0.15
        }, opts);

        var scene = new AuraScene(container, {
            alpha: true,
            fogColor: undefined,
            fogNear: undefined,
            fogFar: undefined
        });

        var planGroup = new THREE.Group();

        opts.rooms.forEach(function(room) {
            var floorMat = new THREE.MeshStandardMaterial({
                color: room.color,
                roughness: 0.7,
                metalness: 0.05,
                transparent: true,
                opacity: 0.85
            });
            var floorMesh = new THREE.Mesh(new THREE.BoxGeometry(room.w, room.h, room.d), floorMat);
            floorMesh.position.set(room.x, 0, room.z);
            planGroup.add(floorMesh);

            var wallMat = new THREE.MeshBasicMaterial({
                color: opts.wallColor,
                transparent: true,
                opacity: 0.25
            });
            var wallPositions = [
                { x: room.x, z: room.z + room.d / 2, ry: 0, w: room.w, d: 0.03 },
                { x: room.x, z: room.z - room.d / 2, ry: 0, w: room.w, d: 0.03 },
                { x: room.x + room.w / 2, z: room.z, ry: Math.PI / 2, w: room.d, d: 0.03 },
                { x: room.x - room.w / 2, z: room.z, ry: Math.PI / 2, w: room.d, d: 0.03 }
            ];
            wallPositions.forEach(function(wp) {
                var wall = new THREE.Mesh(new THREE.BoxGeometry(wp.w, opts.wallHeight, wp.d), wallMat);
                wall.position.set(wp.x, opts.wallHeight / 2, wp.z);
                wall.rotation.y = wp.ry || 0;
                planGroup.add(wall);
            });

            if (room.label) {
                var canvas = document.createElement('canvas');
                canvas.width = 256;
                canvas.height = 64;
                var ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, 256, 64);
                ctx.fillStyle = '#8c7b6c';
                ctx.font = '22px DM Sans, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(room.label, 128, 32);
                var texture = new THREE.CanvasTexture(canvas);
                texture.needsUpdate = true;
                var labelMat = new THREE.SpriteMaterial({ map: texture, transparent: true, opacity: 0.5 });
                var label = new THREE.Sprite(labelMat);
                label.position.set(room.x, 0.3, room.z);
                label.scale.set(0.8, 0.2, 1);
                planGroup.add(label);
            }
        });

        scene.add(planGroup);

        var ambient = new THREE.AmbientLight(0xfcfbfa, 0.8);
        scene.add(ambient);
        var dirLight = new THREE.DirectionalLight(0xffffff, 0.6);
        dirLight.position.set(5, 8, 5);
        scene.add(dirLight);

        scene.camera.position.set(5, 4, 5);
        scene.camera.lookAt(0, 0, 0);

        scene.onUpdate = function(delta, elapsed, mx, my) {
            planGroup.rotation.y += delta * 0.05;
            planGroup.position.y = Math.sin(elapsed * 0.15) * 0.05;
        };

        return scene;
    };

    // ── GSAP Camera Flythrough ──
    AuraScene.flyCameraTo = function(scene, targetPos, targetLook, duration, callback) {
        if (!scene || !scene.camera) return;
        if (typeof gsap !== 'undefined') {
            gsap.to(scene.camera.position, {
                x: targetPos.x, y: targetPos.y, z: targetPos.z,
                duration: duration || 2,
                ease: 'power2.inOut',
                onUpdate: function() {
                    scene.camera.lookAt(targetLook.x, targetLook.y, targetLook.z);
                },
                onComplete: callback
            });
        }
    };

    return AuraScene;
})();
