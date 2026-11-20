/*
 * QR decoder for browsers without BarcodeDetector (Safari on iPhone and iPad).
 * The clock page loads this only when it needs it.
 */
import jsQR from 'jsqr';

declare global {
    interface Window {
        jsQR?: typeof jsQR;
    }
}

window.jsQR = jsQR;
