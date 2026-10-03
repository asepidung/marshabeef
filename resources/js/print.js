import JsBarcode from 'jsbarcode';

const svg = document.getElementById('barcode');

JsBarcode(svg, svg.dataset.value, {
    format: 'CODE128',
    width: 2.2,
    height: 38,
    displayValue: false,
    margin: 0,
});

window.addEventListener('load', () => {
    window.print();
    setTimeout(() => window.close(), 500);
});
