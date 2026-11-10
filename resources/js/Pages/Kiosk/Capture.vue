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



// State & Variabel Perekaman Live Photo (Video Pendek tiap Pose)

const livePhotos = ref([]);

let mediaRecorder = null;

let recordedChunks = [];



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

        await new Promise((resolve) => {

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



// 4. Fungsi Perekaman Live Photo (WebM)

const getSupportedMimeType = () => {

    const types = [

        "video/webm;codecs=vp9",

        "video/webm;codecs=vp8",

        "video/webm",

        "video/mp4",

    ];

    return types.find((t) => MediaRecorder.isTypeSupported(t)) || "";

};



const startLivePhotoRecording = (mediaStream) => {

    if (!mediaStream || typeof MediaRecorder === "undefined") return;

    recordedChunks = [];



    try {

        const mimeType = getSupportedMimeType();

        mediaRecorder = mimeType

            ? new MediaRecorder(mediaStream, { mimeType })

            : new MediaRecorder(mediaStream);



        mediaRecorder.ondataavailable = (event) => {

            if (event.data && event.data.size > 0) {

                recordedChunks.push(event.data);

            }

        };



        mediaRecorder.start();

    } catch (err) {

        console.warn("Gagal memulai rekaman Live Photo:", err);

    }

};



const stopLivePhotoRecording = (poseIndex) => {

    return new Promise((resolve) => {

        if (!mediaRecorder || mediaRecorder.state === "inactive") {

            resolve();

            return;

        }



        mediaRecorder.onstop = () => {

            const mimeType = mediaRecorder.mimeType || "video/webm";

            const blob = new Blob(recordedChunks, { type: mimeType });

            // Buat objek File agar diterima oleh validasi file Laravel

            const ext = mimeType.includes("mp4") ? "mp4" : "webm";

            const file = new File([blob], `live_pose_${poseIndex + 1}.${ext}`, {

                type: mimeType,

            });

            livePhotos.value[poseIndex] = file;

            resolve();

        };



        mediaRecorder.stop();

    });

};



// 5. Jepret Foto dari Video

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



// 6. Urutan Hitung Mundur Otomatis + Perekaman Live Photo

const startCaptureSequence = async () => {

    isSequenceRunning.value = true;

    isFinished.value = false;

    capturedPhotos.value = [];

    livePhotos.value = [];

    renderFrameCanvas();



    for (let pose = 1; pose <= totalPoses; pose++) {

        currentPose.value = pose;



        // Hitung mundur 4.. 3.. 2.. 1..

        for (let sec = 4; sec > 0; sec--) {

            countdown.value = sec;



            // Mulai merekam Live Photo 2 detik sebelum kamera memotret

            if (sec === 2 && stream.value) {

                startLivePhotoRecording(stream.value);

            }



            await new Promise((r) => setTimeout(r, 1000));

        }



        countdown.value = 0;

        captureCurrentPose();



        // Rekam tambahan 400ms setelah jepret agar gerakan akhir tertangkap halus, lalu hentikan

        await new Promise((r) => setTimeout(r, 400));

        await stopLivePhotoRecording(pose - 1);



        if (pose < totalPoses) {

            await new Promise((r) => setTimeout(r, 1400));

        }

    }



    isSequenceRunning.value = false;

    isFinished.value = true;

};



// 7. Ganti Filter

const changeFilter = (filterKey) => {

    selectedFilter.value = filterKey;

    renderFrameCanvas();

};



// 8. Unggah Hasil Komposit Akhir + Foto Mentah + Video Live Photo

const form = useForm({

    composite_image: "",

    raw_photos: [],

    live_photos: [],

    filter: "normal",

});



const submitComposite = () => {

    const canvas = previewCanvasRef.value;

    form.composite_image = canvas.toDataURL("image/jpeg", 0.95);

    form.raw_photos = capturedPhotos.value.map((img) => img.src);

    form.live_photos = livePhotos.value;

    form.filter = selectedFilter.value;



    form.post(route("kiosk.session.upload", { session: props.session.id }), {

        forceFormData: true, // Wajib agar file Blob/File video terkirim sebagai multipart/form-data

    });

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
        class="fixed inset-0 h-screen w-screen overflow-hidden bg-[#080808] font-sans text-white select-none"
    >
        <!-- Ambient editorial background -->
        <div class="pointer-events-none absolute inset-0 z-0 opacity-[0.045]">
            <div
                class="absolute inset-0"
                style="
                    background-image:
                        linear-gradient(to right, #ffffff 1px, transparent 1px),
                        linear-gradient(to bottom, #ffffff 1px, transparent 1px);
                    background-size: 110px 110px;
                "
            ></div>
        </div>
        <div
            class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_50%_45%,transparent_0%,#080808_82%)]"
        ></div>

        <!-- Editorial header -->
        <header
            class="relative z-10 flex h-[68px] shrink-0 items-center justify-between border-b border-neutral-800 px-4 sm:px-6 lg:px-8"
        >
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <div class="flex shrink-0 items-center gap-1.5" aria-hidden="true">
                    <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                </div>
                <div class="min-w-0 border-l border-neutral-700 pl-3 sm:pl-4">
                    <p class="font-mono text-[9px] leading-none tracking-[0.18em] text-neutral-500 italic sm:text-[10px]">
                        EXCLUSIVE PHOTO EXPERIENCE
                    </p>
                    <h1 class="mt-1 truncate text-sm leading-none font-medium tracking-[0.16em] text-white sm:text-base">
                        SELFBOOTH
                    </h1>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 sm:gap-6">
                <div class="hidden text-right sm:block">
                    <p class="font-mono text-[9px] tracking-[0.18em] text-neutral-500">
                        SELECTED FRAME
                    </p>
                    <p class="mt-1 max-w-44 truncate text-xs tracking-wide text-neutral-200">
                        {{ frame.name }}
                    </p>
                </div>
                <div class="flex items-center gap-2 border border-neutral-700 px-2.5 py-1.5 sm:px-3">
                    <span class="font-mono text-[9px] tracking-[0.15em] text-neutral-500 sm:text-[10px]">
                        POSE
                    </span>
                    <span class="font-mono text-xs font-medium tabular-nums text-white sm:text-sm">
                        {{ String(Math.min(currentPose, totalPoses)).padStart(2, '0') }}
                        <span class="text-neutral-600">/</span>
                        {{ String(totalPoses).padStart(2, '0') }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Main kiosk workspace: fixed viewport, no page scrolling -->
        <main
            class="relative z-10 mx-auto grid h-[calc(100vh-68px)] w-full max-w-[1800px] grid-rows-[minmax(0,1.08fr)_minmax(0,0.92fr)] gap-3 p-3 sm:gap-4 sm:p-4 lg:grid-cols-[minmax(0,1.58fr)_minmax(320px,0.82fr)] lg:grid-rows-1 lg:px-6 lg:py-5 xl:px-8"
        >
            <!-- 01. Live camera -->
            <section
                class="group relative min-h-0 overflow-hidden border border-neutral-700 bg-black"
                aria-label="Live camera preview"
            >
                <video
                    ref="videoRef"
                    autoplay
                    playsinline
                    style="transform: scaleX(-1)"
                    class="h-full w-full object-cover"
                ></video>

                <!-- Camera overlays -->
                <div
                    class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/65 via-transparent to-black/70"
                ></div>
                <div class="pointer-events-none absolute inset-0 border-[10px] border-transparent sm:border-[14px]">
                    <div class="absolute left-0 top-0 h-5 w-5 border-l border-t border-white/45 sm:h-7 sm:w-7"></div>
                    <div class="absolute right-0 top-0 h-5 w-5 border-r border-t border-white/45 sm:h-7 sm:w-7"></div>
                    <div class="absolute bottom-0 left-0 h-5 w-5 border-b border-l border-white/45 sm:h-7 sm:w-7"></div>
                    <div class="absolute right-0 bottom-0 h-5 w-5 border-r border-b border-white/45 sm:h-7 sm:w-7"></div>
                </div>

                <div class="absolute top-3 right-3 left-3 flex items-start justify-between gap-3 sm:top-5 sm:right-5 sm:left-5">
                    <div>
                        <p class="font-mono text-[9px] tracking-[0.2em] text-white/70 sm:text-[10px]">
                            01 / LIVE VIEW
                        </p>
                        <p class="mt-1 text-[10px] font-medium tracking-[0.12em] text-white sm:text-xs">
                            CAMERA PREVIEW
                        </p>
                    </div>
                    <span
                        class="border border-white/35 bg-black/45 px-2 py-1 font-mono text-[8px] tracking-[0.16em] text-white/85 backdrop-blur-sm sm:text-[9px]"
                    >
                        {{ isFinished ? 'CAPTURE COMPLETE' : isSequenceRunning ? 'CAPTURING' : 'READY' }}
                    </span>
                </div>

                <!-- Countdown animation -->
                <transition name="scale-fade">
                    <div
                        v-if="countdown > 0"
                        class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/55 backdrop-blur-[2px]"
                    >
                        <span class="mb-2 font-mono text-[9px] tracking-[0.28em] text-white/65 sm:text-xs">
                            POSE {{ String(currentPose).padStart(2, '0') }} / {{ String(totalPoses).padStart(2, '0') }}
                        </span>
                        <span
                            style="font-size: clamp(6rem, 15vw, 14rem); line-height: 0.9"
                            class="animate-pulse font-light tracking-[-0.08em] text-white drop-shadow-[0_15px_30px_rgba(0,0,0,0.9)]"
                        >
                            {{ countdown }}
                        </span>
                    </div>
                </transition>

                <!-- Start capture action -->
                <div
                    v-if="!isSequenceRunning && !isFinished"
                    class="absolute right-3 bottom-3 left-3 z-20 flex justify-center sm:right-5 sm:bottom-5 sm:left-auto"
                >
                    <button
                        @click="startCaptureSequence"
                        class="flex w-full items-center justify-center gap-3 border border-white bg-white px-5 py-3 text-[10px] font-semibold tracking-[0.18em] text-black transition-all duration-300 hover:bg-neutral-200 active:scale-[0.98] sm:w-auto sm:px-7 sm:py-3.5 sm:text-xs"
                    >
                        <svg
                            class="h-4 w-4 sm:h-5 sm:w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span>MULAI FOTO (AUTO)</span>
                        <span class="text-base leading-none">→</span>
                    </button>
                </div>

                <div
                    v-else-if="isSequenceRunning"
                    class="absolute right-4 bottom-4 left-4 z-20 flex items-center justify-between border-t border-white/25 pt-2.5 font-mono text-[9px] tracking-[0.16em] text-white/75 sm:right-5 sm:bottom-5 sm:left-5"
                >
                    <span>SEQUENCE IN PROGRESS</span>
                    <span>POSE {{ String(currentPose).padStart(2, '0') }} OF {{ String(totalPoses).padStart(2, '0') }}</span>
                </div>
            </section>

            <!-- 02. Composite frame preview -->
            <section
                class="flex min-h-0 flex-col overflow-hidden border border-neutral-700 bg-[#050505]"
                aria-label="Composite frame preview"
            >
                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-neutral-800 px-3 py-2.5 sm:px-4 sm:py-3">
                    <div>
                        <p class="font-mono text-[9px] tracking-[0.2em] text-neutral-500 sm:text-[10px]">
                            02 / OUTPUT
                        </p>
                        <h2 class="mt-1 text-xs font-medium tracking-[0.12em] text-white sm:text-sm">
                            LIVE FRAME COMPOSITE
                        </h2>
                    </div>
                    <div class="text-right">
                        <p class="font-mono text-[8px] tracking-[0.15em] text-neutral-600 sm:text-[9px]">
                            PHOTOS
                        </p>
                        <p class="mt-1 font-mono text-xs tabular-nums text-neutral-300 sm:text-sm">
                            {{ String(capturedPhotos.length).padStart(2, '0') }}<span class="text-neutral-600"> / </span>{{ String(totalPoses).padStart(2, '0') }}
                        </p>
                    </div>
                </div>

                <div
                    class="relative flex min-h-0 flex-1 items-center justify-center overflow-hidden bg-[radial-gradient(ellipse_at_center,#202020_0%,#090909_72%)] p-2.5 sm:p-4"
                >
                    <div class="pointer-events-none absolute inset-3 border border-white/[0.04] sm:inset-4"></div>
                    <canvas
                        ref="previewCanvasRef"
                        style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain"
                        class="relative z-10 block border border-neutral-600 bg-white shadow-[0_18px_50px_rgba(0,0,0,0.55)]"
                    ></canvas>
                </div>

                <!-- Filter controls appear after capture is complete -->
                <div class="shrink-0 border-t border-neutral-800 px-3 py-2.5 sm:px-4 sm:py-3">
                    <div v-if="isFinished" class="mb-2.5 sm:mb-3">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="font-mono text-[9px] tracking-[0.17em] text-neutral-400 sm:text-[10px]">
                                COLOR TREATMENT
                            </span>
                            <span class="font-mono text-[8px] tracking-[0.12em] text-neutral-600 sm:text-[9px]">
                                SELECT FILTER
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 sm:gap-2">
                            <button
                                v-for="filter in filterOptions"
                                :key="filter.value"
                                @click="changeFilter(filter.value)"
                                :class="[
                                    'border px-2.5 py-1.5 text-[9px] tracking-[0.1em] transition-colors duration-200 sm:px-3 sm:text-[10px]',
                                    selectedFilter === filter.value
                                        ? 'border-white bg-white font-semibold text-black'
                                        : 'border-neutral-700 bg-transparent text-neutral-400 hover:border-neutral-400 hover:text-white',
                                ]"
                            >
                                {{ filter.label.toUpperCase() }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mb-2.5 flex items-center justify-between gap-2 border-b border-neutral-800 pb-2.5 sm:mb-3 sm:pb-3"
                    >
                        <span class="font-mono text-[8px] tracking-[0.14em] text-neutral-500 sm:text-[9px]">
                            {{ isSequenceRunning ? 'AUTO CAPTURE IN PROGRESS' : 'READY WHEN YOU ARE' }}
                        </span>
                        <span class="font-mono text-[8px] tracking-[0.12em] text-neutral-600 sm:text-[9px]">
                            {{ capturedPhotos.length }} / {{ totalPoses }} COMPLETED
                        </span>
                    </div>

                    <!-- Final actions -->
                    <div class="flex w-full items-stretch justify-end gap-2 sm:gap-2.5">
                        <button
                            v-if="isFinished"
                            @click="startCaptureSequence"
                            class="shrink-0 border border-neutral-600 px-3 py-2.5 text-[9px] font-medium tracking-[0.12em] text-neutral-300 transition-colors hover:border-white hover:bg-white hover:text-black sm:px-4 sm:text-[10px]"
                        >
                            FOTO ULANG
                        </button>
                        <button
                            v-if="isFinished"
                            @click="submitComposite"
                            :disabled="form.processing"
                            class="flex min-w-0 flex-1 items-center justify-center gap-2 border border-white bg-white px-3 py-2.5 text-[9px] font-semibold tracking-[0.12em] text-black transition-colors hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-40 sm:px-4 sm:text-[10px]"
                        >
                            <span>{{ form.processing ? 'MENYIMPAN...' : 'LANJUT & CETAK FOTO' }}</span>
                            <span class="text-base leading-none">→</span>
                        </button>
                    </div>
                </div>
            </section>
        </main>
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
