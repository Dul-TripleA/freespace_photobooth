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
    <div class="min-h-screen bg-gray-950 text-white flex flex-col items-center p-4 md:p-8 font-sans">
        
        <div class="text-center mb-6 mt-2 md:mt-4">
            <h1 class="text-2xl md:text-3xl font-black mb-2 text-blue-500 tracking-wide">Pilih Frame Foto Anda</h1>
            <p class="text-sm md:text-base text-gray-400">Pilih tata letak yang akan digunakan pada hasil cetak nanti.</p>
        </div>

        <!-- Filter Kategori -->
        <div class="flex flex-wrap justify-center gap-2 mb-8">
            <button v-for="cat in categories" :key="cat"
                    @click="activeCategory = cat"
                    :class="['px-5 py-2 rounded-full font-bold text-sm capitalize transition-all shadow-md', 
                             activeCategory === cat ? 'bg-blue-600 text-white ring-2 ring-blue-400 scale-105' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200']">
                {{ cat }}
            </button>
        </div>

        <!-- Grid Frame (Menampilkan Gambar dari Storage) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8 max-w-6xl w-full flex-grow content-start px-2">
            
            <div v-for="frame in filteredFrames" :key="frame.id"
                 @click="selectFrame(frame.id)"
                 :class="['relative bg-gray-900 rounded-2xl overflow-hidden cursor-pointer border-4 transition-all duration-300 group', 
                          form.frame_id === frame.id ? 'border-blue-500 scale-105 shadow-[0_10px_30px_rgba(59,130,246,0.3)]' : 'border-transparent hover:border-gray-700']">
                
                <!-- Wrapper Gambar Frame -->
                <!-- Latar belakang abu-abu terang agar lubang transparan (slot foto) terlihat jelas -->
                <div class="aspect-[2/3] relative bg-gray-300 w-full overflow-hidden flex items-center justify-center">
                    
                    <!-- Gambar Frame Asli (PNG dari storage) -->
                    <!-- Menggunakan object-cover agar pas memenuhi card preview -->
                    <img :src="frame.overlay_path" 
                         :alt="frame.name"
                         class="absolute inset-0 w-full h-full object-cover z-10 transition-transform duration-500 group-hover:scale-105"
                         @error="$event.target.style.display = 'none'" />

                    <!-- Indikator Label Total Pose di pojok bawah -->
                    <div class="absolute bottom-3 left-3 z-20 bg-black/80 backdrop-blur text-white px-3 py-1 rounded-full text-[10px] md:text-xs font-bold border border-gray-700 shadow-lg">
                        {{ frame.total_poses }} Pose
                    </div>
                </div>
                
                <!-- Nama Frame -->
                <div class="p-3 bg-gray-900 text-center relative z-20 border-t border-gray-800">
                    <h3 class="font-bold text-gray-200 text-sm truncate">{{ frame.name }}</h3>
                </div>
                
                <!-- Indikator Centang (Pojok Kanan Atas) -->
                <div v-if="form.frame_id === frame.id" class="absolute top-3 right-3 z-30 bg-blue-500 rounded-full p-1.5 shadow-lg animate-bounce">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

        </div>

        <!-- Tombol Lanjut -->
        <div class="mt-8 h-20 w-full max-w-6xl flex justify-end items-center px-4">
            <button v-if="form.frame_id" 
                    @click="submitFrame" 
                    :disabled="form.processing"
                    class="bg-blue-600 text-white px-8 py-3 md:px-12 md:py-4 rounded-full font-bold text-base md:text-xl hover:bg-blue-500 transition-all shadow-[0_0_20px_rgba(37,99,235,0.4)] hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                <span>{{ form.processing ? 'Menyiapkan Kamera...' : 'Lanjut ke Kamera' }}</span>
                <svg v-if="!form.processing" class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>

    </div>
</template>