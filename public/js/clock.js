/*
 * ClockFence clock page.
 *
 * The order of steps is the security and never changes:
 *   1. read the location once, 2. server Geo-check issues a Clock token,
 *   3. only then open the camera, 4. scan the Poster, which spends the token.
 * Location is never tracked in the background; distance and coordinates are never shown.
 */
(function () {
    'use strict';

    var config = JSON.parse(
        document.getElementById('clock-config').textContent,
    );
    var copy = config.copy;
    var failures = config.failures;
    var csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    var statusEl = document.getElementById('clock-status');
    var actionEl = document.getElementById('clock-action');
    var cancelEl = document.getElementById('clock-cancel');
    var cameraEl = document.getElementById('clock-camera');
    var videoEl = document.getElementById('clock-video');
    var countdownEl = document.getElementById('clock-countdown');

    var state = config.state;
    var stream = null;
    var scanTimer = null;
    var countdownTimer = null;

    function fill(text, replacements) {
        return Object.keys(replacements || {}).reduce(function (result, key) {
            return result.split(':' + key).join(String(replacements[key]));
        }, text);
    }

    function setStatus(text, tone) {
        statusEl.textContent = text || '';
        statusEl.className = 'status' + (tone ? ' ' + tone : '');
    }

    function showAction(label) {
        actionEl.textContent = label;
        actionEl.disabled = false;
        actionEl.hidden = false;
    }

    function hideAction() {
        actionEl.hidden = true;
    }

    function showIdle() {
        stopCamera();
        cancelEl.hidden = true;

        if (state === 'in') {
            showAction(copy.clock_in);
        } else if (state === 'out') {
            showAction(copy.clock_out);
        } else {
            hideAction();
            setStatus(copy[state] || copy.done);
        }
    }

    function showFailure(code) {
        var failure = failures[code] || failures.token_invalid;
        stopCamera();
        cancelEl.hidden = true;
        setStatus(failure.message, 'bad');

        if (failure.retryable && (state === 'in' || state === 'out')) {
            showAction(copy.try_again);
        } else {
            hideAction();
        }
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        }).then(function (response) {
            if (response.status === 401) {
                window.location.href = '/login';
                return new Promise(function () {});
            }
            if (response.status === 419) {
                window.location.reload();
                return new Promise(function () {});
            }
            return response.json().then(function (json) {
                return { ok: response.ok, data: json.data || {} };
            });
        });
    }

    // Step 1: one location read, high accuracy, no cached fix, no watching.
    function readLocation() {
        return new Promise(function (resolve, reject) {
            if (!('geolocation' in navigator)) {
                reject('location_unavailable');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                resolve,
                function (error) {
                    reject(
                        error.code === error.PERMISSION_DENIED
                            ? 'location_denied'
                            : error.code === error.TIMEOUT
                              ? 'location_timeout'
                              : 'location_unavailable',
                    );
                },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 },
            );
        });
    }

    // Steps 3 and 4: camera only with a token; the scan spends it.
    function createDetector() {
        if ('BarcodeDetector' in window) {
            var detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            return function () {
                return detector.detect(videoEl).then(function (codes) {
                    return codes.length ? codes[0].rawValue : null;
                });
            };
        }

        if (typeof window.jsQR === 'function') {
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d', { willReadFrequently: true });
            return function () {
                if (!videoEl.videoWidth) {
                    return Promise.resolve(null);
                }
                canvas.width = videoEl.videoWidth;
                canvas.height = videoEl.videoHeight;
                context.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                var image = context.getImageData(
                    0,
                    0,
                    canvas.width,
                    canvas.height,
                );
                var code = window.jsQR(image.data, image.width, image.height);
                return Promise.resolve(code ? code.data : null);
            };
        }

        return null;
    }

    function stopCamera() {
        clearInterval(scanTimer);
        clearInterval(countdownTimer);
        scanTimer = null;
        countdownTimer = null;
        if (stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
            stream = null;
        }
        videoEl.srcObject = null;
        cameraEl.hidden = true;
    }

    function scanWith(token, expiresIn) {
        var detect = createDetector();
        if (!detect) {
            setStatus(copy.no_scanner, 'bad');
            hideAction();
            return;
        }

        navigator.mediaDevices
            .getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            })
            .then(function (mediaStream) {
                stream = mediaStream;
                videoEl.srcObject = mediaStream;
                cameraEl.hidden = false;
                cancelEl.hidden = false;
                setStatus(copy.scan);
                return videoEl.play();
            })
            .then(function () {
                var secondsLeft = expiresIn;
                countdownEl.textContent = fill(copy.seconds_left, {
                    seconds: secondsLeft,
                });
                countdownTimer = setInterval(function () {
                    secondsLeft -= 1;
                    countdownEl.textContent = fill(copy.seconds_left, {
                        seconds: Math.max(0, secondsLeft),
                    });
                    if (secondsLeft <= 0) {
                        showFailure('token_expired');
                    }
                }, 1000);

                var busy = false;
                scanTimer = setInterval(function () {
                    if (busy) {
                        return;
                    }
                    busy = true;
                    detect()
                        .then(function (payload) {
                            if (payload && scanTimer) {
                                stopCamera();
                                submitScan(token, payload);
                            }
                        })
                        .catch(function () {})
                        .then(function () {
                            busy = false;
                        });
                }, 250);
            })
            .catch(function () {
                showFailure('camera_denied');
            });
    }

    function formatTime(isoTime) {
        return new Intl.DateTimeFormat(
            config.locale === 'ms' ? 'ms-MY' : 'en-GB',
            {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: config.timezone || undefined,
            },
        ).format(new Date(isoTime));
    }

    function submitScan(token, payload) {
        setStatus(copy.saving);
        post(config.urls.clock, { token: token, poster: payload })
            .then(function (result) {
                if (!result.ok) {
                    showFailure(result.data.failure);
                    return;
                }

                var clockedIn = result.data.direction === 'in';
                var message = clockedIn
                    ? fill(copy.clocked_in, {
                          time: formatTime(result.data.clock_in_at),
                      })
                    : fill(copy.clocked_out, {
                          time: formatTime(result.data.clock_out_at),
                      });

                if (clockedIn && result.data.late_minutes > 0) {
                    message +=
                        ' ' +
                        fill(copy.late, { minutes: result.data.late_minutes });
                }

                state = clockedIn ? 'out' : 'done';
                cancelEl.hidden = true;
                hideAction();
                setStatus(message, 'ok');
            })
            .catch(function () {
                showFailure('location_unavailable');
            });
    }

    function startClock() {
        if (!window.isSecureContext) {
            showFailure('insecure_context');
            return;
        }

        actionEl.disabled = true;
        setStatus(copy.locating);

        readLocation()
            .then(function (position) {
                setStatus(copy.checking);
                return post(config.urls.geoCheck, {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    showFailure(result.data.failure);
                    return;
                }
                hideAction();
                scanWith(result.data.token, result.data.expires_in);
            })
            .catch(function (failureCode) {
                showFailure(
                    typeof failureCode === 'string'
                        ? failureCode
                        : 'location_unavailable',
                );
            });
    }

    actionEl.addEventListener('click', startClock);
    cancelEl.addEventListener('click', function () {
        setStatus('');
        showIdle();
    });

    showIdle();
})();
