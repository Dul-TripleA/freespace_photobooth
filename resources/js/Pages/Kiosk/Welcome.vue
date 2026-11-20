<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    accessUrl: {
        type: String,
        default: '/kiosk/access', // Sesuaikan jika route menu utama Anda menggunakan '/' atau URL lain
    },
});

const activeStep = ref(0);
const isFlashing = ref(false);
const isPrinting = ref(false);

const steps = [
    {
        number: '01',
        title: 'PILIH AKSES & BAYAR',
        desc: 'Scan QRIS dinamis secara instan atau masukkan kode voucher aktif Anda.',
        tag: 'INSTANT QRIS / VOUCHER',
    },
    {
        number: '02',
        title: 'PILIH TEMPLATE FRAME',
        desc: 'Tentukan desain layout dan tema frame favorit sebelum kamera dimulai.',
        tag: 'CUSTOM LAYOUTS',
    },
    {
        number: '03',
        title: 'POSE & HITUNG MUNDUR',
        desc: 'Ikuti aba-aba timer di layar dan ekspresikan gaya terbaik Anda.',
        tag: 'AUTO SHUTTER',
    },
    {
        number: '04',
        title: 'CETAK & UNDUH SOFTFILE',
        desc: 'Foto tercetak otomatis dalam hitungan detik beserta QR code untuk unduh ke HP.',
        tag: 'PRINT + DIGITAL',
    },
];

let stepInterval = null;
let boothAnimInterval = null;

const startExperience = () => {
    router.visit(props.accessUrl);
};

onMounted(() => {
    // Rotasi highlight langkah tutorial setiap 3.5 detik
    stepInterval = setInterval(() => {
        activeStep.value = (activeStep.value + 1) % steps.length;
    }, 3500);

    // Simulasi siklus animasi kamera (Flash -> Print Strip) setiap 5 detik
    boothAnimInterval = setInterval(() => {
        isFlashing.value = true;
        setTimeout(() => {
            isFlashing.value = false;
            isPrinting.value = true;
        }, 350);

        setTimeout(() => {
            isPrinting.value = false;
        }, 3800);
    }, 5500);
});

onUnmounted(() => {
    clearInterval(stepInterval);
    clearInterval(boothAnimInterval);
});
</script>

