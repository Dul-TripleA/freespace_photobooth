<script setup>
import { Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted } from "vue";

const props = defineProps({
    session: {
        type: Object,
        default: () => ({}),
    },
    compositePhoto: {
        type: Object,
        default: null,
    },
    gifMedia: {
        type: Object,
        default: null,
    },
    rawPhotos: {
        type: Array,
        default: () => [],
    },
});

let refreshInterval = null;

onMounted(() => {
    // Jika GIF belum selesai dirender oleh FFmpeg, cek ulang setiap 3 detik
    if (!props.gifMedia) {
        refreshInterval = setInterval(() => {
            router.reload({
                only: ["gifMedia"],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    if (page.props.gifMedia && refreshInterval) {
                        clearInterval(refreshInterval);
                    }
                },
            });
        }, 3000);
    }
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <Head title="Digital Gallery - SelfBooth" />

    <div class="min-h-screen bg-neutral-950 text-white py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto space-y-10">
            <!-- Header -->
            <div class="text-center space-y-2">
                <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wider uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">
                    Freespace Photobooth
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Galeri Foto Digital Anda
                </h1>
                <p v-if="session?.session_code" class="text-sm text-neutral-400">
                    Kode Sesi: <span class="font-mono text-neutral-200">{{ session.session_code }}</span>
                </p>
            </div>

            <!-- Bagian 1 & 2: Hasil Strip Foto (Composite) & Animasi GIF -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <!-- Strip Foto Komposit -->
                <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 flex flex-col items-center shadow-xl">
                    <h2 class="text-lg font-bold mb-4 self-start flex items-center gap-2">
                        <span>📸</span> Hasil Photo Strip
                    </h2>

                    <div v-if="compositePhoto?.url" class="w-full flex flex-col items-center">
                        <img
                            :src="compositePhoto.url"
                            alt="Hasil Photo Strip"
                            class="max-h-[480px] w-auto rounded-lg shadow-md object-contain mb-5 border border-neutral-800"
                        />
                        <a
                            :href="compositePhoto.url"
                            target="_blank"
                            download="photostrip.png"
                            class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-400 text-neutral-950 font-bold text-center rounded-xl transition duration-200 shadow-lg"
                        >
                            Unduh Photo Strip
                        </a>
                    </div>
                    <div v-else class="py-16 text-center text-neutral-500 text-sm">
                        Foto strip belum tersedia.
                    </div>
                </div>

                <!-- Animasi GIF -->
                <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 flex flex-col items-center shadow-xl">
                    <h2 class="text-lg font-bold mb-4 self-start flex items-center gap-2">
                        <span>🎞️</span> Animasi GIF
                    </h2>

                    <!-- Jika GIF Sudah Selesai -->
                    <div v-if="gifMedia?.url" class="w-full flex flex-col items-center">
                        <img
                            :src="gifMedia.url"
                            alt="Animasi GIF"
                            class="w-full rounded-lg shadow-md object-cover mb-5 border border-neutral-800"
                        />
                        <a
                            :href="gifMedia.url"
                            target="_blank"
                            download="animation.gif"
                            class="w-full py-3 px-4 bg-white hover:bg-neutral-200 text-neutral-950 font-bold text-center rounded-xl transition duration-200 shadow-lg"
                        >
                            Unduh Animasi GIF
                        </a>
                    </div>

                    <!-- Jika GIF Masih Diproses Queue Worker -->
                    <div v-else class="w-full py-16 px-4 flex flex-col items-center justify-center text-center border border-dashed border-neutral-800 rounded-xl bg-neutral-950/50">
                        <div class="w-10 h-10 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="font-semibold text-neutral-200">Sedang Merender GIF...</p>
                        <p class="text-xs text-neutral-400 mt-1 max-w-xs">
                            Harap tunggu beberapa detik, halaman ini akan menampilkan GIF secara otomatis setelah siap.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Daftar Foto Mentah (6 Pose) -->
            <div v-if="rawPhotos && rawPhotos.length > 0" class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span>🖼️</span> Foto Satuan ({{ rawPhotos.length }} Pose)
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div
                        v-for="(photo, index) in rawPhotos"
                        :key="photo.id || index"
                        class="group relative bg-neutral-950 rounded-xl overflow-hidden border border-neutral-800 flex flex-col"
                    >
                        <img
                            :src="photo.url"
                            :alt="`Pose ${index + 1}`"
                            class="w-full aspect-[4/3] object-cover"
                        />
                        <div class="p-2.5 flex items-center justify-between bg-neutral-900">
                            <span class="text-xs font-medium text-neutral-400">
                                Pose #{{ index + 1 }}
                            </span>
                            <a
                                :href="photo.url"
                                target="_blank"
                                :download="`pose-${index + 1}.jpg`"
                                class="text-xs font-bold text-amber-400 hover:text-amber-300"
                            >
                                Unduh ↓
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-neutral-500 pt-4">
                <p>Tips: Pada iPhone/Safari, tekan dan tahan gambar lalu pilih <strong>"Simpan ke Foto"</strong>.</p>
            </div>
        </div>
    </div>
</template>