// Input bertanda data-uppercase otomatis diubah ke huruf besar saat diketik (posisi kursor dipertahankan).
document.addEventListener('input', (event) => {
    const field = event.target;

    if (!(field instanceof HTMLInputElement) || !field.hasAttribute('data-uppercase')) {
        return;
    }

    const upper = field.value.toUpperCase();

    if (upper === field.value) {
        return;
    }

    const { selectionStart, selectionEnd } = field;
    field.value = upper;
    field.setSelectionRange(selectionStart, selectionEnd);
    // Beri tahu Alpine (x-model) bahwa nilainya berubah.
    field.dispatchEvent(new Event('input', { bubbles: true }));
});
