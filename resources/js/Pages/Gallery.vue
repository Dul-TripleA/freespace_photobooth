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
    livePhotos: {
        type: Array,
        default: () => [],
    },
});

// Auto-polling khusus menunggu GIF selesai dirender di background (maksimal 20 kali cek / 60 detik)
let pollInterval = null;
let pollCount = 0;

onMounted(() => {
    if (!props.gifMedia?.url) {
        pollInterval = setInterval(() => {
            pollCount++;
            if (props.gifMedia?.url || pollCount >= 20) {
                clearInterval(pollInterval);
                return;
            }
            router.reload({
                only: ["gifMedia", "livePhotos", "compositePhoto", "rawPhotos"],
                preserveScroll: true,
                preserveState: true,
            });
        }, 3000);
    }
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="Galeri Foto Digital - Freespace Photobooth" />

    <div class="min-h-screen bg-neutral-950 text-white py-10 px-4 sm:px-6">
        <div class="max-w-md mx-auto space-y-8">
            <!-- Header -->
            <div class="text-center space-y-2">
                <span class="px-3 py-1 bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold uppercase tracking-widest rounded-full">
                    FREESPACE PHOTOBOOTH
                </span>
                <h1 class="text-2xl font-extrabold tracking-tight pt-1">
                    Galeri Foto Digital Anda
                </h1>
                <p class="text-xs text-neutral-400 font-mono">
                    Kode Sesi: {{ session?.session_code || session?.id || "-" }}
                </p>
            </div>

            <!-- 1. Hasil Photo Strip (Composite) -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 space-y-4 shadow-xl">
                <h2 class="text-base font-bold flex items-center gap-2">
                    <span>📸</span> Hasil Photo Strip
                </h2>

                <div v-if="compositePhoto && compositePhoto.url" class="space-y-4">
                    <div class="bg-neutral-950 rounded-xl p-2 border border-neutral-800">
                        <img
                            :src="compositePhoto.url"
                            alt="Hasil Photo Strip"
                            class="w-full h-auto rounded-lg mx-auto"
                        />
                    </div>
                    <a
                        :href="compositePhoto.url"
                        :download="`photostrip-${session?.session_code || 'freespace'}.jpg`"
                        class="block w-full py-3 bg-amber-500 hover:bg-amber-400 text-neutral-950 font-extrabold text-center text-sm rounded-xl transition"
                    >
                        ⬇ Unduh Photo Strip
                    </a>
                </div>
                <p v-else class="text-sm text-neutral-500 text-center py-8">
                    Foto strip belum tersedia.
                </p>
            </div>

            <!-- 2. Animasi GIF (Gabungan Semua Capture) - Selalu Ditampilkan -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 space-y-4 shadow-xl">
                <h2 class="text-base font-bold flex items-center gap-2">
                    <span>🎞️</span> Animasi GIF (Gabungan Capture)
                </h2>

                <div v-if="gifMedia && gifMedia.url" class="space-y-4">
                    <div class="bg-neutral-950 rounded-xl p-2 border border-neutral-800">
                        <img
                            :src="gifMedia.url"
                            alt="Animasi GIF"
                            class="w-full h-auto rounded-lg mx-auto"
                        />
                    </div>
                    <a
                        :href="gifMedia.url"
                        :download="`animasi-gif-${session?.session_code || 'freespace'}.gif`"
                        class="block w-full py-3 bg-amber-500 hover:bg-amber-400 text-neutral-950 font-extrabold text-center text-sm rounded-xl transition"
                    >
                        ⬇ Unduh Animasi GIF
                    </a>
                </div>

                <!-- Indikator Loading saat GIF sedang dirender oleh FFmpeg -->
                <div
                    v-else
                    class="border border-dashed border-neutral-800 rounded-xl p-8 text-center space-y-3"
                >
                    <div class="w-8 h-8 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-sm font-bold text-white">Sedang Merender GIF...</p>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Harap tunggu beberapa detik, halaman ini akan menampilkan GIF gabungan semua pose secara otomatis setelah siap.
                    </p>
                </div>
            </div>

            <!-- 3. Live Photo Bergerak (Video tiap Pose) -->
            <div
                v-if="livePhotos && livePhotos.length > 0"
                class="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 space-y-4 shadow-xl"
            >
                <h2 class="text-base font-bold flex items-center gap-2">
                    <span>✨</span> Live Photo per Pose ({{ livePhotos.length }} Video)
                </h2>

                <div class="grid grid-cols-1 gap-4">
                    <div
                        v-for="(live, idx) in livePhotos"
                        :key="live?.id || idx"
                        class="bg-neutral-950 border border-neutral-800 rounded-xl p-3 space-y-3"
                    >
                        <video
                            v-if="live && live.url"
                            :src="live.url"
                            autoplay
                            loop
                            muted
                            playsinline
                            class="w-full rounded-lg object-cover bg-black"
                        ></video>
                        <a
                            v-if="live && live.url"
                            :href="live.url"
                            :download="`live-pose-${live.pose_number || idx + 1}.webm`"
                            class="block w-full py-2 bg-neutral-800 hover:bg-neutral-700 text-amber-400 text-xs font-bold text-center rounded-lg transition"
                        >
                            ⬇ Unduh Live Photo #{{ live.pose_number || idx + 1 }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Foto Satuan (Raw Poses) -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-5 space-y-4 shadow-xl">
                <h2 class="text-base font-bold flex items-center gap-2">
                    <span>🖼️</span> Foto Satuan ({{ rawPhotos?.length || 0 }} Pose)
                </h2>

                <div v-if="rawPhotos && rawPhotos.length > 0" class="grid grid-cols-2 gap-3">
                    <div
                        v-for="(photo, index) in rawPhotos"
                        :key="photo?.id || index"
                        class="bg-neutral-950 border border-neutral-800 rounded-xl p-2 flex flex-col justify-between"
                    >
                        <img
                            v-if="photo && photo.url"
                            :src="photo.url"
                            :alt="`Pose ${index + 1}`"
                            class="w-full aspect-[4/3] object-cover rounded-lg mb-2"
                        />
                        <a
                            v-if="photo && photo.url"
                            :href="photo.url"
                            :download="`pose-${index + 1}.jpg`"
                            class="w-full py-1.5 bg-neutral-800 hover:bg-neutral-700 text-xs font-bold text-center text-neutral-200 rounded-lg transition"
                        >
                            Unduh Pose #{{ index + 1 }}
                        </a>
                    </div>
                </div>
                <p v-else class="text-sm text-neutral-500 text-center py-6">
                    Belum ada foto satuan.
                </p>
            </div>
        </div>
    </div>
</template>