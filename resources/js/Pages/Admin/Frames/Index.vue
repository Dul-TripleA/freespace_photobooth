<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    frames: {
        type: Array,
        default: () => [],
    },
});

const previewImage = ref(null);

const slotList = ref([
    { x: 100, y: 100, pose: 1, width: 450, height: 500 },
    { x: 650, y: 100, pose: 2, width: 450, height: 500 },
]);

const form = useForm({
    name: "",
    category: "formal",
    image: null,
    total_captures: 2,
    canvas_width: 1200,
    canvas_height: 1800,
    slots: JSON.stringify(slotList.value),
});

// Otomatis sesuaikan jumlah kotak slot saat dropdown total_captures diubah
watch(
    () => form.total_captures,
    (newCount) => {
        const count = Number(newCount) || 2;
        if (slotList.value.length < count) {
            // Tambah slot jika kurang
            while (slotList.value.length < count) {
                const nextPose = slotList.value.length + 1;
                slotList.value.push({
                    x: 100,
                    y: 100 + (nextPose - 1) * 280,
                    pose: nextPose,
                    width: 450,
                    height: 350,
                });
            }
        } else if (slotList.value.length > count) {
            // Potong slot jika lebih
            slotList.value = slotList.value.slice(0, count);
        }
    }
);

watch(
    slotList,
    (newVal) => {
        form.slots = JSON.stringify(newVal);
    },
    { deep: true }
);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.image = file;

    const reader = new FileReader();
    reader.onload = (ev) => {
        previewImage.value = ev.target.result;
        const img = new Image();
        img.onload = () => {
            form.canvas_width = img.naturalWidth;
            form.canvas_height = img.naturalHeight;
        };
        img.src = ev.target.result;
    };
    reader.readAsDataURL(file);
};

// Preset Tombol Cepat
const applyPreset = (type) => {
    if (type === "2pose") {
        form.total_captures = 2;
        slotList.value = [
            { x: 100, y: 200, pose: 1, width: 1000, height: 600 },
            { x: 100, y: 950, pose: 2, width: 1000, height: 600 },
        ];
    } else if (type === "3pose") {
        form.total_captures = 3;
        slotList.value = [
            { x: 100, y: 120, pose: 1, width: 1000, height: 480 },
            { x: 100, y: 660, pose: 2, width: 1000, height: 480 },
            { x: 100, y: 1200, pose: 3, width: 1000, height: 480 },
        ];
    } else if (type === "4pose") {
        form.total_captures = 4;
        slotList.value = [
            { x: 80, y: 150, pose: 1, width: 500, height: 700 },
            { x: 620, y: 150, pose: 2, width: 500, height: 700 },
            { x: 80, y: 920, pose: 3, width: 500, height: 700 },
            { x: 620, y: 920, pose: 4, width: 500, height: 700 },
        ];
    } else if (type === "6pose") {
        form.total_captures = 6;
        slotList.value = [
            { x: 60, y: 150, pose: 1, width: 510, height: 450 },
            { x: 630, y: 150, pose: 2, width: 510, height: 450 },
            { x: 60, y: 670, pose: 3, width: 510, height: 450 },
            { x: 630, y: 670, pose: 4, width: 510, height: 450 },
            { x: 60, y: 1190, pose: 5, width: 510, height: 450 },
            { x: 630, y: 1190, pose: 6, width: 510, height: 450 },
        ];
    }
};

const submitForm = () => {
    form.post("/admin/frames", {
        forceFormData: true,
        onSuccess: () => {
            form.reset("name", "image");
            previewImage.value = null;
        },
    });
};

const deleteFrame = (id) => {
    if (confirm("Yakin ingin menghapus frame ini?")) {
        router.delete(`/admin/frames/${id}`);
    }
};
</script>

