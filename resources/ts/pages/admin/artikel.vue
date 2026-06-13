<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref<any[]>([])
const loading = ref(true)

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/admin/artikel')
    items.value = res.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!localStorage.getItem('admin_token')) {
    router.push('/login')
    return
  }
  fetchItems()
})

const isDialogVisible = ref(false)
const form = ref({
  id: '',
  judul: '',
  konten: '',
  is_published: true,
  gambar: null as File | null
})
const isEdit = ref(false)

const openAddModal = () => {
  isEdit.value = false
  form.value = { id: '', judul: '', konten: '', is_published: true, gambar: null }
  isDialogVisible.value = true
}

const openEditModal = (item: any) => {
  isEdit.value = true
  form.value = { 
    id: item.id, 
    judul: item.judul, 
    konten: item.konten, 
    is_published: item.is_published == 1,
    gambar: null
  }
  isDialogVisible.value = true
}

const saveItem = async () => {
  try {
    const formData = new FormData()
    formData.append('judul', form.value.judul)
    formData.append('konten', form.value.konten)
    formData.append('is_published', form.value.is_published ? '1' : '0')
    
    if (form.value.gambar) {
      formData.append('gambar', form.value.gambar)
    }

    if (isEdit.value) {
      // Laravel trick to handle PUT with FormData
      formData.append('_method', 'PUT')
      await axios.post(`/api/admin/artikel/${form.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await axios.post('/api/admin/artikel', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    isDialogVisible.value = false
    fetchItems()
  } catch (e) {
    alert('Gagal menyimpan data')
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus artikel ini?')) return
  try {
    await axios.delete(`/api/admin/artikel/${id}`)
    fetchItems()
    alert('Artikel berhasil dihapus')
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal menghapus data')
    }
  }
}

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}
</script>

<template>
  <div class="pa-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-article-line" color="primary" />
          Manajemen Artikel / Blog
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Tulis, edit, dan kelola artikel berita atau tips seputar game.</p>
      </div>
      <VBtn color="primary" prepend-icon="ri-add-line" @click="openAddModal" class="rounded-lg font-weight-bold">Tulis Artikel</VBtn>
    </div>

    <!-- Data Table Card -->
    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data artikel...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold" style="width: 80px">Cover</th>
            <th class="text-uppercase text-caption font-weight-bold">Judul & Slug</th>
            <th class="text-uppercase text-caption font-weight-bold">Tanggal</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Views</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="transition-swing">
            <td class="py-3">
              <VAvatar size="60" rounded="lg" color="surface-variant" variant="tonal" class="elevation-1">
                <VImg v-if="item.gambar" :src="item.gambar" cover />
                <VIcon v-else icon="ri-image-line" color="grey-darken-1" size="24" />
              </VAvatar>
            </td>
            <td>
              <div class="font-weight-bold text-high-emphasis text-truncate" style="max-width: 350px;">{{ item.judul }}</div>
              <div class="text-caption text-medium-emphasis d-flex align-center gap-1 mt-1">
                <VIcon icon="ri-links-line" size="12" />
                /blog/{{ item.slug }}
              </div>
            </td>
            <td>
              <div class="text-body-2 font-weight-medium">{{ formatDate(item.created_at) }}</div>
            </td>
            <td class="text-center">
              <VChip size="small" variant="tonal" color="info" class="font-weight-bold">
                <VIcon start icon="ri-eye-line" size="12" />
                {{ item.views }}
              </VChip>
            </td>
            <td class="text-center">
              <VChip :color="item.is_published ? 'success' : 'secondary'" size="small" variant="elevated" class="font-weight-bold">
                {{ item.is_published ? 'Published' : 'Draft' }}
              </VChip>
            </td>
            <td class="text-center">
              <div class="d-flex justify-center gap-2">
                <VBtn icon="ri-edit-line" variant="tonal" size="small" color="info" @click="openEditModal(item)" />
                <VBtn icon="ri-delete-bin-line" variant="tonal" size="small" color="error" @click="deleteItem(item.id)" />
              </div>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="6" class="text-center pa-8">
              <VIcon icon="ri-file-list-3-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada artikel</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Edit/Tambah -->
    <VDialog v-model="isDialogVisible" max-width="800">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold">
          {{ isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru' }}
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isDialogVisible = false" />
        </VCardTitle>
        <VCardText class="px-6 pb-6 pt-4">
          <VForm @submit.prevent="saveItem">
            <VTextField v-model="form.judul" label="Judul Artikel" required variant="outlined" density="comfortable" class="mb-4" />
            
            <VFileInput 
              v-model="form.gambar" 
              label="Upload Foto/Gambar Utama (Opsional)" 
              accept="image/*" 
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-image-add-line" 
              prepend-icon=""
              class="mb-4" 
            />

            <VTextarea 
              v-model="form.konten" 
              label="Konten (Mendukung HTML dasar)" 
              required 
              rows="10"
              variant="outlined"
              class="mb-4" 
            />

            <VSwitch v-model="form.is_published" label="Publish (Tampilkan di Publik)" color="primary" class="mb-6 font-weight-medium" inset />
            
            <div class="d-flex justify-end gap-3">
              <VBtn variant="tonal" color="secondary" @click="isDialogVisible = false" class="px-6 rounded-lg">Batal</VBtn>
              <VBtn type="submit" color="primary" variant="elevated" class="px-6 rounded-lg font-weight-bold">Simpan Artikel</VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.border-t-primary {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}
.custom-table tbody tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.03) !important;
}
</style>
