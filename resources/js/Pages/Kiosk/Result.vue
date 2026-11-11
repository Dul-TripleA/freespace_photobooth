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
        class="fixed inset-0 z-0 h-screen h-[100dvh] w-screen overflow-hidden bg-[#050505] px-2 py-2 font-sans text-white sm:px-4 sm:py-3 lg:px-6 lg:py-4">
        <!-- Editorial background -->
        <div class="pointer-events-none absolute inset-0 opacity-[0.035]"
            style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 100px 100px;">
        </div>
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_50%_0%,rgba(255,255,255,0.07),transparent_55%)]">
        </div>

        <div class="relative z-10 mx-auto flex h-full min-h-0 w-full max-w-[1600px] flex-col gap-2 sm:gap-3 lg:gap-4">
            <!-- Top navigation -->
            <header class="flex shrink-0 items-center justify-between border-b border-neutral-800 pb-2 sm:pb-2.5">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex items-center gap-1.5" aria-hidden="true">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                    </div>
                    <span
                        class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white sm:text-xs sm:tracking-[0.28em]">Procold
                        Studio</span>
                </div>

                <div
                    class="flex items-center gap-2 text-[8px] uppercase tracking-[0.16em] text-neutral-500 sm:gap-3 sm:text-[9px] sm:tracking-[0.2em]">
                    <span class="hidden sm:inline">Digital Experience</span>
                    <span class="h-4 w-px bg-neutral-700"></span>
                    <span class="inline-flex items-center gap-2 text-neutral-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                        Complete
                    </span>
                </div>
            </header>

            <!-- Editorial hero -->
            <section
                class="grid shrink-0 grid-cols-[1fr_auto] items-end gap-2 border-b border-neutral-800 pb-2 sm:gap-4 sm:pb-3 lg:gap-8">
                <div>
                    <p
                        class="mb-1 font-mono text-[8px] italic uppercase tracking-[0.16em] text-neutral-500 sm:text-[9px] sm:tracking-[0.22em] lg:text-[10px]">
                        Exclusive Selfbooth Studio / Digital Delivery
                    </p>
                    <h1
                        class="max-w-4xl text-[clamp(1.65rem,5.2vw,5.4rem)] font-medium uppercase leading-[0.88] tracking-[-0.065em]">
                        Your session<br class="hidden sm:block" /> is ready<span class="text-neutral-500">.</span>
                    </h1>
                    <p
                        class="mt-2 hidden max-w-2xl text-[10px] leading-relaxed text-neutral-400 sm:block sm:text-xs lg:mt-3 lg:text-sm">
                        Foto Anda sudah siap. Scan QR Code untuk membuka galeri digital dan mengunduh semua foto serta
                        Live Photo ke smartphone Anda.
                    </p>
                </div>

                <div class="flex flex-col items-end justify-end gap-2 sm:gap-3">
                    <div class="text-right">
                        <p
                            class="font-mono text-[7px] uppercase tracking-[0.16em] text-neutral-600 sm:text-[8px] sm:tracking-[0.2em]">
                            Status</p>
                        <p
                            class="mt-0.5 text-[8px] uppercase tracking-[0.1em] text-white sm:text-[9px] sm:tracking-[0.16em]">
                            Photobooth complete</p>
                    </div>
                    <div class="text-right">
                        <p
                            class="font-mono text-[7px] uppercase tracking-[0.16em] text-neutral-600 sm:text-[8px] sm:tracking-[0.2em]">
                            Output</p>
                        <p
                            class="mt-0.5 text-[8px] uppercase tracking-[0.08em] text-neutral-300 sm:text-[9px] sm:tracking-[0.12em]">
                            Photo / Motion / Gallery</p>
                    </div>
                </div>
            </section>

            <!-- Main editorial grid -->
            <main
                class="grid min-h-0 flex-1 grid-cols-[minmax(0,1fr)_minmax(0,1fr)] grid-rows-2 items-stretch gap-2 overflow-hidden sm:gap-3 xl:grid-cols-12 xl:grid-rows-1 xl:gap-4">
                <!-- 01. Photo strip -->
                <section
                    class="group relative row-span-2 flex min-h-0 min-w-0 flex-col overflow-hidden border border-neutral-800 bg-[#090909] p-2 sm:p-3 xl:col-span-5 xl:row-span-1 xl:p-4">
                    <div
                        class="mb-2 flex shrink-0 items-end justify-between gap-2 border-b border-neutral-800 pb-2 sm:mb-3 sm:pb-2.5">
                        <div>
                            <p
                                class="font-mono text-[7px] uppercase tracking-[0.14em] text-neutral-500 sm:text-[8px] sm:tracking-[0.22em]">
                                01 / Your result</p>
                            <h2 class="mt-0.5 text-sm font-medium uppercase tracking-[-0.04em] sm:text-lg xl:text-xl">
                                Photo strip</h2>
                        </div>
                        <span
                            class="hidden font-mono text-[8px] uppercase tracking-[0.12em] text-neutral-600 sm:inline">Final
                            output</span>
                    </div>

                    <div
                        class="relative flex min-h-0 flex-1 items-center justify-center overflow-hidden border border-neutral-800 bg-black p-1.5 sm:p-2 xl:p-3">
                        <div class="pointer-events-none absolute inset-0 opacity-30"
                            style="background-image: linear-gradient(45deg, #111 25%, transparent 25%, transparent 75%, #111 75%), linear-gradient(45deg, #111 25%, transparent 25%, transparent 75%, #111 75%); background-position: 0 0, 8px 8px; background-size: 16px 16px;">
                        </div>

                        <img v-if="compositePhoto" :src="compositePhoto" alt="Composite Result"
                            class="relative z-10 max-h-full max-w-full w-auto object-contain shadow-[0_18px_55px_rgba(0,0,0,0.7)] transition-transform duration-700 group-hover:scale-[1.015]" />

                        <div v-else
                            class="relative z-10 flex flex-col items-center justify-center gap-4 text-neutral-500">
                            <div class="h-9 w-9 animate-spin rounded-full border border-neutral-700 border-t-white">
                            </div>
                            <span class="font-mono text-[10px] uppercase tracking-[0.18em]">Memproses foto...</span>
                        </div>

                        <span
                            class="absolute bottom-1.5 left-2 font-mono text-[6px] uppercase tracking-[0.16em] text-neutral-600 sm:bottom-2 sm:left-3 sm:text-[7px]">Composite
                            preview</span>
                        <span
                            class="absolute bottom-1.5 right-2 font-mono text-[6px] uppercase tracking-[0.16em] text-neutral-600 sm:bottom-2 sm:right-3 sm:text-[7px]">01
                            — 03</span>
                    </div>
                </section>

                <!-- 02. Live photo -->

                <section
                    class="flex min-w-0 min-h-0 flex-col border border-neutral-800 bg-[#090909] p-4 sm:p-5 xl:col-span-3">

                    <!-- HEADER -->
                    <div class="mb-4 flex shrink-0 items-end justify-between gap-3 border-b border-neutral-800 pb-3">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-neutral-500">
                                02 / Animated Preview
                            </p>
                            <h2 class="mt-1 text-xl font-medium uppercase tracking-[-0.04em] sm:text-2xl">
                                Live Photo
                            </h2>
                        </div>

                        <div class="inline-flex items-center gap-2 border border-neutral-700 px-2 py-1">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                            <span class="font-mono text-[9px] uppercase tracking-[0.16em] text-neutral-300">
                                Live
                            </span>
                        </div>
                    </div>

                    <!-- LIVE PHOTO PREVIEW CONTAINER -->
                    <div
                        class="relative flex min-h-0 flex-1 items-center justify-center overflow-hidden border border-neutral-800 bg-black p-3 sm:p-5">

                        <!-- Ambient Background -->
                        <div
                            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.055),transparent_70%)]">
                        </div>

                        <!-- Decorative Corner -->
                        <div
                            class="pointer-events-none absolute left-3 top-3 h-5 w-5 border-l border-t border-neutral-700">
                        </div>
                        <div
                            class="pointer-events-none absolute right-3 top-3 h-5 w-5 border-r border-t border-neutral-700">
                        </div>
                        <div
                            class="pointer-events-none absolute bottom-3 left-3 h-5 w-5 border-b border-l border-neutral-700">
                        </div>
                        <div
                            class="pointer-events-none absolute bottom-3 right-3 h-5 w-5 border-b border-r border-neutral-700">
                        </div>

                        <!-- FRAME: CENTERED & RESPONSIVE -->
                        <div class="relative z-10 h-full w-auto max-h-full max-w-full overflow-hidden border border-neutral-700 bg-neutral-950 shadow-[0_18px_55px_rgba(0,0,0,0.65)]"
                            :style="{
                                aspectRatio: `${canvasWidth} / ${canvasHeight}`,
                                width: 'auto',
                                height: '100%',
                                maxWidth: '100%',
                                maxHeight: '100%',
                            }">

                            <!-- Live Photo Slots -->
                            <template v-for="slot in slotsConfig" :key="slot.pose">

                                <div v-if="livePhotos.find(l => l.pose_number === slot.pose)"
                                    class="absolute inset-0 overflow-hidden bg-black" :style="{
                                        left: `${(slot.x / canvasWidth) * 100}%`,
                                        top: `${(slot.y / canvasHeight) * 100}%`,
                                        width: `${(slot.width / canvasWidth) * 100}%`,
                                        height: `${(slot.height / canvasHeight) * 100}%`,
                                    }">
                                    <video :src="livePhotos.find(l => l.pose_number === slot.pose)?.file_path" autoplay
                                        loop muted playsinline class="h-full w-full object-cover"></video>
                                </div>

                            </template>

                            <!-- Overlay Frame -->
                            <img v-if="overlayPath" :src="overlayPath" alt="Frame Overlay"
                                class="pointer-events-none absolute inset-0 z-20 h-full w-full object-contain" />

                        </div>

                        <!-- Preview Label -->
                        <span
                            class="absolute bottom-3 left-4 z-20 font-mono text-[8px] uppercase tracking-[0.18em] text-neutral-600">
                            Motion Preview
                        </span>

                        <span
                            class="absolute bottom-3 right-4 z-20 font-mono text-[8px] uppercase tracking-[0.18em] text-neutral-600">
                            {{ canvasWidth }} × {{ canvasHeight }}
                        </span>

                    </div>

                    <!-- FOOTER -->
                    <p class="mt-3 shrink-0 font-mono text-[9px] uppercase tracking-[0.14em] text-neutral-500">
                        Preview Live Photo dalam frame Anda
                    </p>

                </section>


                <!-- 03. QR Code / Digital gallery -->
                <section
                    class="relative flex min-h-0 min-w-0 flex-col justify-between overflow-hidden border border-neutral-800 bg-[#0b0b0b] p-2 sm:p-3 xl:col-span-4 xl:p-4">
                    <div class="flex min-h-0 flex-1 flex-col">
                        <div class="flex shrink-0 items-end justify-between gap-2 border-b border-neutral-800 pb-2">
                            <div>
                                <p
                                    class="font-mono text-[7px] uppercase tracking-[0.14em] text-neutral-500 sm:text-[8px] sm:tracking-[0.22em]">
                                    03 / Digital gallery</p>
                                <h2
                                    class="mt-0.5 text-sm font-medium uppercase tracking-[-0.04em] sm:text-lg xl:text-xl">
                                    Scan &
                                    download</h2>
                            </div>
                            <span class="font-mono text-lg leading-none text-neutral-700 sm:text-xl">03</span>
                        </div>

                        <div class="mt-2 flex min-h-0 flex-1 items-center justify-center sm:mt-3">
                            <div class="relative border border-neutral-700 bg-white p-1.5 sm:p-2">
                                <img :src="qrCodeSrc" alt="QR Code Galeri"
                                    class="relative h-[clamp(4.5rem,17vh,10rem)] w-[clamp(4.5rem,17vh,10rem)] object-contain sm:h-[clamp(5.5rem,19vh,11rem)] sm:w-[clamp(5.5rem,19vh,11rem)]" />
                                <span class="absolute -left-1.5 -top-1.5 h-3 w-3 border-l border-t border-white"></span>
                                <span
                                    class="absolute -right-1.5 -top-1.5 h-3 w-3 border-r border-t border-white"></span>
                                <span
                                    class="absolute -bottom-1.5 -left-1.5 h-3 w-3 border-b border-l border-white"></span>
                                <span
                                    class="absolute -bottom-1.5 -right-1.5 h-3 w-3 border-b border-r border-white"></span>
                            </div>
                        </div>

                        <div class="mt-2 shrink-0 text-center sm:mt-3">
                            <p class="text-[9px] font-medium uppercase tracking-[0.1em] text-white sm:text-xs">Scan QR
                                Code</p>
                            <p
                                class="mx-auto mt-1 max-w-[250px] text-[8px] leading-snug text-neutral-400 sm:text-[10px] sm:leading-relaxed">
                                Arahkan kamera smartphone Anda ke QR Code untuk membuka galeri digital.
                            </p>
                        </div>
                    </div>

                    <div class="mt-2 shrink-0 sm:mt-3">
                        <div
                            class="mb-2 flex items-center justify-between border-t border-neutral-800 pt-2 font-mono text-[6px] uppercase tracking-[0.12em] text-neutral-600 sm:text-[7px] sm:tracking-[0.18em]">
                            <span>Selfbooth digital archive</span>
                            <span>Return / 01</span>
                        </div>

                        <Link href="/kiosk"
                            class="group flex w-full items-center justify-between border border-white bg-white px-2 py-2 text-left text-[8px] font-bold uppercase tracking-[0.1em] text-black transition-colors duration-200 hover:bg-neutral-300 active:bg-neutral-400 sm:px-3 sm:py-2.5 sm:text-[10px] sm:tracking-[0.16em]">
                            <span>Mulai sesi baru</span>
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full border border-black/20 transition-transform duration-300 group-hover:translate-x-1 sm:h-6 sm:w-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                                </svg>
                            </span>
                        </Link>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer
                class="flex shrink-0 items-center justify-between gap-2 border-t border-neutral-800 pt-1.5 font-mono text-[6px] uppercase tracking-[0.12em] text-neutral-600 sm:pt-2 sm:text-[7px] sm:tracking-[0.2em]">
                <span>Thank you for using our photobooth</span>
                <span>Selfbooth Studio / Digital memories</span>
            </footer>
        </div>
    </div>
</template>
