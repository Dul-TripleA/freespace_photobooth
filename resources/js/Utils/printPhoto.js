// resources/js/Utils/printPhoto.js

export const printCompositePhoto = (imageUrl, copies = 1) => {
    return new Promise((resolve, reject) => {
        // Hapus iframe print sebelumnya jika masih ada
        const existingIframe = document.getElementById('silent-print-frame');
        if (existingIframe) {
            document.body.removeChild(existingIframe);
        }

        // Buat iframe tersembunyi
        const iframe = document.createElement('iframe');
        iframe.id = 'silent-print-frame';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;

        // Buat halaman cetak sebanyak jumlah copies yang diminta
        let imagesHtml = '';
        for (let i = 0; i < copies; i++) {
            imagesHtml += `
                <div class="print-page">
                    <img src="${imageUrl}" onload="window.checkLoaded()" onerror="window.handleError()" />
                </div>
            `;
        }

        // Tulis struktur HTML & CSS khusus ukuran kertas 4R (4x6 inch) tanpa margin
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Photobooth</title>
                <style>
                    @page {
                        size: 4in 6in; /* Standar kertas 4R mesin cetak photobooth */
                        margin: 0mm;   /* Wajib 0 agar Full-Bleed / Borderless */
                    }
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    html, body {
                        width: 100%;
                        height: 100%;
                        background: #000;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .print-page {
                        width: 100vw;
                        height: 100vh;
                        page-break-after: always;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        overflow: hidden;
                    }
                    .print-page:last-child {
                        page-break-after: auto;
                    }
                    img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        display: block;
                    }
                </style>
            </head>
            <body>
                ${imagesHtml}
                <script>
                    let loadedCount = 0;
                    const totalImages = ${copies};

                    window.checkLoaded = function() {
                        loadedCount++;
                        if (loadedCount === totalImages) {
                            setTimeout(() => {
                                window.focus();
                                window.print();
                                window.parent.postMessage('print-triggered', '*');
                            }, 300);
                        }
                    };

                    window.handleError = function() {
                        window.parent.postMessage('print-error', '*');
                    };
                <\/script>
            </body>
            </html>
        `);
        doc.close();

        // Dengarkan sinyal selesai dari iframe
        const messageHandler = (event) => {
            if (event.data === 'print-triggered') {
                window.removeEventListener('message', messageHandler);
                resolve(true);
            } else if (event.data === 'print-error') {
                window.removeEventListener('message', messageHandler);
                reject(new Error('Gagal memuat gambar untuk dicetak.'));
            }
        };

        window.addEventListener('message', messageHandler);
    });
};