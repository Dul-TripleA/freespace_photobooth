<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue";

const props = defineProps({
    session: Object,
    frame: Object,
});

// Referensi Elemen DOM
const videoRef = ref(null);
const previewCanvasRef = ref(null);

// State Webcam & Pemotretan
const stream = ref(null);
const capturedPhotos = ref([]); // Menyimpan Image elements/DataURL
const countdown = ref(0);
const currentPose = ref(1);
const totalPoses = props.frame?.total_poses || 3;
const isSequenceRunning = ref(false);
const isFinished = ref(false);

// State Filter Warna
const selectedFilter = ref("normal");
const filterOptions = [
    { label: "Normal", value: "normal", filterStyle: "none" },
    {
        label: "B&W",
        value: "bw",
        filterStyle: "grayscale(100%) contrast(120%)",
    },
    {
        label: "Vintage",
        value: "vintage",
        filterStyle: "sepia(50%) contrast(90%) brightness(105%)",
    },
    {
        label: "Warm",
        value: "warm",
        filterStyle: "sepia(30%) saturate(140%) brightness(102%)",
    },
    {
        label: "Vivid",
        value: "vivid",
        filterStyle: "saturate(160%) contrast(110%)",
    },
];

// Parser slots_config jika masih bertipe string JSON dari PostgreSQL
const slotsConfig =
    typeof props.frame.slots_config === "string"
        ? JSON.parse(props.frame.slots_config)
        : props.frame.slots_config;

const overlayImage = ref(null);

// 1. Inisialisasi Kamera & Aset Frame Overlay
const initCameraAndAssets = async () => {
    try {
        // Load gambar overlay frame transparan terlebih dahulu
        const img = new Image();
        img.crossOrigin = "anonymous";
        img.src = props.frame.overlay_path;
        await new Promise((resolve, reject) => {
            img.onload = () => {
                overlayImage.value = img;
                resolve();
            };
            img.onerror = () => {
                console.warn(
                    "Gambar frame overlay belum ditemukan di " +
                        props.frame.overlay_path,
                );
                resolve(); // Tetap jalan meskipun gambar belum ada di storage
            };
        });

        // Buka Webcam
        stream.value = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                facingMode: "user",
            },
            audio: false,
        });
        if (videoRef.value) {
            videoRef.value.srcObject = stream.value;
        }

        // Gambar frame kosong pertama kali
        renderFrameCanvas();
    } catch (error) {
        console.error("Gagal inisialisasi:", error);
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach((track) => track.stop());
    }
};

// 2. Fungsi Crop & Draw Image dengan Rasio Cover (Menghindari Foto Gepeng)
const drawCoverImage = (ctx, img, x, y, w, h) => {
    const imgRatio = img.width / img.height;
    const slotRatio = w / h;
    let sx, sy, sw, sh;

    if (imgRatio > slotRatio) {
        sh = img.height;
        sw = img.height * slotRatio;
        sx = (img.width - sw) / 2;
        sy = 0;
    } else {
        sw = img.width;
        sh = img.width / slotRatio;
        sx = 0;
        sy = (img.height - sh) / 2;
    }
    ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
};

// 3. Render Ulang Frame Canvas
const renderFrameCanvas = () => {
    const canvas = previewCanvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");

    const canvasWidth = props.frame.canvas_width || 1200;
    const canvasHeight = props.frame.canvas_height || 1800;

    canvas.width = canvasWidth;
    canvas.height = canvasHeight;

    // Bersihkan Kanvas / Beri background putih dasar
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvasWidth, canvasHeight);

    // Dapatkan style filter yang dipilih
    const activeFilter =
        filterOptions.find((f) => f.value === selectedFilter.value)
            ?.filterStyle || "none";

    // A. Gambar foto yang sudah terisi ke slot masing-masing
    slotsConfig.forEach((slot) => {
        const poseIndex = slot.pose - 1;
        const photoData = capturedPhotos.value[poseIndex];

        if (photoData) {
            ctx.save();
            ctx.filter = activeFilter;
            drawCoverImage(
                ctx,
                photoData,
                slot.x,
                slot.y,
                slot.width,
                slot.height,
            );
            ctx.restore();
        } else {
            // Gambar placeholder kotak abu-abu jika foto pose tersebut belum diambil
            ctx.fillStyle = "#2d3748";
            ctx.fillRect(slot.x, slot.y, slot.width, slot.height);
            ctx.fillStyle = "#718096";
            ctx.font = "bold 30px sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(
                `Pose ${slot.pose}`,
                slot.x + slot.width / 2,
                slot.y + slot.height / 2,
            );
        }
    });

    // B. Gambar Frame Overlay PNG di lapisan teratas (tanpa filter warna agar desain asli tetap tajam)
    if (overlayImage.value) {
        ctx.drawImage(overlayImage.value, 0, 0, canvasWidth, canvasHeight);
    }
};

