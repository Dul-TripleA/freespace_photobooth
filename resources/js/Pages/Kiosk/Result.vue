<script setup>
import { Head, router } from "@inertiajs/vue3";
import QrcodeVue from "qrcode.vue";
import { ref, computed } from "vue";

const props = defineProps({
    session: Object,
    compositePhoto: Object,
});

const galleryUrl = computed(() =>
    route("gallery.show", { token: props.session.gallery_token }),
);

const isPrinting = ref(false);

const printPhoto = () => {
    isPrinting.value = true;

    window.print();

    setTimeout(() => {
        isPrinting.value = false;
    }, 3000);
};

const finishSession = () => {
    router.visit(route("kiosk.home"));
};
</script>

<template>
    <Head title="Hasil Sesi & Cetak" />

    <!-- 1. GAMBAR KHUSUS CETAK (Tersembunyi di layar, hanya muncul saat Print) -->
    <img id="print-only-image" :src="compositePhoto.file_path" />

    <!-- Kunci tinggi layar tepat 100vh dengan h-screen dan overflow-hidden (Tanpa Scroll) -->
    <div
        id="screen-ui"
        class="h-screen w-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gray-800 via-gray-950 to-black text-white flex flex-col font-sans p-4 md:p-8 overflow-hidden select-none relative"
    >
        <!-- Ornamen Cahaya Dekoratif -->
        <div
            class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"
        ></div>
        <div
            class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-emerald-600/20 rounded-full blur-[120px] pointer-events-none"
        ></div>

        <!-- Header (Dibuat lebih ringkas agar hemat ruang vertikal) -->
        <div class="text-center mb-4 shrink-0 relative z-10">
            <h1
                class="text-2xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400 tracking-tight mb-1 drop-shadow-lg"
            >
                Sesi Foto Selesai!
            </h1>
            <p class="text-gray-300 text-sm md:text-base font-medium">
                Pindai QR Code untuk mengunduh foto & video Anda.
            </p>
        </div>

        <!-- Kontainer Utama (min-h-0 wajib ada agar tidak meluap) -->
        <div
            class="flex-1 max-w-6xl w-full mx-auto bg-white/5 backdrop-blur-xl border border-white/10 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] p-6 md:p-8 flex flex-col lg:flex-row gap-8 items-center justify-center relative z-10 min-h-0 overflow-hidden"
        >
            <!-- Panel Kiri: Area Preview Cetak -->
            <div
                class="flex-1 w-full h-full flex flex-col items-center justify-center bg-black/40 rounded-2xl p-6 border border-white/5 shadow-inner min-h-0 overflow-hidden relative"
            >
                <span
                    class="shrink-0 mb-3 bg-gray-900 text-gray-300 text-[10px] font-bold px-4 py-1 rounded-full border border-gray-700 tracking-widest uppercase print:hidden shadow-lg z-10"
                >
                    Preview Cetak
                </span>

                <!-- Wrapper Pembatas Gambar -->
                <div
                    class="relative flex-1 w-full min-h-0 flex items-center justify-center"
                >
                    <img
                        id="print-area"
                        :src="compositePhoto.file_path"
                        class="max-h-full max-w-full w-auto h-auto object-contain rounded-xl shadow-2xl border border-white/10 bg-gray-900"
                    />
                </div>
            </div>

            <!-- Panel Kanan: QR Code & Tombol Aksi -->
            <div
                class="w-full lg:w-[340px] h-full flex flex-col items-center justify-center shrink-0 min-h-0"
            >
                <!-- Kotak QR Code -->
                <div
                    class="bg-gradient-to-b from-white to-gray-200 p-5 md:p-6 rounded-3xl shadow-[0_0_40px_rgba(255,255,255,0.15)] flex flex-col items-center w-full"
                >
                    <div
                        class="bg-white p-2.5 rounded-2xl shadow-sm mb-3 border border-gray-100"
                    >
                        <qrcode-vue :value="galleryUrl" :size="160" level="H" />
                    </div>
                    <p
                        class="text-gray-800 font-bold text-base text-center leading-tight"
                    >
                        Scan untuk Download (HD)
                    </p>
                    <div
                        class="mt-3 bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-300 w-full text-center"
                    >
                        <p
                            class="text-[10px] text-gray-500 font-mono uppercase tracking-wider"
                        >
                            ID SESI:
                            <span class="font-bold text-gray-800">{{
                                session.session_code
                            }}</span>
                        </p>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col w-full gap-3 mt-5 shrink-0">
                    <button
                        @click="printPhoto"
                        :disabled="isPrinting"
                        class="group relative bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-3.5 rounded-xl font-bold text-base transition-all shadow-[0_10px_20px_rgba(79,70,229,0.3)] disabled:opacity-50 flex justify-center items-center space-x-2 overflow-hidden"
                    >
                        <svg
                            class="w-5 h-5 relative z-10"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            ></path>
                        </svg>
                        <span class="relative z-10">{{
                            isPrinting
                                ? "Mengirim ke Printer..."
                                : "Cetak Foto Sekarang"
                        }}</span>
                    </button>

                    <button
                        @click="finishSession"
                        class="bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white py-3.5 rounded-xl font-bold text-base transition-all border border-white/10 backdrop-blur-sm"
                    >
                        Selesai & Kembali
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Sembunyikan gambar cetak saat di layar normal */
#print-only-image {
    display: none;
}

/* CSS HANYA aktif saat window.print() dipanggil */
@media print {
    /* Kunci ukuran kertas tepat 4x6 inci (4R) tanpa margin */
    @page {
        size: 4in 6in;
        margin: 0mm !important;
    }

    html,
    body {
        width: 4in !important;
        height: 6in !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Matikan seluruh UI Kiosk beserta efek kacanya agar tidak mengganggu koordinat kertas */
    #screen-ui {
        display: none !important;
    }

    /* Tampilkan gambar khusus cetak tepat dari titik 0,0 memenuhi kertas 4R */
    #print-only-image {
        display: block !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 4in !important;
        height: 6in !important;
        max-width: none !important;
        max-height: none !important;
        object-fit: cover !important; /* Gunakan 'contain' jika tidak ingin ada bagian tepi yang terpotong sedikitpun */
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }
}
</style>
