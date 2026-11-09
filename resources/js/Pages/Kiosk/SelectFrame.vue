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

    <div
        class="min-h-screen bg-neutral-950 text-white flex flex-col items-center px-4 py-6 md:px-8 md:py-8 font-sans relative overflow-hidden"
    >

        <!-- Background Glow -->
        <div
            class="absolute -top-48 -left-48 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[120px] pointer-events-none"
        ></div>

        <div
            class="absolute -bottom-48 -right-48 w-[500px] h-[500px] bg-orange-500/10 rounded-full blur-[120px] pointer-events-none"
        ></div>

        <!-- Background Grid -->
        <div
            class="absolute inset-0 opacity-[0.025] pointer-events-none"
            style="
                background-image:
                    linear-gradient(rgba(255,255,255,.6) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.6) 1px, transparent 1px);
                background-size: 45px 45px;
            "
        ></div>


        <div class="relative z-10 w-full flex flex-col items-center">

            <!-- Header -->
            <div class="text-center mb-7 mt-2 md:mt-3">

                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-400 text-[10px] sm:text-xs font-bold uppercase tracking-[0.22em] mb-4"
                >
                    <span
                        class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"
                    ></span>

                    Choose Your Frame
                </div>

                <h1
                    class="text-2xl md:text-4xl font-black mb-2 tracking-tight text-white"
                >
                    Pilih Frame Foto Anda
                </h1>

                <p
                    class="text-xs md:text-sm text-neutral-400 max-w-xl mx-auto leading-relaxed"
                >
                    Pilih tata letak frame yang akan digunakan pada hasil foto
                    Anda.
                </p>

            </div>


            <!-- Filter Kategori -->
            <div
                class="flex flex-wrap justify-center gap-2 mb-8 p-1.5 rounded-2xl bg-neutral-900/80 border border-white/10 backdrop-blur-xl"
            >

                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="activeCategory = cat"
                    :class="[
                        'px-5 py-2.5 rounded-xl font-bold text-xs md:text-sm capitalize transition-all duration-200',
                        activeCategory === cat
                            ? 'bg-amber-400 text-neutral-950 shadow-lg shadow-amber-400/10'
                            : 'text-neutral-500 hover:text-white hover:bg-white/5'
                    ]"
                >
                    {{ cat }}
                </button>

            </div>


            <!-- Grid Frame -->
            <div
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 max-w-6xl w-full flex-grow content-start px-1 md:px-2"
            >

                <div
                    v-for="frame in filteredFrames"
                    :key="frame.id"
                    @click="selectFrame(frame.id)"
                    :class="[
                        'relative bg-neutral-900/80 backdrop-blur-xl rounded-2xl overflow-hidden cursor-pointer border transition-all duration-300 group',
                        form.frame_id === frame.id
                            ? 'border-amber-400 shadow-[0_10px_40px_rgba(251,191,36,0.16)] -translate-y-1'
                            : 'border-white/10 hover:border-amber-400/40 hover:-translate-y-1'
                    ]"
                >

                    <!-- Selected Glow -->
                    <div
                        v-if="form.frame_id === frame.id"
                        class="absolute inset-0 z-10 pointer-events-none rounded-2xl ring-1 ring-inset ring-amber-400/30"
                    ></div>


                    <!-- Wrapper Gambar Frame -->
                    <div
                        class="aspect-[2/3] relative bg-neutral-800 w-full overflow-hidden flex items-center justify-center"
                    >

                        <!-- Background sebelum image -->
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-neutral-700 via-neutral-800 to-neutral-950"
                        ></div>


                        <!-- Gambar Frame -->
                        <img
                            :src="frame.overlay_path"
                            :alt="frame.name"
                            class="absolute inset-0 w-full h-full object-cover z-10 transition-transform duration-500 group-hover:scale-[1.035]"
                            @error="$event.target.style.display = 'none'"
                        />


                        <!-- Overlay Gradient -->
                        <div
                            class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/70 to-transparent z-15 pointer-events-none"
                        ></div>


                        <!-- Indikator Total Pose -->
                        <div
                            class="absolute bottom-3 left-3 z-20 bg-black/70 backdrop-blur-md text-white px-3 py-1.5 rounded-full text-[9px] md:text-[10px] font-bold border border-white/10 shadow-lg flex items-center gap-1.5"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-amber-400"
                            ></span>

                            {{ frame.total_poses }} Pose
                        </div>


                        <!-- Hover Indicator -->
                        <div
                            class="absolute inset-0 z-20 flex items-center justify-center bg-black/0 group-hover:bg-black/20 transition-all duration-300 pointer-events-none"
                        >
                            <div
                                class="opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100 transition-all duration-300 px-4 py-2 rounded-full bg-white/90 text-neutral-900 text-[10px] font-black uppercase tracking-wider shadow-xl"
                            >
                                Pilih Frame
                            </div>
                        </div>

                    </div>


                    <!-- Nama Frame -->
                    <div
                        class="p-3.5 bg-neutral-900/90 text-center relative z-20 border-t border-white/5"
                    >

                        <h3
                            class="font-bold text-neutral-200 text-xs md:text-sm truncate"
                        >
                            {{ frame.name }}
                        </h3>

                        <p
                            class="text-[9px] text-neutral-600 uppercase tracking-wider mt-1"
                        >
                            {{ frame.category }}
                        </p>

                    </div>


                    <!-- Indikator Centang -->
                    <div
                        v-if="form.frame_id === frame.id"
                        class="absolute top-3 right-3 z-30 w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center shadow-lg shadow-amber-500/20"
                    >

                        <svg
                            class="w-4 h-4 md:w-5 md:h-5 text-neutral-950"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            ></path>
                        </svg>

                    </div>

                </div>

            </div>


            <!-- Tombol Lanjut -->
            <div
                class="mt-7 h-20 w-full max-w-6xl flex justify-end items-center px-1 md:px-2"
            >

                <button
                    v-if="form.frame_id"
                    @click="submitFrame"
                    :disabled="form.processing"
                    class="group bg-amber-400 text-neutral-950 px-7 py-3.5 md:px-10 md:py-4 rounded-2xl font-black text-sm md:text-base hover:bg-amber-300 hover:-translate-y-0.5 transition-all duration-200 shadow-[0_10px_40px_rgba(251,191,36,0.15)] hover:shadow-[0_10px_45px_rgba(251,191,36,0.25)] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0 flex items-center gap-3"
                >

                    <span>
                        {{ form.processing ? 'Menyiapkan Kamera...' : 'Lanjut ke Kamera' }}
                    </span>

                    <svg
                        v-if="!form.processing"
                        class="w-5 h-5 md:w-6 md:h-6 transition-transform duration-200 group-hover:translate-x-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        ></path>
                    </svg>

                    <!-- Loading -->
                    <svg
                        v-if="form.processing"
                        class="w-5 h-5 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="3"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"
                        ></path>
                    </svg>

                </button>

            </div>

        </div>

    </div>
</template>