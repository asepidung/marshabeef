// Kunci otomatis: tanpa klik/sentuh/keyboard selama N menit, kembali ke dashboard untuk PIN ulang.
const { idleMinutes, lockUrl, keepaliveUrl } = document.body.dataset;
const limit = Number(idleMinutes) * 60 * 1000;

if (limit > 0 && lockUrl) {
    const PING_EVERY = 5 * 60 * 1000;
    let lastActivity = Date.now();
    let lastPing = lastActivity;

    const onActivity = () => {
        const now = Date.now();
        lastActivity = now;

        // Beri tahu server bahwa pengguna masih aktif agar sesi server tidak kedaluwarsa lebih dulu.
        if (keepaliveUrl && now - lastPing > PING_EVERY) {
            lastPing = now;
            fetch(keepaliveUrl, { credentials: 'same-origin' }).catch(() => {});
        }
    };

    ['pointerdown', 'keydown', 'touchstart'].forEach((type) =>
        window.addEventListener(type, onActivity, { passive: true }),
    );

    // Pakai pembanding waktu (bukan setTimeout) supaya tetap akurat setelah PC tidur/standby.
    const check = () => {
        if (Date.now() - lastActivity >= limit) {
            window.location.href = lockUrl;
        }
    };

    setInterval(check, 15 * 1000);
    document.addEventListener('visibilitychange', () => document.visibilityState === 'visible' && check());
}
