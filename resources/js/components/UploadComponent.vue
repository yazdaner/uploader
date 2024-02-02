<script setup>
import { onMounted, ref, watch } from "vue";
import axios from "axios";

const step = ref(1);
const file = ref();
const formatError = ref(false);
const serverError = ref(false);
const networkError = ref(false);
const network = ref('online');
const stop = ref(false);
const selectedFileIndex = ref(null);
const uploadedSize = ref(0);
const chunks = ref([]);
let size = 500000;

async function select(event) {
    file.value = event.target.files[0];
    await checkFileTypes();
    createChunks();
}
function checkFileTypes() {
    const types = ["mp4", "mov", "wvm", "flv", "avi", "mkv"];
    const fileType = file.value.type.replace("video/", "");
    let error = true;
    for (let i = 0; i < types.length; i++) {
        if (fileType == types[i]) {
            error = false;
        }
    }
    error ? (formatError.value = true) : (formatError.value = false);
}
function createChunks() {
    if (formatError.value == false) {
        let chunksLength = Math.ceil(file.value.size / size);
        for (let i = 0; i < chunksLength; i++) {
            chunks.value.push(
                file.value.slice(
                    i * size,
                    Math.min(i * size + size, file.value.size),
                    file.value.type
                )
            );
        }
        step.value = 2;
    }
    selectedFileIndex.value = 0;
}

function upload(key) {
    if (
        chunks.value[key] != undefined &&
        stop.value == false &&
        networkError.value == false &&
        network.value == "online"
    ) {
        serverError.value = false;
        const url = "http://127.0.0.1:8000/upload";
        const formData = new FormData();
        formData.set("latest", (key == chunks.value.length - 1).toString());
        formData.set("sliceFile", chunks.value[key], `${file.value.name}.part`);
        formData.set("part", key);
        axios
            .post(url, formData)
            .then((response) => {
                console.log(response);
                selectedFileIndex.value++;
                changeProcessDetail();
            })
            .catch((error) => {
                stop.value = true;
                serverError.value = true;
                console.log(error);
            });
    }
}

function changeProcessDetail() {
    const w = (uploadedSize.value / file.value.size) * 100;
    document.querySelector(".progress-bar").style.width = w + "%";
    document.querySelector(".progress-bar").innerHTML = Math.round(w) + "%";
    if (selectedFileIndex.value < chunks.value.length - 1) {
        uploadedSize.value = selectedFileIndex.value * size;
    } else {
        uploadedSize.value = file.value.size;
    }
}
function convertFileSize(size) {
    if (size > 1000000000) {
        return (size / 1000000000).toFixed(2) + "GB";
    } else if (size > 1000000) {
        return (size / 1000000).toFixed(2) + "MB";
    } else if (size > 1000) {
        return (size / 1000).toFixed(2) + "KB";
    } else {
        return (size / 1000).toFixed(2) + "B";
    }
}

watch(selectedFileIndex, () => {
    upload(selectedFileIndex.value);
});

watch(stop, (newValue, oldValue) => {
    if ((oldValue == true, newValue == false)) {
        upload(selectedFileIndex.value);
    }
});

watch(network, (newValue, oldValue) => {
    if ((oldValue == 'offline', newValue == 'online')) {
        upload(selectedFileIndex.value);
    }
});

function checkNetwork() {
    setInterval(function () {
        if (file.value != null) {
            if (navigator.onLine) {
                network.value = "online";
                networkError.value = false;
            } else {
                network.value = "offline";
                networkError.value = true;
            }
        }
    }, 500);
}

onMounted(() => {
    checkNetwork();
});

async function onDrag(e){
    file.value = e.dataTransfer.files[0]
    await checkFileTypes();
    createChunks();
}

</script>

<template>
    <div v-if="step == 1" @drop.prevent="onDrag" @dragover.prevent>
        <div
            class="upload-box bg-white rounded cursor-pointer text-center shadow mx-auto w-50 mt-150 p-5"
        >
            <img
                src="../../images/upload.png"
                class="img-fluid"
                width="350"
                alt="upload"
            />
            <h5>File Upload</h5>
            <p>Drag or Choice your file</p>
            <input
                @change="select"
                type="file"
                accept="video/*"
                class="d-none"
                ref="fileInput"
            />
            <button class="btn btn-primary" @click="$refs['fileInput'].click()">
                Choice File
            </button>
            <p class="mt-3 text-danger" v-if="formatError">
                file format is invalid
            </p>
        </div>
    </div>
    <div v-else>
        <div
            class="upload-box bg-white rounded cursor-pointer text-center shadow mx-auto w-50 mt-150 p-5"
        >
            <div class="">
                <div
                    class="progress mb-4"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <div class="progress-bar animation"></div>
                </div>
                <span class="">{{ convertFileSize(file.size) }}</span>
                -
                <span class="">{{ convertFileSize(uploadedSize) }}</span>
            </div>
            <div class="mt-4">
                <div v-if="uploadedSize != file.size && networkError == false">
                    <button
                        v-if="stop == false"
                        @click="stop = true"
                        class="btn btn-danger"
                    >
                        stop
                    </button>
                    <button
                        v-if="stop == true"
                        @click="stop = false"
                        class="btn btn-success"
                    >
                        start
                    </button>
                </div>
                <div v-if="uploadedSize == file.size" class="text-success">
                    <strong>file uploaded</strong>
                </div>
                <p class="mt-3 text-danger" v-if="serverError">
                    Server has error
                </p>
                <p class="mt-3 text-danger" v-if="networkError">
                    Network has error
                </p>
            </div>
        </div>
    </div>
</template>
<style>
.animation {
    transition: width 0.5s;
}
</style>
