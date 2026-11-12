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
    is_public: true,
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

// Fungsi Penambah dan Pengurang Slot (Jika tombol + Tambah Slot / x diklik)
const addSlot = () => {
    const nextPose = slotList.value.length + 1;
    slotList.value.push({
        x: 100,
        y: 100,
        pose: nextPose,
        width: 400,
        height: 400,
    });
    form.total_captures = slotList.value.length;
};

const removeSlot = (index) => {
    slotList.value.splice(index, 1);
    // Atur ulang penomoran pose
    slotList.value.forEach((slot, idx) => {
        slot.pose = idx + 1;
    });
    form.total_captures = slotList.value.length;
};

// Preset Tombol Cepat
const applyPreset = (type) => {
    if (type === "2pose") {
        form.total_captures = 2;
        slotList.value = [
            { x: 100, y: 200, pose: 1, width: 1000, height: 600 },
            { x: 100, y: 950, pose: 2, width: 1000, height: 600 },
        ];
    } else if (type === "3pose" || type === "3pose_strip") {
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
    } else if (type === "6pose" || type === "6pose_grid") {
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

// ----------------------------------------------------
// FUNGSI BARU: UNDUH TEMPLATE GUIDE PNG 
// ----------------------------------------------------
const downloadTemplateGuide = () => {
    // 1. Buat elemen canvas sementara
    const canvas = document.createElement("canvas");
    canvas.width = form.canvas_width;
    canvas.height = form.canvas_height;
    const ctx = canvas.getContext("2d");

    // 2. Isi seluruh background dengan warna gelap (abu-abu tua)
    ctx.fillStyle = "#1a1a1a";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // 3. Tambahkan teks penjelasan di canvas (Opsional agar desainer tahu)
    ctx.fillStyle = "#ffffff";
    ctx.font = "bold 40px sans-serif";
    ctx.textAlign = "center";
    ctx.fillText("FRAME TEMPLATE GUIDE", canvas.width / 2, 80);
    ctx.font = "24px sans-serif";
    ctx.fillText("Area biru di bawah adalah area transparan untuk foto", canvas.width / 2, 130);

    // 4. Gambar kotak-kotak (slot) yang harus dilubangi (pakai warna biru kontras)
    slotList.value.forEach((slot, index) => {
        ctx.fillStyle = "#3b82f6"; // Warna biru
        ctx.fillRect(slot.x, slot.y, slot.width, slot.height);

        // Tambahkan nomor pose di tengah kotak biru
        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 60px sans-serif";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(`POSE ${index + 1}`, slot.x + slot.width / 2, slot.y + slot.height / 2);
    });

    // 5. Ubah canvas menjadi file PNG dan paksa unduh
    const dataUrl = canvas.toDataURL("image/png");
    const link = document.createElement("a");
    link.href = dataUrl;
    link.download = `template_guide_${form.total_captures}pose_${form.canvas_width}x${form.canvas_height}.png`;

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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

    <div
        class="relative min-h-screen overflow-hidden bg-[#050505] px-4 py-6 font-sans text-white sm:px-6 sm:py-8 lg:px-10">
        <!-- Editorial background -->
        <div class="pointer-events-none fixed inset-0 opacity-[0.035]"
            style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 100px 100px;">
        </div>
        <div
            class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,0.045),transparent_55%)]">
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[1600px] space-y-8 lg:space-y-10">
            <!-- HEADER -->
            <header
                class="flex flex-col gap-5 border-b border-neutral-800 pb-5 sm:flex-row sm:items-end sm:justify-between sm:pb-6">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2.5">
                        <span class="h-2 w-2 bg-white"></span>
                        <span class="h-2 w-2 bg-white/60"></span>
                        <span class="h-2 w-2 bg-white/30"></span>
                        <span class="ml-2 font-mono text-[9px] uppercase tracking-[0.24em] text-neutral-500">SelfBooth /
                            Administration</span>
                    </div>
                    <p class="mb-1 font-mono text-[10px] italic tracking-[0.18em] text-neutral-500">01 — Studio
                        configuration</p>
                    <h1
                        class="text-3xl font-medium uppercase leading-[0.95] tracking-[-0.055em] sm:text-4xl lg:text-5xl">
                        Manage <span class="text-neutral-500">Frames</span>
                    </h1>
                    <p class="mt-3 max-w-2xl text-xs leading-relaxed text-neutral-400 sm:text-sm">
                        Unggah template PNG transparan dan atur titik koordinat slot foto secara visual.
                    </p>
                </div>

                <a href="/"
                    class="group inline-flex shrink-0 items-center justify-between gap-8 border border-neutral-700 px-4 py-3 font-mono text-[10px] uppercase tracking-[0.16em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">
                    <span>Kembali ke Kiosk</span>
                    <span class="text-base transition-transform group-hover:-translate-x-1">←</span>
                </a>
            </header>

            <!-- FORM + VISUAL PREVIEW -->
            <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-12 xl:gap-6">
                <!-- FORM PANEL -->
                <form @submit.prevent="submitForm"
                    class="space-y-6 border border-neutral-800 bg-[#090909] p-4 shadow-[0_20px_70px_rgba(0,0,0,0.3)] sm:p-6 xl:col-span-7">
                    <div class="flex items-end justify-between gap-4 border-b border-neutral-800 pb-4">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-neutral-500">02 / New asset
                            </p>
                            <h2 class="mt-1 text-xl font-medium uppercase tracking-[-0.04em] sm:text-2xl">Tambah Frame
                                Baru</h2>
                        </div>
                        <span class="font-mono text-[10px] text-neutral-600">PNG / CONFIG</span>
                    </div>

                    <div class="space-y-5">
                        <!-- NAME -->
                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">01 /
                                Nama Frame</label>
                            <input v-model="form.name" type="text" required placeholder="Contoh: Cinema Ticket 6 Pose"
                                class="w-full border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white placeholder:text-neutral-700 focus:border-white focus:outline-none focus:ring-1 focus:ring-white/20" />
                        </div>

                        <!-- IMAGE -->
                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">02 /
                                File Frame Overlay</label>
                            <input type="file" accept="image/png" required @change="handleFileChange"
                                class="w-full border border-neutral-800 bg-[#050505] px-3 py-2.5 text-xs text-neutral-300 file:mr-4 file:border-0 file:bg-white file:px-3 file:py-2 file:font-mono file:text-[9px] file:uppercase file:tracking-[0.12em] file:text-black hover:file:bg-neutral-300" />
                            <p class="font-mono text-[9px] uppercase tracking-[0.12em] text-neutral-600">Wajib
                                menggunakan PNG transparan</p>
                        </div>

                        <!-- CAPTURE COUNT -->
                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">
                                03 / Jumlah Jepretan — {{ form.total_captures }} Pose
                            </label>
                            <select v-model.number="form.total_captures"
                                class="w-full appearance-none border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none">
                                <option :value="2">2 Kali Capture</option>
                                <option :value="3">3 Kali Capture</option>
                                <option :value="4">4 Kali Capture</option>
                                <option :value="5">5 Kali Capture</option>
                                <option :value="6">6 Kali Capture</option>
                            </select>
                            <p class="font-mono text-[9px] leading-relaxed text-neutral-600">
                                Jumlah kotak koordinat otomatis menyesuaikan pilihan capture.
                            </p>
                        </div>

                        <!-- QUICK PRESETS -->
                        <div class="space-y-3 border-y border-neutral-800 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">04 /
                                        Koordinat Slot</p>
                                    <p class="mt-1 text-xs text-neutral-500">{{ slotList.length }} kotak aktif</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="applyPreset('2pose')"
                                        class="border border-neutral-700 px-3 py-2 font-mono text-[9px] uppercase tracking-[0.12em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">Preset
                                        2</button>
                                    <button type="button" @click="applyPreset('3pose')"
                                        class="border border-neutral-700 px-3 py-2 font-mono text-[9px] uppercase tracking-[0.12em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">Preset
                                        3</button>
                                    <button type="button" @click="applyPreset('4pose')"
                                        class="border border-neutral-700 px-3 py-2 font-mono text-[9px] uppercase tracking-[0.12em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">Preset
                                        4</button>
                                    <button type="button" @click="applyPreset('6pose')"
                                        class="border border-neutral-700 px-3 py-2 font-mono text-[9px] uppercase tracking-[0.12em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">Preset
                                        6</button>
                                </div>
                            </div>
                        </div>

                        <!-- CATEGORY -->
                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">05 /
                                Kategori Frame</label>
                            <select v-model="form.category"
                                class="w-full appearance-none border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none">
                                <option value="formal">Formal</option>
                                <option value="pesta">Pesta</option>
                            </select>
                        </div>

                        <!-- VISIBILITY -->
                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">08 /
                                Visibilitas Frame</label>
                            <select v-model="form.is_public"
                                class="w-full appearance-none border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none">
                                <option :value="true">Publik (Muncul untuk semua pengunjung reguler)</option>
                                <option :value="false">Eksklusif (Sembunyi. Hanya muncul jika pakai Voucher Khusus)
                                </option>
                            </select>
                        </div>

                        <!-- CANVAS SIZE -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label
                                    class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">06 /
                                    Lebar Kanvas (px)</label>
                                <input v-model.number="form.canvas_width" type="number"
                                    class="w-full border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none" />
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">07 /
                                    Tinggi Kanvas (px)</label>
                                <input v-model.number="form.canvas_height" type="number"
                                    class="w-full border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none" />
                            </div>
                        </div>

                        <!-- SLOT COORDINATES -->
                        <div class="space-y-3 border-t border-neutral-800 pt-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">08 /
                                        Koordinat Lubang Pose</p>
                                    <p class="mt-1 text-xs text-neutral-500">{{ slotList.length }} pose terdaftar</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="applyPreset('6pose_grid')"
                                        class="border border-neutral-700 px-2.5 py-2 font-mono text-[9px] uppercase tracking-[0.08em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">6
                                        Pose Grid</button>
                                    <button type="button" @click="applyPreset('3pose_strip')"
                                        class="border border-neutral-700 px-2.5 py-2 font-mono text-[9px] uppercase tracking-[0.08em] text-neutral-300 transition hover:border-white hover:bg-white hover:text-black">3
                                        Pose Strip</button>
                                    <button type="button" @click="addSlot"
                                        class="border border-white bg-white px-2.5 py-2 font-mono text-[9px] font-bold uppercase tracking-[0.08em] text-black transition hover:bg-neutral-300">+
                                        Tambah Slot</button>
                                </div>
                            </div>

                            <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                                <div v-for="(slot, idx) in slotList" :key="idx"
                                    class="grid grid-cols-12 items-end gap-2 border border-neutral-800 bg-[#050505] p-3 text-xs transition hover:border-neutral-600">
                                    <span
                                        class="col-span-12 font-mono text-[9px] uppercase tracking-[0.16em] text-white sm:col-span-2">Pose
                                        / {{ String(idx + 1).padStart(2, '0') }}</span>
                                    <div class="col-span-3 sm:col-span-2">
                                        <label
                                            class="mb-1 block font-mono text-[8px] uppercase tracking-wider text-neutral-600">X</label>
                                        <input v-model.number="slot.x" type="number"
                                            class="w-full min-w-0 border border-neutral-800 bg-neutral-900 px-2 py-2 text-xs text-white focus:border-white focus:outline-none" />
                                    </div>
                                    <div class="col-span-3 sm:col-span-2">
                                        <label
                                            class="mb-1 block font-mono text-[8px] uppercase tracking-wider text-neutral-600">Y</label>
                                        <input v-model.number="slot.y" type="number"
                                            class="w-full min-w-0 border border-neutral-800 bg-neutral-900 px-2 py-2 text-xs text-white focus:border-white focus:outline-none" />
                                    </div>
                                    <div class="col-span-3 sm:col-span-3">
                                        <label
                                            class="mb-1 block font-mono text-[8px] uppercase tracking-wider text-neutral-600">Width</label>
                                        <input v-model.number="slot.width" type="number"
                                            class="w-full min-w-0 border border-neutral-800 bg-neutral-900 px-2 py-2 text-xs text-white focus:border-white focus:outline-none" />
                                    </div>
                                    <div class="col-span-2 sm:col-span-2">
                                        <label
                                            class="mb-1 block font-mono text-[8px] uppercase tracking-wider text-neutral-600">Height</label>
                                        <input v-model.number="slot.height" type="number"
                                            class="w-full min-w-0 border border-neutral-800 bg-neutral-900 px-2 py-2 text-xs text-white focus:border-white focus:outline-none" />
                                    </div>
                                    <button type="button" @click="removeSlot(idx)"
                                        class="col-span-1 border border-neutral-800 py-2 text-center font-mono text-xs text-neutral-500 transition hover:border-red-500 hover:bg-red-500/10 hover:text-red-400">×</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit" :disabled="form.processing"
                        class="group flex w-full items-center justify-between border border-white bg-white px-5 py-4 text-left text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50">
                        <span>
                            <span class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-600">Save
                                configuration</span>
                            <span class="mt-1 block text-sm font-bold uppercase tracking-[0.08em]">{{ form.processing ?
                                "Menyimpan..." : "Simpan Frame Baru" }}</span>
                        </span>
                        <span class="text-xl transition-transform group-hover:translate-x-1">→</span>
                    </button>
                </form>

                <!-- VISUAL PREVIEW PANEL -->
                <aside
                    class="flex min-w-0 flex-col border border-neutral-800 bg-[#090909] p-4 shadow-[0_20px_70px_rgba(0,0,0,0.3)] sm:p-6 xl:col-span-5">
                    <div class="mb-5 flex items-start justify-between gap-3 border-b border-neutral-800 pb-4">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-neutral-500">03 / Layout
                                mapping</p>
                            <h2 class="mt-1 text-xl font-medium uppercase tracking-[-0.04em] sm:text-2xl">Pratinjau Slot
                            </h2>
                        </div>
                        <span
                            class="border border-neutral-700 px-2 py-1 font-mono text-[9px] tabular-nums text-neutral-400">
                            {{ form.canvas_width }} × {{ form.canvas_height }} px
                        </span>
                    </div>

                    <div class="relative mx-auto w-full max-w-[340px] overflow-hidden border border-neutral-700 bg-[#050505] shadow-[0_18px_55px_rgba(0,0,0,0.55)]"
                        :style="{ aspectRatio: `${form.canvas_width} / ${form.canvas_height}` }">
                        <div class="pointer-events-none absolute inset-0 opacity-[0.08]"
                            style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 24px 24px;">
                        </div>

                        <!-- Slot position indicators -->
                        <div v-for="(slot, index) in slotList" :key="index"
                            class="absolute flex flex-col items-center justify-center border border-white bg-white/15 text-center text-[9px] font-bold text-white shadow-[0_0_18px_rgba(255,255,255,0.08)] sm:text-xs"
                            :style="{
                                left: `${(slot.x / form.canvas_width) * 100}%`,
                                top: `${(slot.y / form.canvas_height) * 100}%`,
                                width: `${(slot.width / form.canvas_width) * 100}%`,
                                height: `${(slot.height / form.canvas_height) * 100}%`,
                            }">
                            <span class="font-mono uppercase tracking-[0.12em]">Pose #{{ index + 1 }}</span>
                            <span class="mt-1 font-mono text-[8px] font-normal text-neutral-300 sm:text-[9px]">{{
                                slot.width }} × {{ slot.height }}</span>
                        </div>

                        <!-- PNG frame overlay -->
                        <img v-if="previewImage" :src="previewImage" alt="Preview Frame"
                            class="pointer-events-none absolute inset-0 z-10 h-full w-full object-contain" />

                        <div v-if="!previewImage"
                            class="pointer-events-none absolute inset-0 z-20 flex items-end justify-center p-3">
                            <span class="font-mono text-[8px] uppercase tracking-[0.18em] text-neutral-600">Overlay
                                preview / awaiting PNG</span>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-neutral-800 pt-4">
                        <button type="button" @click="downloadTemplateGuide"
                            class="group flex w-full items-center justify-between border border-neutral-700 px-4 py-3 text-left transition hover:border-white hover:bg-white hover:text-black">
                            <span>
                                <span
                                    class="block font-mono text-[9px] uppercase tracking-[0.16em] text-neutral-500 group-hover:text-neutral-600">Template
                                    guide</span>
                                <span class="mt-1 block text-xs font-semibold uppercase tracking-[0.04em]">Unduh Guide
                                    PNG</span>
                                <span
                                    class="mt-1 block font-mono text-[9px] text-neutral-500 group-hover:text-neutral-600">{{
                                        form.canvas_width }} × {{ form.canvas_height }} px</span>
                            </span>
                            <span class="text-xl">↓</span>
                        </button>
                        <p class="mt-3 text-[10px] leading-relaxed text-neutral-500">
                            Masukkan gambar panduan ke layer paling bawah di <strong
                                class="font-medium text-neutral-300">Photoshop / Canva / Figma</strong>. Lubangi area
                            kotak sebelum mengekspor desain sebagai PNG transparan.
                        </p>
                    </div>
                </aside>
            </div>

            <!-- FRAME LIBRARY -->
            <section class="border border-neutral-800 bg-[#090909] p-4 shadow-[0_20px_70px_rgba(0,0,0,0.3)] sm:p-6">
                <div
                    class="mb-5 flex flex-col gap-2 border-b border-neutral-800 pb-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-neutral-500">04 / Asset library
                        </p>
                        <h2 class="mt-1 text-xl font-medium uppercase tracking-[-0.04em] sm:text-2xl">Daftar Frame Aktif
                        </h2>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-neutral-500">{{ frames.length }}
                        frame tersimpan</span>
                </div>

                <div v-if="frames.length > 0"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
                    <article v-for="frame in frames" :key="frame.id"
                        class="group flex min-w-0 flex-col justify-between border border-neutral-800 bg-[#050505] transition hover:border-neutral-500">
                        <div class="p-3 sm:p-4">
                            <div
                                class="relative mb-3 flex aspect-[2/3] items-center justify-center overflow-hidden border border-neutral-800 bg-neutral-950 p-2">
                                <img v-if="frame.preview_url" :src="frame.preview_url" :alt="frame.name"
                                    class="max-h-full w-auto object-contain transition duration-500 group-hover:scale-[1.025]" />
                                <span
                                    class="absolute left-2 top-2 font-mono text-[8px] uppercase tracking-[0.14em] text-neutral-600">Frame
                                    / {{ frame.id }}</span>
                            </div>
                            <h3 class="truncate text-xs font-semibold uppercase tracking-[0.04em] text-neutral-100">{{
                                frame.name || frame.title }}</h3>
                            <p class="mt-1 font-mono text-[9px] uppercase tracking-[0.12em] text-neutral-500">
                                {{ frame.pose_count || frame.total_poses || 6 }} Pose
                                <span class="mx-1">•</span>
                                <span :class="frame.is_public ? 'text-emerald-400' : 'text-amber-400'">
                                    {{ frame.is_public ? 'PUBLIK' : 'EKSKLUSIF' }}
                                </span>
                            </p>
                        </div>

                        <div class="flex flex-col border-t border-neutral-800">
                            <a v-if="frame.preview_url" :href="frame.preview_url" target="_blank"
                                :download="`${frame.name || 'frame'}.png`"
                                class="flex items-center justify-between px-3 py-2.5 font-mono text-[9px] uppercase tracking-[0.1em] text-neutral-400 transition hover:bg-white hover:text-black sm:px-4">
                                <span>Unduh PNG Asli</span><span>↗</span>
                            </a>
                            <button @click="deleteFrame(frame.id)"
                                class="flex items-center justify-between border-t border-neutral-800 px-3 py-2.5 text-left font-mono text-[9px] uppercase tracking-[0.1em] text-neutral-500 transition hover:bg-red-500/10 hover:text-red-400 sm:px-4">
                                <span>Hapus Frame</span><span>×</span>
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else
                    class="flex min-h-36 flex-col items-center justify-center border border-dashed border-neutral-800 px-4 py-8 text-center">
                    <span class="font-mono text-2xl text-neutral-700">—</span>
                    <p class="mt-2 text-xs text-neutral-500">Belum ada frame yang tersimpan.</p>
                </div>
            </section>

            <footer
                class="flex flex-col gap-2 border-t border-neutral-800 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <span class="font-mono text-[8px] uppercase tracking-[0.2em] text-neutral-600">SelfBooth / Frame
                    management</span>
                <span class="font-mono text-[8px] uppercase tracking-[0.2em] text-neutral-600">Urban editorial
                    interface</span>
            </footer>
        </div>
    </div>
</template>
