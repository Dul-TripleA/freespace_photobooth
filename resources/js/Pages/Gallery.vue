<script setup>
import { Head } from "@inertiajs/vue3";

import { Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted } from "vue";

const props = defineProps({
    session: Object,
    compositePhoto: Object,
    rawPhotos: Array,
});

// Cari media bertipe GIF
const gifAnimation = props.session.media.find(
    (m) => m.type === "gif_animation",
);

// Auto-refresh halaman setiap 3 detik jika GIF belum selesai diproses oleh FFmpeg
let refreshInterval;
onMounted(() => {
    if (!gifAnimation) {
        refreshInterval = setInterval(() => {
            router.reload({ only: ["session"] });
        }, 3000);
    }
});
onUnmounted(() => clearInterval(refreshInterval));

const props = defineProps({
    session: Object,
    compositePhoto: Object,
    rawPhotos: Array,
});

// Fungsi untuk memaksa browser mengunduh gambar (bukan sekadar membuka tab baru)
const downloadImage = async (url, filename) => {
    try {
        const response = await fetch(url);
        const blob = await response.blob();
        const blobUrl = window.URL.createObjectURL(blob);

        const link = document.createElement("a");
        link.href = blobUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(blobUrl);
    } catch (error) {
        console.error("Gagal mengunduh gambar", error);
        // Fallback jika fetch gagal (misal masalah CORS di server produksi)
        window.open(url, "_blank");
    }
};
</script>

<template>
    <Head title="Unduh Foto Anda - SelfBooth" />
    <!-- Tampilan disesuaikan untuk layar HP (Mobile First) -->
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans pb-12">
        <!-- Header Minimalis -->
        <header
            class="bg-white shadow-sm py-4 px-6 text-center sticky top-0 z-10"
        >
            <h1 class="text-xl font-black text-blue-600 tracking-wider">
                SELFBOOTH
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                ID Sesi: {{ session.session_code }}
            </p>
        </header>

        <!-- Area GIF Animasi -->
        <div
            class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8 p-6 text-center"
        >
            <h2 class="font-bold text-lg mb-2">Boomerang / GIF</h2>

            <div v-if="gifAnimation" class="flex flex-col items-center">
                <img
                    :src="gifAnimation.file_path"
                    class="w-full max-w-[300px] rounded-xl mb-4 shadow-inner"
                />
                <button
                    @click="
                        downloadImage(
                            gifAnimation.file_path,
                            `SelfBooth-Anim-${session.session_code}.gif`,
                        )
                    "
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl flex justify-center items-center space-x-2 transition-colors"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                        ></path>
                    </svg>
                    <span>Unduh GIF</span>
                </button>
            </div>

            <div
                v-else
                class="py-8 flex flex-col items-center justify-center text-gray-400"
            >
                <svg
                    class="w-8 h-8 animate-spin mb-3 text-blue-500"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <p class="text-sm">Sedang merender video...</p>
            </div>
        </div>

        <main class="max-w-md mx-auto px-4 mt-6">
            <div
                class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 mb-8"
            >
                <!-- Gambar Utama (Komposit) -->
                <div
                    class="bg-gray-200 aspect-[2/3] w-full flex items-center justify-center p-4"
                >
                    <img
                        v-if="compositePhoto"
                        :src="compositePhoto.file_path"
                        class="max-w-full max-h-full object-contain drop-shadow-md"
                    />
                </div>

                <!-- Tombol Unduh Utama -->
                <div class="p-6">
                    <h2 class="font-bold text-lg mb-2 text-center">
                        Foto Utama Anda
                    </h2>
                    <p class="text-sm text-gray-500 text-center mb-6">
                        Ketuk tombol di bawah untuk menyimpan foto dengan
                        resolusi tinggi.
                    </p>

                    <button
                        v-if="compositePhoto"
                        @click="
                            downloadImage(
                                compositePhoto.file_path,
                                `SelfBooth-${session.session_code}.jpg`,
                            )
                        "
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl flex justify-center items-center space-x-2 transition-colors active:scale-95 shadow-md"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            ></path>
                        </svg>
                        <span>Unduh Foto (HD)</span>
                    </button>
                </div>
            </div>

            <!-- Bagian Foto Mentah (Raw Poses) -->
            <div v-if="rawPhotos && rawPhotos.length > 0">
                <h3 class="font-bold text-gray-800 mb-4 px-2">
                    Foto Tiap Pose (Original)
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div
                        v-for="photo in rawPhotos"
                        :key="photo.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 flex flex-col"
                    >
                        <img
                            :src="photo.file_path"
                            class="w-full aspect-[3/4] object-cover rounded-lg mb-3 bg-gray-100"
                        />
                        <button
                            @click="
                                downloadImage(
                                    photo.file_path,
                                    `SelfBooth-Pose-${photo.pose_number}.jpg`,
                                )
                            "
                            class="w-full mt-auto bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 rounded-lg text-sm transition-colors flex justify-center items-center space-x-1"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                ></path>
                            </svg>
                            <span>Pose {{ photo.pose_number }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