// 4. Jepret Foto dari Video
const captureCurrentPose = () => {
    const video = videoRef.value;
    const offscreenCanvas = document.createElement("canvas");
    offscreenCanvas.width = video.videoWidth;
    offscreenCanvas.height = video.videoHeight;
    const ctx = offscreenCanvas.getContext("2d");

    // Efek cermin saat jepret
    ctx.translate(offscreenCanvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0);

    const img = new Image();
    img.src = offscreenCanvas.toDataURL("image/jpeg", 0.95);
    img.onload = () => {
        capturedPhotos.value.push(img);
        renderFrameCanvas(); // Perbarui kanvas secara langsung begitu foto masuk
    };
};

// 5. Urutan Hitung Mundur Otomatis
const startCaptureSequence = async () => {
    isSequenceRunning.value = true;
    capturedPhotos.value = [];
    renderFrameCanvas();

    for (let pose = 1; pose <= totalPoses; pose++) {
        currentPose.value = pose;

        // Hitung mundur 4.. 3.. 2.. 1..
        for (let sec = 4; sec > 0; sec--) {
            countdown.value = sec;
            await new Promise((r) => setTimeout(r, 1000));
        }

        countdown.value = 0;
        captureCurrentPose();

        if (pose < totalPoses) {
            await new Promise((r) => setTimeout(r, 1800));
        }
    }

    isSequenceRunning.value = false;
    isFinished.value = true;
};

// 6. Ganti Filter
const changeFilter = (filterKey) => {
    selectedFilter.value = filterKey;
    renderFrameCanvas();
};

// 7. Unggah Hasil Komposit Akhir
const form = useForm({
    composite_image: "",
    raw_photos: [],
    filter: "normal",
});

const submitComposite = () => {
    const canvas = previewCanvasRef.value;
    form.composite_image = canvas.toDataURL("image/jpeg", 0.95);
    form.raw_photos = capturedPhotos.value.map((img) => img.src);
    form.filter = selectedFilter.value;
    form.post(route("kiosk.session.upload", { session: props.session.id }));
};

onMounted(() => {
    initCameraAndAssets();
});

onBeforeUnmount(() => {
    stopCamera();
});
</script>

