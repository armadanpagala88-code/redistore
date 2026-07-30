<script setup lang="ts">
import { Placeholder } from '@tiptap/extension-placeholder'
import { TextAlign } from '@tiptap/extension-text-align'
import { Underline } from '@tiptap/extension-underline'
import { StarterKit } from '@tiptap/starter-kit'
import { Image } from '@tiptap/extension-image'
import { EditorContent, useEditor } from '@tiptap/vue-3'
import axios from 'axios'

const props = defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const editorRef = ref()
const fileInput = ref<HTMLInputElement | null>(null)

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Image.configure({
      inline: true,
      allowBase64: true,
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph', 'image'],
    }),
    Placeholder.configure({
      placeholder: 'Write something here...',
    }),
    Underline,
  ],
  onUpdate() {
    if (!editor.value)
      return

    emit('update:modelValue', editor.value.getHTML())
  },
})

const triggerImageUpload = () => {
  fileInput.value?.click()
}

const handleImageUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('image', file)

  try {
    const res = await axios.post('/api/admin/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (res.data.success && res.data.url) {
      editor.value?.chain().focus().setImage({ src: res.data.url }).run()
    }
  } catch (error) {
    console.error('Failed to upload image', error)
    alert('Gagal mengunggah gambar')
  } finally {
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

watch(() => props.modelValue, () => {
  const isSame = editor.value?.getHTML() === props.modelValue

  if (isSame)
    return

  editor.value?.commands.setContent(props.modelValue)
})
</script>

<template>
  <div class="pa-5">
    <div
      v-if="editor"
      class="d-flex flex-wrap"
    >
      <VBtn
        :class="{ 'is-active': editor.isActive('bold') }"
        icon="ri-bold"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        @click="editor.chain().focus().toggleBold().run()"
      />
      <VBtn
        :class="{ 'is-active': editor.isActive('underline') }"
        icon="ri-underline"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        @click="editor.commands.toggleUnderline()"
      />
      <VBtn
        icon="ri-italic"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        :class="{ 'is-active': editor.isActive('italic') }"
        @click="editor.chain().focus().toggleItalic().run()"
      />
      <VBtn
        icon="ri-strikethrough"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        :class="{ 'is-active': editor.isActive('strike') }"
        @click="editor.chain().focus().toggleStrike().run()"
      />
      <VBtn
        icon="ri-align-left"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
        @click="editor.chain().focus().setTextAlign('left').run()"
      />
      <VBtn
        icon="ri-align-center"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
        @click="editor.chain().focus().setTextAlign('center').run()"
      />
      <VBtn
        icon="ri-align-right"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
        @click="editor.chain().focus().setTextAlign('right').run()"
      />
      <VBtn
        icon="ri-align-justify"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        :class="{ 'is-active': editor.isActive({ textAlign: 'justify' }) }"
        @click="editor.chain().focus().setTextAlign('justify').run()"
      />
      <VDivider vertical class="mx-2" />
      <VBtn
        icon="ri-image-add-line"
        class="rounded"
        size="small"
        variant="text"
        color="default"
        @click="triggerImageUpload"
      />
      <input type="file" ref="fileInput" accept="image/*" style="display: none" @change="handleImageUpload" />
    </div>
    <VDivider class="mt-4 mb-2" />
    <EditorContent
      ref="editorRef"
      :editor="editor"
    />
  </div>
</template>

<style lang="scss">
.ProseMirror {
  padding: 0.5rem;
  min-block-size: 15vh;

  p {
    margin-block-end: 0;
  }

  p.is-editor-empty:first-child::before {
    block-size: 0;
    color: #adb5bd;
    content: attr(data-placeholder);
    float: inline-start;
    pointer-events: none;
  }
}
</style>

<style lang="scss">
.is-active {
  border-color: rgba(var(--v-theme-primary), var(--v-border-opacity)) !important;
  background-color: rgba(var(--v-theme-primary), var(--v-activated-opacity));
  color: rgb(var(--v-theme-primary));
}
</style>