<template>
    <div
        @click="startExperience"
        class="relative min-h-screen w-full cursor-pointer select-none overflow-hidden bg-[#0a0a0a] font-mono text-white flex flex-col justify-between"
    >
        <!-- Grid Background Halus -->
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.07]"
            style="
                background-image: linear-gradient(to right, #ffffff 1px, transparent 1px),
                    linear-gradient(to bottom, #ffffff 1px, transparent 1px);
                background-size: 64px 64px;
            "
        ></div>

        <!-- Efek Shutter Flash Layar Penuh -->
        <div
            class="pointer-events-none absolute inset-0 z-50 bg-white transition-opacity duration-300"
            :class="isFlashing ? 'opacity-80' : 'opacity-0'"
        ></div>

        <!-- Top Bar -->
        <header class="relative z-10 flex items-center justify-between border-b border-white/15 px-8 py-5">
            <div class="flex items-center gap-3">
                <span class="inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-400"></span>
                <span class="text-xs tracking-[0.25em] text-neutral-400">PROCOLD STUDIO // SELF-PHOTOBOOTH</span>
            </div>
            <div class="text-xs tracking-[0.2em] text-neutral-500">
                SYSTEM READY &bull; TAP SCREEN TO START
            </div>
        </header>

        <!-- Main Content Grid -->
        <main class="relative z-10 mx-auto grid w-full max-w-7xl flex-1 grid-cols-1 items-center gap-10 px-8 py-8 lg:grid-cols-12">
            <!-- KIRI: Animasi Visual Photobooth & Print Strip -->
            <div class="flex flex-col items-center justify-center lg:col-span-5">
                <div class="relative w-full max-w-sm border border-white/20 bg-neutral-950 p-5 shadow-2xl">
                    <!-- Viewfinder Header -->
                    <div class="mb-3 flex items-center justify-between text-[10px] tracking-widest text-neutral-400">
                        <span>REC &bull; LIVE PREVIEW</span>
                        <span>ISO 400 // 1/125</span>
                    </div>

                    <!-- Simulasi Layar Kamera -->
                    <div class="relative aspect-[4/3] w-full overflow-hidden border border-white/15 bg-neutral-900 flex items-center justify-center">
                        <!-- Garis Bantu Rule of Thirds -->
                        <div class="absolute inset-0 grid grid-cols-3 grid-rows-3 opacity-20">
                            <div class="border-r border-b border-white"></div>
                            <div class="border-r border-b border-white"></div>
                            <div class="border-b border-white"></div>
                            <div class="border-r border-b border-white"></div>
                            <div class="border-r border-b border-white"></div>
                            <div class="border-b border-white"></div>
                            <div class="border-r border-white"></div>
                            <div class="border-r border-white"></div>
                            <div></div>
                        </div>

                        <!-- Bingkai Fokus Tengah Animasi -->
                        <div class="relative flex h-28 w-28 items-center justify-center transition-transform duration-500" :class="isFlashing ? 'scale-90' : 'scale-100'">
                            <span class="absolute top-0 left-0 h-4 w-4 border-t-2 border-l-2 border-amber-400"></span>
                            <span class="absolute top-0 right-0 h-4 w-4 border-t-2 border-r-2 border-amber-400"></span>
                            <span class="absolute bottom-0 left-0 h-4 w-4 border-b-2 border-l-2 border-amber-400"></span>
                            <span class="absolute bottom-0 right-0 h-4 w-4 border-b-2 border-r-2 border-amber-400"></span>

                            <div class="text-center">
                                <div class="text-2xl font-bold tracking-tighter text-white">SMILE</div>
                                <div class="mt-1 text-[10px] tracking-[0.2em] text-amber-400">3 &bull; 2 &bull; 1</div>
                            </div>
                        </div>

                        <!-- Badge Status -->
                        <div class="absolute bottom-2 left-2 bg-black/70 px-2 py-0.5 text-[10px] tracking-widest text-neutral-300">
                            AUTO-CAPTURE
                        </div>
                    </div>

                    <!-- Slot Keluar Cetak Foto (Printer Slot) -->
                    <div class="mt-5">
                        <div class="flex items-center justify-between text-[10px] tracking-widest text-neutral-500 mb-1.5">
                            <span>PHOTO DISPENSER</span>
                            <span :class="isPrinting ? 'text-amber-400 animate-pulse' : ''">
                                {{ isPrinting ? 'PRINTING...' : 'IDLE' }}
                            </span>
                        </div>
                        <div class="h-2.5 w-full rounded-full bg-neutral-800 border border-white/15"></div>

                        <!-- Animasi Kertas Photo Strip Keluar -->
                        <div class="relative mx-auto h-36 w-32 overflow-hidden">
                            <div
                                class="mx-auto w-28 border border-neutral-300 bg-white p-2 text-black shadow-lg transition-transform duration-1000 ease-out"
                                :class="isPrinting ? 'translate-y-2' : '-translate-y-full'"
                            >
                                <div class="space-y-1.5">
                                    <div class="h-8 w-full bg-neutral-900 flex items-center justify-center text-[8px] text-white tracking-widest">POSE 01</div>
                                    <div class="h-8 w-full bg-neutral-800 flex items-center justify-center text-[8px] text-white tracking-widest">POSE 02</div>
                                    <div class="h-8 w-full bg-neutral-900 flex items-center justify-center text-[8px] text-white tracking-widest">POSE 03</div>
                                </div>
                                <div class="mt-1.5 flex items-center justify-between border-t border-neutral-300 pt-1 text-[7px] font-bold tracking-wider">
                                    <span>PROCOLD</span>
                                    <span>2026</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: Judul Utama & Alur Tutorial 4 Langkah -->
            <div class="flex flex-col justify-center lg:col-span-7">
                <p class="text-xs tracking-[0.3em] text-neutral-400">SELF-SERVICE EXPERIENCE</p>
                <h1 class="mt-2 font-sans text-5xl font-black tracking-tight text-white sm:text-6xl">
                    CAPTURE YOUR<br />
                    <span class="text-neutral-400">MOMENT.</span>
                </h1>

                <!-- 4 Langkah Tutorial -->
                <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div
                        v-for="(step, idx) in steps"
                        :key="step.number"
                        @click.stop="activeStep = idx"
                        class="border p-4 transition-all duration-300"
                        :class="
                            activeStep === idx
                                ? 'border-white bg-white text-black shadow-lg'
                                : 'border-white/15 bg-neutral-950/80 text-white hover:border-white/40'
                        "
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold tracking-widest"
                                :class="activeStep === idx ? 'text-neutral-600' : 'text-amber-400'"
                            >
                                STEP {{ step.number }}
                            </span>
                            <span
                                class="border px-1.5 py-0.5 text-[9px] tracking-wider"
                                :class="activeStep === idx ? 'border-black/20 text-black' : 'border-white/15 text-neutral-400'"
                            >
                                {{ step.tag }}
                            </span>
                        </div>
                        <h2 class="mt-2 font-sans text-base font-bold tracking-wide">
                            {{ step.title }}
                        </h2>
                        <p
                            class="mt-1 text-xs leading-relaxed"
                            :class="activeStep === idx ? 'text-neutral-700' : 'text-neutral-400'"
                        >
                            {{ step.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bottom Action Bar -->
        <footer class="relative z-10 border-t border-white/15 bg-black/80 px-8 py-5 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="text-xs tracking-[0.2em] text-neutral-400">
                    SENTUH LAYAR DI MANA SAJA UNTUK MELANJUTKAN
                </div>
                <button
                    type="button"
                    @click.stop="startExperience"
                    class="group flex items-center gap-4 bg-white px-8 py-4 font-sans text-sm font-black tracking-[0.2em] text-black transition-transform active:scale-95 hover:bg-neutral-200"
                >
                    <span>MULAI SESI SEKARANG</span>
                    <span class="transition-transform duration-300 group-hover:translate-x-1.5">&rarr;</span>
                </button>
            </div>
        </footer>
    </div>
</template>