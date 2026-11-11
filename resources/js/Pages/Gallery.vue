
<script setup>
import { Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted, watch } from "vue";

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

// ==========================================
// GIF AUTO POLLING
// ==========================================

let pollInterval = null;
let pollCount = 0;

const stopGifPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

// Hentikan polling segera setelah GIF tersedia.
watch(
    () => props.gifMedia?.url,
    (url) => {
        if (url) {
            stopGifPolling();
        }
    }
);

onMounted(() => {
    if (!props.gifMedia?.url) {
        pollInterval = setInterval(() => {
            if (props.gifMedia?.url || pollCount >= 20) {
                stopGifPolling();
                return;
            }

            pollCount++;

            router.reload({
                only: [
                    "gifMedia",
                    "livePhotos",
                    "compositePhoto",
                    "rawPhotos",
                ],
                preserveScroll: true,
                preserveState: true,
            });

            // Maksimal 20 kali reload.
            if (pollCount >= 20) {
                stopGifPolling();
            }
        }, 3000);
    }
});

onUnmounted(() => {
    stopGifPolling();
});
</script>

<template>
    <Head title="Digital Gallery — Freespace Photobooth" />

    <div class="gallery-page min-h-screen overflow-x-hidden bg-[#080808] text-white">

        <!-- =====================================
             HEADER
        ====================================== -->

        <header class="relative z-10 border-b border-white/10">
            <div class="mx-auto flex max-w-[1600px] items-center justify-between px-5 py-5 sm:px-8 lg:px-12">

                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center border border-white/30 text-xs font-black tracking-tighter">
                        Pd
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.25em]">
                            Procold
                        </p>
                        <p class="text-[9px] uppercase tracking-[0.2em] text-neutral-500">
                            Photobooth Studio
                        </p>
                    </div>
                </div>

                <div class="hidden text-right sm:block">
                    <p class="text-[9px] uppercase tracking-[0.25em] text-neutral-500">
                        Digital Collection
                    </p>
                    <p class="mt-1 font-mono text-xs text-neutral-300">
                        {{ session?.session_code || session?.id || "-" }}
                    </p>
                </div>

                <div class="text-[10px] uppercase tracking-[0.2em] text-neutral-400 sm:hidden">
                    GALLERY / 01
                </div>
            </div>
        </header>


        <!-- =====================================
             HERO SECTION
        ====================================== -->

        <section class="relative border-b border-white/10">

            <div class="mx-auto grid max-w-[1600px] grid-cols-1 gap-10 px-5 py-14 sm:px-8 md:py-20 lg:grid-cols-12 lg:items-end lg:px-12 lg:py-24">

                <!-- Main Heading -->

                <div class="lg:col-span-8">

                    <div class="mb-7 flex items-center gap-3">
                        <span class="h-px w-8 bg-white"></span>

                        <p class="text-[10px] font-semibold uppercase tracking-[0.35em] text-neutral-400">
                            Your Digital Memories
                        </p>

                        <span class="text-[10px] text-neutral-600">
                            / 2026
                        </span>
                    </div>

                    <h1 class="max-w-5xl text-5xl font-black leading-[0.95] tracking-[-0.06em] sm:text-6xl md:text-7xl lg:text-[100px]">
                        Every moment,

                        <span class="block font-serif font-normal italic tracking-[-0.04em] text-neutral-400">
                            worth keeping.
                        </span>
                    </h1>
                </div>

                <!-- Description -->

                <div class="flex flex-col justify-end lg:col-span-4 lg:pl-10">

                    <p class="max-w-sm text-sm leading-7 text-neutral-400">
                        Setiap pose memiliki ceritanya sendiri.
                        Temukan, simpan, dan bagikan momen terbaik
                        dari sesi photobooth Anda.
                    </p>

                    <div class="mt-8 grid grid-cols-3 border-y border-white/15 py-4">

                        <div>
                            <p class="text-2xl font-light tracking-tight">
                                {{ rawPhotos?.length || 0 }}
                            </p>
                            <p class="mt-1 text-[8px] uppercase tracking-[0.2em] text-neutral-500">
                                Photos
                            </p>
                        </div>

                        <div class="border-l border-white/10 pl-4">
                            <p class="text-2xl font-light tracking-tight">
                                {{ livePhotos?.length || 0 }}
                            </p>
                            <p class="mt-1 text-[8px] uppercase tracking-[0.2em] text-neutral-500">
                                Live
                            </p>
                        </div>

                        <div class="border-l border-white/10 pl-4">
                            <p class="text-2xl font-light tracking-tight">
                                {{ gifMedia?.url ? "01" : "..." }}
                            </p>
                            <p class="mt-1 text-[8px] uppercase tracking-[0.2em] text-neutral-500">
                                GIF
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </section>


        <!-- =====================================
             MAIN GALLERY
        ====================================== -->

        <main class="mx-auto max-w-[1600px] px-5 py-14 sm:px-8 lg:px-12 lg:py-20">

            <!-- Section Header -->

            <div class="mb-8 flex items-end justify-between">

                <div>
                    <p class="mb-2 text-[9px] uppercase tracking-[0.3em] text-neutral-500">
                        Collection / 001
                    </p>

                    <h2 class="text-2xl font-bold tracking-tight md:text-3xl">
                        Your Highlights
                    </h2>
                </div>

                <p class="hidden text-[9px] uppercase tracking-[0.2em] text-neutral-600 sm:block">
                    Freespace Digital Archive
                </p>

            </div>


            <!-- =================================
                 FEATURED GRID
            ================================== -->

            <div class="grid grid-cols-1 gap-px border border-white/10 bg-white/10 lg:grid-cols-12">

                <!-- PHOTO STRIP -->

                <section class="bg-[#0b0b0b] p-5 sm:p-7 lg:col-span-7 lg:p-9">

                    <div class="mb-7 flex items-center justify-between">

                        <div>
                            <p class="text-[9px] uppercase tracking-[0.3em] text-neutral-500">
                                01 / Featured
                            </p>

                            <h3 class="mt-2 text-xl font-bold tracking-tight">
                                Photo Strip
                            </h3>
                        </div>

                        <span class="text-[10px] uppercase tracking-widest text-neutral-600">
                            JPG
                        </span>

                    </div>

                    <div
                        v-if="compositePhoto?.url"
                        class="space-y-6"
                    >

                        <div class="media-stage flex min-h-[280px] items-center justify-center overflow-hidden border border-white/10 p-4 sm:min-h-[420px] sm:p-8">

                            <img
                                :src="compositePhoto.url"
                                alt="Hasil Photo Strip"
                                loading="lazy"
                                decoding="async"
                                class="relative z-10 mx-auto max-h-[650px] w-auto max-w-full rounded-sm object-contain"
                            />

                        </div>

                        <a
                            :href="compositePhoto.url"
                            :download="`photostrip-${session?.session_code || 'freespace'}.jpg`"
                            class="download-link flex w-full items-center justify-between border border-white/20 px-5 py-4 text-[10px] font-bold uppercase tracking-[0.2em] transition duration-300 hover:border-white hover:bg-white hover:text-black"
                        >
                            <span>Download Photo Strip</span>
                            <span class="text-base">↗</span>
                        </a>

                    </div>

                    <div
                        v-else
                        class="flex min-h-[280px] flex-col items-center justify-center border border-dashed border-white/15 text-center sm:min-h-[420px]"
                    >
                        <span class="font-serif text-4xl italic text-neutral-700">
                            01
                        </span>

                        <p class="mt-4 text-xs uppercase tracking-[0.2em] text-neutral-500">
                            Photo Strip Not Available
                        </p>
                    </div>

                </section>


                <!-- GIF ANIMATION -->

                <section class="bg-[#0b0b0b] p-5 sm:p-7 lg:col-span-5 lg:p-9">

                    <div class="mb-7 flex items-center justify-between">

                        <div>
                            <p class="text-[9px] uppercase tracking-[0.3em] text-neutral-500">
                                02 / Motion
                            </p>

                            <h3 class="mt-2 text-xl font-bold tracking-tight">
                                Animated Moments
                            </h3>
                        </div>

                        <span class="text-[10px] uppercase tracking-widest text-neutral-600">
                            GIF
                        </span>

                    </div>

                    <!-- GIF AVAILABLE -->

                    <div
                        v-if="gifMedia?.url"
                        class="space-y-6"
                    >

                        <div class="media-stage flex min-h-[280px] items-center justify-center overflow-hidden border border-white/10 p-4 sm:min-h-[420px]">

                            <img
                                :src="gifMedia.url"
                                alt="Animasi GIF"
                                loading="lazy"
                                class="relative z-10 mx-auto max-h-[500px] w-auto max-w-full object-contain"
                            />

                        </div>

                        <a
                            :href="gifMedia.url"
                            :download="`animasi-gif-${session?.session_code || 'freespace'}.gif`"
                            class="download-link flex w-full items-center justify-between border border-white/20 px-5 py-4 text-[10px] font-bold uppercase tracking-[0.2em] transition duration-300 hover:border-white hover:bg-white hover:text-black"
                        >
                            <span>Download Animated GIF</span>
                            <span class="text-base">↗</span>
                        </a>

                    </div>


                    <!-- GIF LOADING -->

                    <div
                        v-else
                        class="flex min-h-[280px] flex-col items-center justify-center border border-dashed border-white/15 px-6 text-center sm:min-h-[420px]"
                    >

                        <div class="relative flex h-12 w-12 items-center justify-center">
                            <div class="absolute inset-0 rounded-full border border-white/10"></div>

                            <div class="h-10 w-10 animate-spin rounded-full border border-white border-t-transparent"></div>

                            <span class="absolute text-[9px] font-bold">
                                FS
                            </span>
                        </div>

                        <p class="mt-7 text-xs font-bold uppercase tracking-[0.2em]">
                            Rendering In Progress
                        </p>

                        <p class="mt-3 max-w-[220px] text-[11px] leading-6 text-neutral-500">
                            GIF sedang diproses. Halaman akan diperbarui
                            secara otomatis setelah hasilnya tersedia.
                        </p>

                        <div class="mt-6 h-px w-20 bg-white/20"></div>

                        <p class="mt-3 font-mono text-[9px] text-neutral-600">
                            PROCESSING MEDIA...
                        </p>

                    </div>

                </section>

            </div>


            <!-- =================================
                 LIVE PHOTO SECTION
            ================================== -->

            <section
                v-if="livePhotos?.length > 0"
                class="mt-20 border-t border-white/10 pt-10 md:mt-28 md:pt-14"
            >

                <div class="mb-9 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

                    <div>
                        <p class="mb-2 text-[9px] uppercase tracking-[0.3em] text-neutral-500">
                            Collection / 002
                        </p>

                        <h2 class="text-2xl font-bold tracking-tight md:text-4xl">
                            Live in Motion<span class="font-serif font-normal italic text-neutral-500">.</span>
                        </h2>
                    </div>

                    <p class="text-[10px] uppercase tracking-[0.2em] text-neutral-500">
                        {{ livePhotos.length }} Motion Clips
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    <article
                        v-for="(live, idx) in livePhotos"
                        :key="live?.id || idx"
                        class="group"
                    >

                        <div class="relative overflow-hidden border border-white/10 bg-[#101010]">

                            <video
                                v-if="live?.url"
                                :src="live.url"
                                autoplay
                                loop
                                muted
                                playsinline
                                controls
                                preload="metadata"
                                class="aspect-[4/5] w-full object-cover transition duration-700 group-hover:scale-[1.02]"
                            ></video>

                            <div
                                v-else
                                class="flex aspect-[4/5] items-center justify-center"
                            >
                                <span class="font-serif text-3xl italic text-neutral-700">
                                    No Preview
                                </span>
                            </div>

                            <div class="absolute left-3 top-3 border border-white/20 bg-black/70 px-3 py-1.5 backdrop-blur-sm">
                                <span class="text-[9px] uppercase tracking-[0.2em]">
                                    Pose {{ live.pose_number || idx + 1 }}
                                </span>
                            </div>

                        </div>

                        <div class="flex items-center justify-between border-b border-white/10 py-4">

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.15em]">
                                    Motion Clip
                                </p>

                                <p class="mt-1 text-[9px] text-neutral-500">
                                    Live Photo / {{ String(idx + 1).padStart(2, '0') }}
                                </p>
                            </div>

                            <a
                                v-if="live?.url"
                                :href="live.url"
                                :download="`live-pose-${live.pose_number || idx + 1}.webm`"
                                class="flex h-9 w-9 items-center justify-center border border-white/20 text-sm transition hover:bg-white hover:text-black"
                                :aria-label="`Download Live Photo ${idx + 1}`"
                            >
                                ↓
                            </a>

                        </div>

                    </article>

                </div>

            </section>


            <!-- =================================
                 RAW PHOTOS SECTION
            ================================== -->

            <section class="mt-20 border-t border-white/10 pt-10 md:mt-28 md:pt-14">

                <div class="mb-9 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

                    <div>
                        <p class="mb-2 text-[9px] uppercase tracking-[0.3em] text-neutral-500">
                            Collection / 003
                        </p>

                        <h2 class="text-2xl font-bold tracking-tight md:text-4xl">
                            Every Single Pose<span class="font-serif font-normal italic text-neutral-500">.</span>
                        </h2>
                    </div>

                    <p class="text-[10px] uppercase tracking-[0.2em] text-neutral-500">
                        {{ rawPhotos?.length || 0 }} Original Photos
                    </p>

                </div>


                <!-- PHOTO GRID -->

                <div
                    v-if="rawPhotos?.length > 0"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4 xl:grid-cols-5"
                >

                    <article
                        v-for="(photo, index) in rawPhotos"
                        :key="photo?.id || index"
                        class="group min-w-0"
                    >

                        <div class="relative overflow-hidden border border-white/10 bg-[#111111]">

                            <img
                                v-if="photo?.url"
                                :src="photo.url"
                                :alt="`Pose ${index + 1}`"
                                loading="lazy"
                                decoding="async"
                                class="aspect-[4/5] w-full object-cover transition duration-700 group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex aspect-[4/5] items-center justify-center"
                            >
                                <span class="text-xs text-neutral-600">
                                    No Image
                                </span>
                            </div>

                            <span class="absolute left-2 top-2 bg-black/70 px-2 py-1 text-[9px] font-mono text-white backdrop-blur-sm">
                                #{{ String(index + 1).padStart(2, "0") }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-white/10 py-3">

                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider">
                                    Pose {{ index + 1 }}
                                </p>

                                <p class="mt-1 truncate text-[9px] text-neutral-600">
                                    Original Capture
                                </p>
                            </div>

                            <a
                                v-if="photo?.url"
                                :href="photo.url"
                                :download="`pose-${index + 1}.jpg`"
                                class="flex h-8 w-8 shrink-0 items-center justify-center border border-white/20 text-xs transition hover:bg-white hover:text-black"
                                :aria-label="`Download Pose ${index + 1}`"
                            >
                                ↓
                            </a>

                        </div>

                    </article>

                </div>


                <!-- EMPTY STATE -->

                <div
                    v-else
                    class="border border-dashed border-white/15 py-20 text-center"
                >
                    <p class="font-serif text-4xl italic text-neutral-700">
                        No captures yet.
                    </p>

                    <p class="mt-4 text-[10px] uppercase tracking-[0.2em] text-neutral-500">
                        Your photos will appear here.
                    </p>
                </div>

            </section>

        </main>


        <!-- =====================================
             FOOTER
        ====================================== -->

        <footer class="border-t border-white/10">

            <div class="mx-auto flex max-w-[1600px] flex-col gap-5 px-5 py-8 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-12">

                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.25em]">
                        Procold Studio
                    </p>

                    <p class="mt-2 text-[9px] uppercase tracking-[0.15em] text-neutral-600">
                        Every frame tells a story.
                    </p>
                </div>

                <div class="flex items-center gap-5 text-[9px] uppercase tracking-[0.2em] text-neutral-500">
                    <span>DIGITAL GALLERY</span>
                    <span class="h-1 w-1 rounded-full bg-neutral-600"></span>
                    <span>© 2026 FREESPACE</span>
                </div>

            </div>

        </footer>

    </div>
</template>

<style scoped>
.gallery-page {
    font-family: Inter, "Helvetica Neue", Arial, sans-serif;
}

/* Editorial grid texture untuk area preview */
.media-stage {
    position: relative;
    isolation: isolate;
    background-color: #080808;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
    background-size: 32px 32px;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Minimal scrollbar */
.gallery-page::-webkit-scrollbar {
    width: 6px;
}

.gallery-page::-webkit-scrollbar-track {
    background: #080808;
}

.gallery-page::-webkit-scrollbar-thumb {
    background: #404040;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .gallery-page *,
    .gallery-page *::before,
    .gallery-page *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
