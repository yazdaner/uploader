<script setup>
import { ref } from "vue";
const step = ref(1);
const file = ref();
const formatError = ref(false);
const stop = ref(false);
const selectedFileIndex = ref(null);
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
const chunks = ref([]);
function createChunks() {
    if (formatError.value == false) {
        let size = 500000;
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
        step.value = 2
    }
    selectedFileIndex.value = 0
}
</script>

<template>
    <div v-if="step.value == 1">
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
    <div v-else>step 2</div>
</template>
