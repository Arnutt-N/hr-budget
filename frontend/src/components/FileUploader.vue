<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFileStore } from '@/stores/files'
import { fileDownloadUrl } from '@/api/files'

const props = defineProps<{
  requestId: number
  disabled?: boolean
}>()

const emit = defineEmits<{
  uploaded: []
  removed: []
}>()

const store = useFileStore()
const dragOver = ref(false)
const uploadError = ref<string | null>(null)

onMounted(() => {
  if (props.requestId) store.fetchForRequest(props.requestId)
})

const ALLOWED_EXTENSIONS = ['pdf', 'xlsx', 'xls', 'csv', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif']
const MAX_SIZE = 10 * 1024 * 1024

function validateFile(file: File): string | null {
  const ext = file.name.split('.').pop()?.toLowerCase() ?? ''
  if (!ALLOWED_EXTENSIONS.includes(ext)) return `ประเภทไฟล์ .${ext} ไม่รองรับ`
  if (file.size > MAX_SIZE) return 'ไฟล์ขนาดเกิน 10 MB'
  return null
}

async function handleFiles(fileList: FileList | null) {
  if (!fileList || fileList.length === 0) return
  uploadError.value = null

  for (const file of Array.from(fileList)) {
    const err = validateFile(file)
    if (err) {
      uploadError.value = err
      continue
    }
    const ok = await store.upload(props.requestId, file)
    if (ok) {
      emit('uploaded')
    }
  }
}

function onDrop(e: DragEvent) {
  dragOver.value = false
  if (props.disabled) return
  handleFiles(e.dataTransfer?.files ?? null)
}

function onFileInput(e: Event) {
  const input = e.target as HTMLInputElement
  handleFiles(input.files)
  input.value = ''
}

async function handleDelete(fileId: number) {
  const ok = await store.remove(props.requestId, fileId)
  if (ok) emit('removed')
}

function formatSize(bytes: number): string {
  if (bytes >= 1_048_576) return (bytes / 1_048_576).toFixed(1) + ' MB'
  if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return bytes + ' B'
}

const files = computed(() => store.filesForRequest(props.requestId))
</script>

<template>
  <div>
    <h3 class="mb-2 text-sm font-medium text-gray-700">ไฟล์แนบ</h3>

    <!-- Drop zone -->
    <div
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="onDrop"
      :class="{ 'border-blue-400 bg-blue-50': dragOver, 'border-gray-300': !dragOver, 'opacity-50': disabled }"
      class="rounded-lg border-2 border-dashed p-6 text-center transition"
    >
      <template v-if="disabled">
        <p class="text-sm text-gray-400">บันทึกคำขอก่อน จึงจะแนบไฟล์ได้</p>
      </template>
      <template v-else>
        <p class="text-sm text-gray-500">คลิกหรือลากไฟล์มาวาง</p>
        <label class="mt-2 inline-block cursor-pointer rounded bg-blue-600 px-3 py-1.5 text-xs text-white hover:bg-blue-700">
          เลือกไฟล์
          <input type="file" class="hidden" multiple :accept="ALLOWED_EXTENSIONS.map(e => '.' + e).join(',')" @change="onFileInput" />
        </label>
      </template>
    </div>

    <p v-if="uploadError" class="mt-2 text-xs text-red-600">{{ uploadError }}</p>

    <!-- File list -->
    <ul v-if="files.length > 0" class="mt-3 divide-y rounded-lg border bg-white">
      <li v-for="f in files" :key="f.id" class="flex items-center justify-between px-3 py-2">
        <div class="flex items-center gap-2 min-w-0">
          <span class="text-xs font-mono text-gray-400 uppercase">{{ f.file_type }}</span>
          <a
            :href="fileDownloadUrl(f.id)"
            class="truncate text-sm text-blue-600 hover:underline"
            :title="f.original_name"
          >{{ f.original_name }}</a>
          <span class="text-xs text-gray-400">({{ formatSize(f.file_size) }})</span>
        </div>
        <button
          v-if="!disabled"
          @click="handleDelete(f.id)"
          class="ml-2 shrink-0 text-xs text-red-500 hover:text-red-700"
          title="ลบไฟล์"
        >
          &#10005;
        </button>
      </li>
    </ul>
  </div>
</template>