<template>

    <Head title="Kelola Frame - Admin SelfBooth" />

    <div class="min-h-screen bg-neutral-950 text-white py-10 px-4 sm:px-8">
        <div class="max-w-6xl mx-auto space-y-10">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-neutral-800 pb-6">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight">Kelola Frame Photobooth</h1>
                    <p class="text-sm text-neutral-400 mt-1">
                        Unggah template PNG transparan dan atur titik koordinat slot foto secara visual.
                    </p>
                </div>
                <a href="/"
                    class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-sm font-semibold rounded-xl transition">
                    ← Kembali ke Kiosk
                </a>
            </div>

            <!-- Form Tambah Frame & Live Preview -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Kolom Kiri: Form Input (7 Kolom) -->
                <form @submit.prevent="submitForm"
                    class="lg:col-span-7 bg-neutral-900 border border-neutral-800 rounded-2xl p-6 space-y-6 shadow-xl">
                    <h2 class="text-xl font-bold">Tambah Frame Baru</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                                Nama Frame
                            </label>
                            <input v-model="form.name" type="text" required placeholder="Contoh: Cinema Ticket 6 Pose"
                                class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-amber-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                                File Frame Overlay (Wajib PNG Transparan)
                            </label>
                            <input type="file" accept="image/png" required @change="handleFileChange"
                                class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-4 py-2.5 text-sm text-neutral-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-neutral-950 file:font-bold hover:file:bg-amber-400" />
                        </div>

                        <!-- Pilihan Jumlah Jepretan Kamera -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                                Jumlah Jepretan Kamera (Total Capture: {{ form.total_captures }} Pose)
                            </label>
                            <select v-model.number="form.total_captures"
                                class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-amber-500 focus:outline-none">
                                <option :value="2">2 Kali Capture</option>
                                <option :value="3">3 Kali Capture</option>
                                <option :value="4">4 Kali Capture</option>
                                <option :value="5">5 Kali Capture</option>
                                <option :value="6">6 Kali Capture</option>
                            </select>
                            <p class="text-[11px] text-neutral-500 mt-1">
                                Jumlah kotak koordinat di bawah akan otomatis menyesuaikan dengan pilihan angka di atas.
                            </p>
                        </div>

                        <!-- Tombol Preset Cepat -->
                        <div class="flex items-center justify-between mb-3 pt-2">
                            <label class="text-xs font-semibold uppercase tracking-wider text-neutral-400">
                                Koordinat Slot ({{ slotList.length }} Kotak Aktif)
                            </label>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="applyPreset('2pose')"
                                    class="text-[11px] px-2 py-1 bg-neutral-800 hover:bg-neutral-700 rounded-lg text-amber-400 font-medium">Preset
                                    2</button>
                                <button type="button" @click="applyPreset('3pose')"
                                    class="text-[11px] px-2 py-1 bg-neutral-800 hover:bg-neutral-700 rounded-lg text-amber-400 font-medium">Preset
                                    3</button>
                                <button type="button" @click="applyPreset('4pose')"
                                    class="text-[11px] px-2 py-1 bg-neutral-800 hover:bg-neutral-700 rounded-lg text-amber-400 font-medium">Preset
                                    4</button>
                                <button type="button" @click="applyPreset('6pose')"
                                    class="text-[11px] px-2 py-1 bg-neutral-800 hover:bg-neutral-700 rounded-lg text-amber-400 font-medium">Preset
                                    6</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                                Kategori Frame
                            </label>
                            <select v-model="form.category"
                                class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-amber-500 focus:outline-none">
                                <option value="formal">Formal</option>
                                <option value="pesta">Pesta</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                                    Lebar Kanvas (px)
                                </label>
                                <input v-model.number="form.canvas_width" type="number"
                                    class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-4 py-2.5 text-white" />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold uppercase tracking-wider text-neutral-400 mb-2">
                                    Tinggi Kanvas (px)
                                </label>
                                <input v-model.number="form.canvas_height" type="number"
                                    class="w-full bg-neutral-950 border border-neutral-800 rounded-xl px-4 py-2.5 text-white" />
                            </div>
                        </div>

                        <!-- Preset Cepat -->
                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-xs font-semibold uppercase tracking-wider text-neutral-400">
                                    Koordinat Lubang Pose ({{ slotList.length }} Pose)
                                </label>
                                <div class="flex gap-2">
                                    <button type="button" @click="applyPreset('6pose_grid')"
                                        class="text-xs px-2.5 py-1 bg-neutral-800 hover:bg-neutral-700 rounded-lg text-amber-400 font-medium">
                                        Preset 6 Pose (Grid)
                                    </button>
                                    <button type="button" @click="applyPreset('3pose_strip')"
                                        class="text-xs px-2.5 py-1 bg-neutral-800 hover:bg-neutral-700 rounded-lg text-amber-400 font-medium">
                                        Preset 3 Pose
                                    </button>
                                    <button type="button" @click="addSlot"
                                        class="text-xs px-2.5 py-1 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 rounded-lg font-bold">
                                        + Tambah Slot
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                                <div v-for="(slot, idx) in slotList" :key="idx"
                                    class="grid grid-cols-12 gap-2 items-center bg-neutral-950 p-2.5 rounded-xl border border-neutral-800 text-xs">
                                    <span class="col-span-2 font-bold text-amber-400">Pose #{{ idx + 1 }}</span>
                                    <div class="col-span-2">
                                        <span class="text-neutral-500 block">X</span>
                                        <input v-model.number="slot.x" type="number"
                                            class="w-full bg-neutral-900 border border-neutral-800 rounded px-2 py-1" />
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-neutral-500 block">Y</span>
                                        <input v-model.number="slot.y" type="number"
                                            class="w-full bg-neutral-900 border border-neutral-800 rounded px-2 py-1" />
                                    </div>
                                    <div class="col-span-3">
                                        <span class="text-neutral-500 block">Width</span>
                                        <input v-model.number="slot.width" type="number"
                                            class="w-full bg-neutral-900 border border-neutral-800 rounded px-2 py-1" />
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-neutral-500 block">Height</span>
                                        <input v-model.number="slot.height" type="number"
                                            class="w-full bg-neutral-900 border border-neutral-800 rounded px-2 py-1" />
                                    </div>
                                    <button type="button" @click="removeSlot(idx)"
                                        class="col-span-1 text-red-400 hover:text-red-300 font-bold text-center mt-3">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-neutral-950 font-extrabold rounded-xl transition shadow-lg">
                        {{ form.processing ? "Menyimpan..." : "Simpan Frame Baru" }}
                    </button>
                </form>

                <!-- Kolom Kanan: Live Visual Preview & Download Template Guide (5 Kolom) -->
                <div
                    class="lg:col-span-5 bg-neutral-900 border border-neutral-800 rounded-2xl p-6 flex flex-col items-center shadow-xl">
                    <div class="w-full flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-400">
                            Pratinjau Posisi Slot Foto
                        </h2>
                        <span
                            class="text-xs font-mono text-neutral-400 bg-neutral-950 px-2.5 py-1 rounded-md border border-neutral-800">
                            {{ form.canvas_width }} × {{ form.canvas_height }} px
                        </span>
                    </div>

                    <div class="relative w-full max-w-[320px] bg-neutral-950 border border-neutral-800 rounded-lg overflow-hidden shadow-inner"
                        :style="{ aspectRatio: `${form.canvas_width} / ${form.canvas_height}` }">
                        <!-- Kotak Indikator Slot Pose -->
                        <div v-for="(slot, index) in slotList" :key="index"
                            class="absolute bg-emerald-500/30 border-2 border-emerald-400 flex flex-col items-center justify-center text-xs font-extrabold text-white shadow"
                            :style="{
                                left: `${(slot.x / form.canvas_width) * 100}%`,
                                top: `${(slot.y / form.canvas_height) * 100}%`,
                                width: `${(slot.width / form.canvas_width) * 100}%`,
                                height: `${(slot.height / form.canvas_height) * 100}%`,
                            }">
                            <span>Pose #{{ index + 1 }}</span>
                            <span class="text-[10px] font-mono text-emerald-200 font-normal">
                                {{ slot.width }}×{{ slot.height }}
                            </span>
                        </div>

                        <!-- Gambar PNG Frame di Atasnya -->
                        <img v-if="previewImage" :src="previewImage" alt="Preview Frame"
                            class="absolute inset-0 w-full h-full object-contain pointer-events-none z-10" />
                    </div>

                    <!-- Tombol Download Template Guide untuk Photoshop / Canva -->
                    <button type="button" @click="downloadTemplateGuide"
                        class="mt-5 w-full py-3 px-4 bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow">
                        <span>📥</span> Unduh Template Guide ({{ form.canvas_width }}×{{ form.canvas_height }} PNG)
                    </button>

                    <p class="text-xs text-neutral-500 mt-3 text-center leading-relaxed">
                        Unduh gambar panduan di atas lalu masukkan ke layer paling bawah di <strong>Photoshop / Canva /
                            Figma</strong>. Lubangi area kotak hijau sebelum mengekspor desain Anda ke PNG Transparan.
                    </p>
                </div>
            </div>

            <!-- Daftar Frame yang Sudah Ada -->
            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-xl font-bold mb-6">Daftar Frame Aktif ({{ frames.length }})</h2>

                <div v-if="frames.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <div v-for="frame in frames" :key="frame.id"
                        class="bg-neutral-950 border border-neutral-800 rounded-xl p-4 flex flex-col justify-between">
                        <div>
                            <div
                                class="aspect-[2/3] bg-neutral-900 rounded-lg overflow-hidden mb-3 flex items-center justify-center p-2">
                                <img v-if="frame.preview_url" :src="frame.preview_url" :alt="frame.name"
                                    class="max-h-full w-auto object-contain" />
                            </div>
                            <h3 class="font-bold text-sm truncate">{{ frame.name || frame.title }}</h3>
                            <p class="text-xs text-neutral-400 mt-0.5">
                                {{ frame.pose_count || frame.total_poses || 6 }} Pose
                            </p>
                        </div>

                        <div class="mt-4 flex flex-col gap-2">
                            <a v-if="frame.preview_url" :href="frame.preview_url" target="_blank"
                                :download="`${frame.name || 'frame'}.png`"
                                class="w-full py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-200 text-xs font-bold text-center rounded-lg transition">
                                Unduh PNG Asli
                            </a>
                            <button @click="deleteFrame(frame.id)"
                                class="w-full py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-bold rounded-lg border border-red-500/20 transition">
                                Hapus Frame
                            </button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-neutral-500">Belum ada frame yang tersimpan.</p>
            </div>
        </div>
    </div>
</template>