<template>
    <Head title="Sesi Foto & Frame Preview" />
    <div
        class="h-screen w-full bg-gray-950 text-white flex flex-col select-none overflow-hidden font-sans"
    >
        <!-- Header Info -->
        <header
            class="h-16 shrink-0 border-b border-gray-800 flex items-center justify-between px-4 md:px-8 bg-gray-900/60 backdrop-blur z-20"
        >
            <h1
                class="text-xl md:text-2xl font-black tracking-wider text-blue-500"
            >
                SELFBOOTH
            </h1>
            <div class="flex items-center space-x-2 md:space-x-4">
                <span class="hidden md:inline text-sm text-gray-400"
                    >Frame: {{ frame.name }}</span
                >
                <span
                    class="bg-gray-800 text-white px-3 py-1 md:px-4 md:py-1 rounded-full text-xs md:text-sm font-semibold border border-gray-700"
                >
                    Pose {{ Math.min(currentPose, totalPoses) }} /
                    {{ totalPoses }}
                </span>
            </div>
        </header>

        <!-- Area Konten Utama (Responsif: Atas-Bawah di HP, Kiri-Kanan di PC) -->
        <div
            class="flex-1 flex flex-col lg:flex-row gap-4 p-4 min-h-0 overflow-hidden"
        >
            <!-- Kolom Kiri: Live Webcam View -->
            <div
                class="w-full lg:w-7/12 h-1/2 lg:h-full bg-black rounded-2xl md:rounded-3xl overflow-hidden relative border-2 border-gray-800 flex items-center justify-center shadow-2xl shrink-0 lg:shrink"
            >
                <!-- Video Kamera -->
                <video
                    ref="videoRef"
                    autoplay
                    playsinline
                    style="transform: scaleX(-1)"
                    class="w-full h-full object-cover"
                ></video>

                <!-- Animasi Hitung Mundur -->
                <transition name="scale-fade">
                    <div
                        v-if="countdown > 0"
                        class="absolute inset-0 flex items-center justify-center bg-black/30 backdrop-blur-sm z-30"
                    >
                        <span
                            style="
                                font-size: clamp(6rem, 15vw, 14rem);
                                line-height: 1;
                            "
                            class="font-black text-white drop-shadow-[0_15px_30px_rgba(0,0,0,0.9)] animate-pulse"
                        >
                            {{ countdown }}
                        </span>
                    </div>
                </transition>

                <!-- Tombol Mulai Ambil Foto -->
                <div
                    v-if="!isSequenceRunning && !isFinished"
                    class="absolute bottom-6 z-20"
                >
                    <button
                        @click="startCaptureSequence"
                        class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 md:px-10 md:py-4 rounded-full font-bold text-sm md:text-xl transition-all shadow-[0_0_30px_rgba(37,99,235,0.6)] hover:scale-105 active:scale-95 flex items-center space-x-2 md:space-x-3"
                    >
                        <svg
                            class="w-5 h-5 md:w-7 md:h-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                            ></path>
                        </svg>
                        <span>Mulai Foto (Auto)</span>
                    </button>
                </div>
            </div>

            <!-- Kolom Kanan: Pratinjau Frame Lengkap -->
            <div
                class="w-full lg:w-5/12 h-1/2 lg:h-full bg-gray-900 rounded-2xl md:rounded-3xl p-4 md:p-6 border-2 border-gray-800 flex flex-col items-center justify-between shadow-2xl min-h-0"
            >
                <div
                    class="text-[10px] md:text-xs uppercase tracking-widest text-gray-400 font-bold mb-2 shrink-0"
                >
                    Live Frame Composite
                </div>

                <!-- Wrapper Canvas agar selalu fit di dalam container (object-contain) -->
                <div
                    class="flex-1 w-full flex items-center justify-center min-h-0 overflow-hidden"
                >
                    <canvas
                        ref="previewCanvasRef"
                        style="
                            max-width: 100%;
                            max-height: 100%;
                            object-fit: contain;
                        "
                        class="rounded-xl shadow-2xl border border-gray-700 bg-white"
                    ></canvas>
                </div>

                <!-- Selector Filter Warna & Tombol (Muncul setelah foto selesai) -->
                <div class="w-full shrink-0 mt-4">
                    <div v-if="isFinished" class="w-full mb-4">
                        <div
                            class="text-[10px] md:text-xs text-center text-gray-400 mb-2"
                        >
                            Pilih Filter Warna:
                        </div>
                        <div class="flex justify-center flex-wrap gap-2">
                            <button
                                v-for="filter in filterOptions"
                                :key="filter.value"
                                @click="changeFilter(filter.value)"
                                :class="[
                                    'px-2 py-1 md:px-3 md:py-1.5 rounded-lg text-[10px] md:text-xs font-bold transition-all',
                                    selectedFilter === filter.value
                                        ? 'bg-blue-600 text-white scale-105 ring-2 ring-blue-400'
                                        : 'bg-gray-800 text-gray-300 hover:bg-gray-700',
                                ]"
                            >
                                {{ filter.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Aksi Akhir -->
                    <div class="w-full flex justify-end space-x-2 md:space-x-3">
                        <button
                            v-if="isFinished"
                            @click="startCaptureSequence"
                            class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-3 py-2 md:px-5 md:py-3 rounded-xl font-semibold text-xs md:text-sm transition-colors"
                        >
                            Foto Ulang
                        </button>
                        <button
                            v-if="isFinished"
                            @click="submitComposite"
                            :disabled="form.processing"
                            class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white py-2 md:py-3 rounded-xl font-bold text-xs md:text-sm transition-all shadow-[0_0_20px_rgba(16,185,129,0.4)] disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Menyimpan..."
                                    : "Lanjut & Cetak Foto"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scale-fade-enter-active,
.scale-fade-leave-active {
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.scale-fade-enter-from {
    opacity: 0;
    transform: scale(0.6);
}
.scale-fade-leave-to {
    opacity: 0;
    transform: scale(1.3);
}
</style>
