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

    // ── Hero Particle Field ──
    var _sharedParticleGeo = null;

    AuraScene.createParticleField = function(container, opts) {
        opts = Object.assign({
            count: 4000,
            spread: 50,
            speed: 0.4,
            size: 0.06,
            color: 0x8c7b6c,
            opacity: 0.5,
            gradient: true
        }, opts);

        var scene = new AuraScene(container, {
            alpha: true,
            antialias: false,
            fogColor: 0xfcfbfa,
            fogNear: 5,
            fogFar: 60
        });

        if (!_sharedParticleGeo) {
            _sharedParticleGeo = new THREE.BufferGeometry();
            var total = 4000;
            var pos = new Float32Array(total * 3);
            var col = new Float32Array(total * 3);
            var sz = new Float32Array(total);
            var spd = new Float32Array(total);
            var phs = new Float32Array(total);
            var baseColor = new THREE.Color(0x8c7b6c);
            for (var i = 0; i < total; i++) {
                var radius = 5 + Math.random() * 45;
                var theta = Math.random() * Math.PI * 2;
                var phi = Math.random() * Math.PI * 2;
                pos[i * 3] = Math.sin(theta) * Math.cos(phi) * radius;
                pos[i * 3 + 1] = Math.sin(theta) * Math.sin(phi) * radius * 0.6;
                pos[i * 3 + 2] = Math.cos(theta) * radius * 0.8;
                sz[i] = 0.06 * (0.3 + Math.random() * 1.2);
                spd[i] = 0.1 + Math.random() * 0.5;
                phs[i] = Math.random() * Math.PI * 2;
                var c = baseColor.clone().lerp(new THREE.Color(0xd6cec5), Math.random() * 0.4);
                col[i * 3] = c.r; col[i * 3 + 1] = c.g; col[i * 3 + 2] = c.b;
            }
            _sharedParticleGeo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
            _sharedParticleGeo.setAttribute('size', new THREE.BufferAttribute(sz, 1));
            _sharedParticleGeo.setAttribute('speed', new THREE.BufferAttribute(spd, 1));
            _sharedParticleGeo.setAttribute('phase', new THREE.BufferAttribute(phs, 1));
            _sharedParticleGeo.setAttribute('color', new THREE.BufferAttribute(col, 3));
            _sharedParticleGeo._speeds = spd;
            _sharedParticleGeo._phases = phs;
        }

        var geometry = _sharedParticleGeo.clone();

        var material = new THREE.PointsMaterial({
            size: 0.06,
            transparent: true,
            opacity: 0.5,
            blending: THREE.AdditiveBlending,
            depthWrite: false,
            sizeAttenuation: true,
            vertexColors: true
        });

        var points = new THREE.Points(geometry, material);
        scene.add(points);
        scene.camera.position.z = 22;

        var driftX = 0, driftY = 0;
        var targetDriftX = 0, targetDriftY = 0;

        scene.onUpdate = function(delta, elapsed, mx, my) {
            var pos = geometry.attributes.position.array;
            var szArr = geometry.attributes.size.array;
            var spdArr = _sharedParticleGeo._speeds;
            var phsArr = _sharedParticleGeo._phases;

            targetDriftX = mx * 0.4;
            targetDriftY = my * 0.4;
            driftX += (targetDriftX - driftX) * 0.02;
            driftY += (targetDriftY - driftY) * 0.02;

            for (var i = 0; i < total; i++) {
                var i3 = i * 3;
                var wave = Math.sin(elapsed * spdArr[i] + phsArr[i]);
                var wave2 = Math.cos(elapsed * spdArr[i] * 0.7 + phsArr[i] * 1.3);
                pos[i3] += wave * 0.004 + driftX * 0.001;
                pos[i3 + 1] += wave2 * 0.004 + driftY * 0.001;
                pos[i3 + 2] += Math.sin(elapsed * spdArr[i] * 0.5 + phsArr[i]) * 0.003;
                szArr[i] = 0.06 * (0.5 + 0.5 * Math.sin(elapsed * 0.8 + phsArr[i]));
            }
            geometry.attributes.position.needsUpdate = true;
            geometry.attributes.size.needsUpdate = true;
            points.rotation.y += delta * 0.008;
            material.opacity = 0.5 * (0.8 + 0.2 * Math.sin(elapsed * 0.15));
        };

        return scene;
    };

    // ── 3D Building Skyline (Real GLB Model) ──
    AuraScene.createBuildingSkyline = function(container, opts) {
        opts = Object.assign({
            scale: 2.5,
            autoRotateSpeed: 0.3
        }, opts);

        var scene = new AuraScene(container, {
            alpha: true,
            fogColor: 0xfcfbfa,
            fogNear: 8,
            fogFar: 35
        });

        var modelGroup = new THREE.Group();
        var modelLoaded = false;

        var ambientLight = new THREE.AmbientLight(0xfcfbfa, 0.8);
        scene.add(ambientLight);
        var dirLight = new THREE.DirectionalLight(0x8c7b6c, 1.0);
        dirLight.position.set(5, 15, 10);
        scene.add(dirLight);
        var fillLight = new THREE.DirectionalLight(0xf5f3f0, 0.4);
        fillLight.position.set(-5, 5, -5);
        scene.add(fillLight);
        var rimLight = new THREE.DirectionalLight(0xffffff, 0.5);
        rimLight.position.set(-3, -2, -8);
        scene.add(rimLight);

        var groundMat = new THREE.MeshStandardMaterial({
            color: 0xf5f3f0,
            transparent: true,
            opacity: 0.2,
            roughness: 0.9,
            metalness: 0
        });
        var ground = new THREE.Mesh(new THREE.CircleGeometry(12, 32), groundMat);
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = -1.5;
        scene.add(ground);

        scene.camera.position.set(0, 0.5, 8);
        scene.camera.lookAt(0, 0, 0);

        function loadModel() {
            if (typeof THREE.GLTFLoader !== 'undefined') {
                var loader = new THREE.GLTFLoader();
                loader.load('resources/models/building.glb', function(gltf) {
                    var model = gltf.scene;
                    model.scale.setScalar(opts.scale);
                    model.position.y = -0.5;
                    model.traverse(function(child) {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                        }
                    });
                    modelGroup.add(model);
                    modelLoaded = true;
                }, undefined, function(error) {
                    console.error('Building model load error:', error);
                    fallbackModel();
                });
            } else {
                console.warn('GLTFLoader not available');
                fallbackModel();
            }
        }

        function fallbackModel() {
            var geo = new THREE.BoxGeometry(2.5, 3.5, 1.8);
            var mat = new THREE.MeshStandardMaterial({
                color: 0x8c7b6c,
                roughness: 0.2,
                metalness: 0.8
            });
            var mesh = new THREE.Mesh(geo, mat);
            mesh.position.y = -0.3;
            modelGroup.add(mesh);
            modelLoaded = true;
        }

        scene.add(modelGroup);
        loadModel();

        scene.onUpdate = function(delta, elapsed, mx, my) {
            if (modelLoaded) {
                modelGroup.rotation.y += delta * opts.autoRotateSpeed;
                modelGroup.rotation.x = Math.sin(elapsed * 0.015) * 0.02 + my * 0.02;
            }
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
