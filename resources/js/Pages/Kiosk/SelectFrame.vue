<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    session: Object,
    frames: Array
});

const form = useForm({
    frame_id: ''
});

const activeCategory = ref('semua');
const categories = ['semua', 'pesta', 'corporate', 'formal'];

// Filter frame berdasarkan kategori yang dipilih
const filteredFrames = computed(() => {
    if (activeCategory.value === 'semua') return props.frames;
    return props.frames.filter(f => f.category === activeCategory.value);
});

const selectFrame = (id) => {
    form.frame_id = id;
};

const submitFrame = () => {
    form.post(route('kiosk.frame.select', { session: props.session.id }));
};
</script>

<template>
    <Head title="Pilih Frame" />

    <!-- Container Utama: Terkunci pas di satu layar penuh tanpa scroll -->
    <div
        class="relative h-screen w-screen bg-[#0a0a0a] text-white flex flex-col justify-between px-6 py-3 sm:px-10 sm:py-4 select-none overflow-hidden font-sans">
        <!-- 1. BACKGROUND AMBIENT PHOTOGRAPHY & FILM GRAIN -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <img src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?auto=format&fit=crop&w=1920&q=80"
                alt="Editorial Frame Studio Background"
                class="w-full h-full object-cover object-center opacity-[0.12] grayscale contrast-125 scale-105" />
            <div class="absolute inset-0 bg-gradient-to-b from-[#0a0a0a]/90 via-[#0a0a0a]/80 to-[#0a0a0a]"></div>
            <div class="absolute inset-0 opacity-[0.04]" style="
                    background-image:
                        linear-gradient(to right, #ffffff 1px, transparent 1px),
                        linear-gradient(to bottom, #ffffff 1px, transparent 1px);
                    background-size: 120px 120px;
                "></div>
        </div>

        <!-- 2. TOP NAVIGATION BAR -->
        <header
            class="relative z-10 w-full max-w-7xl mx-auto flex items-center justify-between pb-2 border-b border-neutral-800/80 shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-white inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-white inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-white inline-block"></span>
            </div>

            <div class="flex items-center gap-4">
                <span class="font-mono text-xs uppercase tracking-[0.2em] text-neutral-400">
                    STEP 02 // FRAME SELECTION
                </span>
            </div>
        </header>

        <!-- 3. MAIN EDITORIAL SECTION -->
        <main class="relative z-10 w-full max-w-7xl mx-auto my-1.5 flex-grow flex flex-col min-h-0">
            <!-- Hero Title & Category Filter Tabs -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-3 gap-3 shrink-0">
                <div>
                    <p class="font-mono italic text-xs text-neutral-400 tracking-wider mb-0.5">
                        Curated Studio Templates
                    </p>
                    <h1 class="text-3xl sm:text-4xl font-normal uppercase tracking-tight leading-none">
                        SELECT FRAME
                    </h1>
                </div>

                <!-- Filter Kategori -->
                <div class="flex flex-wrap gap-1.5 border border-neutral-700 p-1 bg-black/60 backdrop-blur-md">
                    <button v-for="cat in categories" :key="cat" @click="activeCategory = cat" :class="[
                        'px-3.5 py-1 font-mono text-xs uppercase tracking-widest transition-colors',
                        activeCategory === cat
                            ? 'bg-white text-black font-bold'
                            : 'text-neutral-400 hover:text-white hover:bg-neutral-900'
                    ]">
                        {{ cat }}
                    </button>
                </div>
            </div>

            <!-- Exposed Hairline Grid (Daftar Frame dengan Proporsi 3/4 & Scroll Internal) -->
            <div
                class="flex-grow overflow-y-auto pr-1 min-h-0 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 border-t border-l border-neutral-700 bg-[#0a0a0a]/85 backdrop-blur-sm">

                <div v-for="(frame, index) in filteredFrames" :key="frame.id" @click="selectFrame(frame.id)" :class="[
                    'group relative border-r border-b border-neutral-700 flex flex-col justify-between cursor-pointer transition-colors duration-300',
                    form.frame_id === frame.id
                        ? 'bg-neutral-900/95 ring-1 ring-inset ring-white'
                        : 'bg-[#0a0a0a]/90 hover:bg-neutral-950',
                ]">
                    <!-- Pratinjau Gambar Frame (Tinggi diset tepat 75% / 3/4 dari card & width proporsional) -->
                    <div
                        class="relative h-[72%] w-full overflow-hidden border-b border-neutral-700 bg-neutral-950 flex items-center justify-center p-3">
                        <img :src="frame.overlay_path" :alt="frame.name"
                            class="max-h-full max-w-full object-contain filter drop-shadow-[0_10px_20px_rgba(0,0,0,0.8)] transition-transform duration-500 group-hover:scale-105"
                            @error="$event.target.style.display = 'none'" />

                        <!-- Badge Status & Pose di Atas -->
                        <div
                            class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between pointer-events-none">
                            <span
                                class="font-mono text-[9px] uppercase tracking-[0.2em] bg-black/80 backdrop-blur-md px-2 py-0.5 border border-neutral-700 text-neutral-300">
                                {{ frame.category || 'GENERAL' }}
                            </span>
                            <span class="px-2 py-0.5 text-[9px] font-mono uppercase tracking-widest border" :class="form.frame_id === frame.id
                                    ? 'border-white bg-white text-black font-bold'
                                    : 'border-neutral-700 bg-black/80 text-neutral-400'
                                ">
                                {{ form.frame_id === frame.id ? 'SELECTED' : `${frame.total_poses} POSES` }}
                            </span>
                        </div>
                    </div>

                    <!-- Informasi & Aksi Frame (Sisa 25% ruang bawah) -->
                    <div class="px-4 py-2.5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-normal uppercase tracking-wide text-sm text-white truncate mb-0.5">
                                {{ frame.name }}
                            </h3>
                            <p class="font-mono text-[9px] text-neutral-500 uppercase tracking-widest">
                                DIMENSION: {{ frame.canvas_width || 1200 }} × {{ frame.canvas_height || 1800 }} PX
                            </p>
                        </div>

                        <!-- Bottom Action & Index -->
                        <div class="flex items-end justify-between pt-2 mt-1.5 border-t border-neutral-800/80">
                            <span :class="[
                                'inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-mono uppercase tracking-widest border transition-colors',
                                form.frame_id === frame.id
                                    ? 'bg-white text-black border-white font-bold'
                                    : 'border-white/70 text-white group-hover:bg-white group-hover:text-black',
                            ]">
                                {{ form.frame_id === frame.id ? 'ACTIVE' : 'SELECT' }} <span>→</span>
                            </span>

                            <span class="text-2xl font-mono text-neutral-600 leading-none">
                                {{ String(index + 1).padStart(2, '0') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Empty State jika tidak ada frame -->
            <div v-if="filteredFrames.length === 0" class="border border-neutral-700 p-12 text-center bg-black/50">
                <p class="font-mono text-xs text-neutral-400 uppercase tracking-widest">
                    Tidak ada frame tersedia dalam kategori ini.
                </p>
            </div>
        </main>

        <!-- 4. BOTTOM ACTION BAR -->
        <footer class="relative z-10 w-full max-w-7xl mx-auto shrink-0 pt-1">
            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-3 border border-neutral-700 p-3 bg-[#0a0a0a]/90 backdrop-blur-md">
                <div class="font-mono text-xs text-neutral-400">
                    <span v-if="form.frame_id">
                        SELECTED FRAME ID:
                        <strong class="text-white font-mono ml-1">
                            [ {{ form.frame_id }} ]
                        </strong>
                    </span>
                    <span v-else class="italic font-mono">
                        Silakan pilih template frame di atas untuk melanjutkan ke sesi kamera...
                    </span>
                </div>

                <button v-if="form.frame_id" @click="submitFrame" :disabled="form.processing"
                    class="w-full sm:w-auto bg-white text-black hover:bg-neutral-200 px-8 py-2.5 font-mono text-xs font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-4 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                    <span>
                        {{ form.processing ? "MENYIAPKAN KAMERA..." : "LANJUT KE KAMERA" }}
                    </span>
                    <span>→</span>
                </button>
            </div>
        </footer>
    </div>
</template>