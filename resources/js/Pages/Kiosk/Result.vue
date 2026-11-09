<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    session: Object,
    compositePhoto: String,
    livePhotos: {
        type: Array,
        default: () => [],
    },
    galleryUrl: String,
    gifMedia: Object,
});

// Parse slots_config dari frame jika ada
const slotsConfig = computed(() => {
    if (!props.session?.frame?.slots_config) return [];
    return typeof props.session.frame.slots_config === "string"
        ? JSON.parse(props.session.frame.slots_config)
        : props.session.frame.slots_config;
});

const canvasWidth = computed(() => props.session?.frame?.canvas_width || 1200);
const canvasHeight = computed(() => props.session?.frame?.canvas_height || 1800);
const overlayPath = computed(() => props.session?.frame?.overlay_path || null);

// URL QR Code otomatis berdasarkan galleryUrl
const qrCodeSrc = computed(() => {
    const url = props.galleryUrl || window.location.origin;
    return `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(url)}`;
});
</script>

<template>

    <Head title="Hasil Photobooth - Kiosk" />

    <div
        class="min-h-screen bg-neutral-950 text-white px-4 py-6 sm:px-6 lg:px-8 flex flex-col items-center justify-center relative overflow-hidden">

        <!-- Background Glow -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <!-- Subtle Grid -->
        <div class="absolute inset-0 opacity-[0.025] pointer-events-none" style="
                background-image:
                    linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
                background-size: 40px 40px;
            "></div>

        <div class="relative z-10 max-w-7xl w-full space-y-7">

            <!-- HEADER -->
            <div class="text-center space-y-3">

                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-400 text-[11px] font-bold uppercase tracking-[0.2em]">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Photobooth Complete
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                    Sesi Berhasil Disimpan!
                    <span class="inline-block ml-1">🎉</span>
                </h1>

                <p class="text-neutral-400 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                    Foto Anda sudah siap. Scan QR Code untuk membuka galeri digital
                    dan mengunduh semua foto serta Live Photo ke smartphone Anda.
                </p>

            </div>


            <!-- MAIN CARD -->
            <div
                class="relative bg-neutral-900/80 backdrop-blur-xl border border-white/10 rounded-[2rem] p-4 sm:p-6 lg:p-8 shadow-2xl">

                <!-- Top accent -->
                <div
                    class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-[2px] bg-gradient-to-r from-transparent via-amber-400 to-transparent">
                </div>


                <!-- MAIN GRID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6 items-stretch">

                    <!-- ===================================== -->
                    <!-- 1. PHOTO STRIP -->
                    <!-- ===================================== -->
                    <div
                        class="group relative flex flex-col bg-neutral-950/70 border border-white/10 rounded-3xl p-5 overflow-hidden">

                        <!-- Glow -->
                        <div
                            class="absolute -top-20 -right-20 w-40 h-40 bg-amber-400/10 rounded-full blur-3xl pointer-events-none">
                        </div>

                        <div class="relative flex items-center justify-between mb-4">

                            <div>
                                <p class="text-[10px] uppercase tracking-[0.2em] text-neutral-500 font-bold">
                                    Your Result
                                </p>

                                <h2 class="text-lg font-extrabold text-white">
                                    Photo Strip
                                </h2>
                            </div>

                            <div
                                class="w-9 h-9 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 19h16" />
                                </svg>
                            </div>

                        </div>


                        <!-- Image Container -->
                        <div
                            class="relative flex-1 min-h-[360px] sm:min-h-[430px] lg:min-h-[500px] bg-black/70 rounded-2xl border border-white/5 overflow-hidden flex items-center justify-center p-4">

                            <div
                                class="absolute inset-0 bg-gradient-to-br from-amber-400/[0.03] via-transparent to-orange-400/[0.03]">
                            </div>

                            <img v-if="compositePhoto" :src="compositePhoto" alt="Composite Result"
                                class="relative max-h-[470px] max-w-full w-auto object-contain rounded-xl shadow-2xl transition duration-500 group-hover:scale-[1.015]" />

                            <div v-else class="flex flex-col items-center justify-center gap-3 text-neutral-500">
                                <div
                                    class="w-10 h-10 border-2 border-neutral-700 border-t-amber-400 rounded-full animate-spin">
                                </div>

                                <span class="text-xs">
                                    Memproses foto...
                                </span>
                            </div>

                        </div>

                    </div>


                    <!-- ===================================== -->
                    <!-- 2. LIVE PHOTO -->
                    <!-- ===================================== -->
                    <div class="flex flex-col bg-neutral-950/70 border border-white/10 rounded-3xl p-5">
                        <div class="flex items-center justify-between mb-4">

                            <div>
                                <p class="text-[10px] uppercase tracking-[0.2em] text-neutral-500 font-bold">
                                    Animated Preview
                                </p>

                                <h2 class="text-lg font-extrabold text-white">
                                    Live Photo
                                </h2>
                            </div>

                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-red-500/10 border border-red-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>

                                <span class="text-[9px] font-bold uppercase tracking-wider text-red-400">
                                    Live
                                </span>
                            </div>

                        </div>


                        <!-- Frame Preview -->
                        <div
                            class="flex-1 flex items-center justify-center bg-black/60 rounded-2xl border border-white/5 p-6 relative overflow-hidden">

                            <!-- Background Glow -->
                            <div
                                class="absolute inset-0 bg-gradient-to-b from-amber-400/[0.04] via-transparent to-orange-400/[0.04]">
                            </div>


                            <!-- Frame -->
                            <div class="relative w-full max-w-[280px] bg-neutral-950 border border-white/10 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/5"
                                :style="{
                                    aspectRatio: `${canvasWidth} / ${canvasHeight}`,
                                }">

                                <!-- Live Photo Slots -->
                                <template v-for="slot in slotsConfig" :key="slot.pose">

                                    <div v-if="livePhotos.find(l => l.pose_number === slot.pose)"
                                        class="absolute overflow-hidden bg-black" :style="{
                                            left: `${(slot.x / canvasWidth) * 100}%`,
                                            top: `${(slot.y / canvasHeight) * 100}%`,
                                            width: `${(slot.width / canvasWidth) * 100}%`,
                                            height: `${(slot.height / canvasHeight) * 100}%`,
                                        }">

                                        <video :src="livePhotos.find(l => l.pose_number === slot.pose)?.file_path"
                                            autoplay loop muted playsinline class="w-full h-full object-cover"></video>

                                    </div>

                                </template>


                                <!-- Overlay Frame -->
                                <img v-if="overlayPath" :src="overlayPath" alt="Frame Overlay"
                                    class="absolute inset-0 w-full h-full object-contain pointer-events-none z-20" />

                            </div>

                        </div>


                        <div class="mt-4 text-center text-[11px] text-neutral-500">
                            Preview Live Photo dalam frame Anda
                        </div>

                    </div>


                    <!-- ===================================== -->
                    <!-- 3. QR CODE -->
                    <!-- ===================================== -->
                    <div
                        class="relative flex flex-col justify-between bg-gradient-to-b from-neutral-800/80 to-neutral-950/80 border border-amber-400/20 rounded-3xl p-6 overflow-hidden">

                        <!-- Glow -->
                        <div
                            class="absolute -top-24 -right-24 w-52 h-52 bg-amber-400/10 rounded-full blur-3xl pointer-events-none">
                        </div>

                        <div class="relative">

                            <div class="flex items-center justify-between">

                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.2em] text-amber-400/70 font-bold">
                                        Digital Gallery
                                    </p>

                                    <h2 class="text-xl font-extrabold text-white mt-1">
                                        Scan & Download
                                    </h2>
                                </div>

                                <div
                                    class="w-10 h-10 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center text-amber-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 4h4v4H3V4zm0 12h4v4H3v-4zm12-12h6v6h-6V4zM9 4h3v3H9V4zm0 6h3v3H9v-3zm3 3h3v3h-3v-3zm3 3h3v4h-3v-4z" />
                                    </svg>
                                </div>

                            </div>


                            <!-- QR -->
                            <div class="mt-7 flex justify-center">
                                <div class="relative p-4 bg-white rounded-3xl shadow-[0_0_50px_rgba(251,191,36,0.12)]">

                                    <div class="absolute inset-0 rounded-3xl ring-1 ring-white/20"></div>

                                    <img :src="qrCodeSrc" alt="QR Code Galeri"
                                        class="relative w-44 h-44 sm:w-48 sm:h-48 object-contain" />

                                </div>
                            </div>


                            <div class="text-center mt-6 space-y-2">

                                <p class="text-sm font-bold text-white">
                                    Scan QR Code
                                </p>

                                <p class="text-xs text-neutral-400 leading-relaxed max-w-[240px] mx-auto">
                                    Arahkan kamera smartphone Anda ke QR Code
                                    untuk membuka galeri digital.
                                </p>

                            </div>

                        </div>


                        <!-- Bottom CTA -->
                        <div class="relative mt-8">

                            <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent mb-5"></div>

                            <Link href="/kiosk"
                                class="group w-full py-3.5 px-4 bg-amber-400 hover:bg-amber-300 active:bg-amber-500 text-neutral-950 font-black text-sm text-center rounded-xl transition-all duration-200 shadow-lg shadow-amber-500/10 hover:shadow-amber-400/20 flex items-center justify-center gap-2">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>

                                Mulai Sesi Baru

                            </Link>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FOOTER -->
            <div class="text-center text-[10px] text-neutral-600 uppercase tracking-[0.2em]">
                Thank you for using our photobooth
            </div>

        </div>
    </div>
</template>