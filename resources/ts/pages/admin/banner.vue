<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref<any[]>([])
const loading = ref(true)

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/admin/banner')
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
  deskripsi: '',
  link_tujuan: '',
  is_aktif: true
})
const fileInput = ref<any>(null)
const isEdit = ref(false)

const openAddModal = () => {
  isEdit.value = false
  form.value = { id: '', judul: '', deskripsi: '', link_tujuan: '', is_aktif: true }
  fileInput.value = null
  isDialogVisible.value = true
}

const openEditModal = (item: any) => {
  isEdit.value = true
  form.value = { 
    id: item.id, 
    judul: item.judul, 
    deskripsi: item.deskripsi || '', 
    link_tujuan: item.link_tujuan || '',
    is_aktif: item.is_aktif == 1 
  }
  fileInput.value = null
  isDialogVisible.value = true
}

const handleFile = (e: any) => {
  if (e.target.files && e.target.files[0]) {
    fileInput.value = e.target.files[0]
  }
}

const saveItem = async () => {
  const formData = new FormData()
  formData.append('judul', form.value.judul)
  formData.append('deskripsi', form.value.deskripsi)
  formData.append('link_tujuan', form.value.link_tujuan)
  formData.append('is_aktif', form.value.is_aktif ? '1' : '0')
  if (fileInput.value) {
    formData.append('gambar_banner', fileInput.value)
  }

  try {
    if (isEdit.value) {
      formData.append('_method', 'PUT')
      await axios.post(`/api/admin/banner/${form.value.id}`, formData)
    } else {
      await axios.post('/api/admin/banner', formData)
    }
    isDialogVisible.value = false
    fetchItems()
  } catch (e) {
    alert('Gagal menyimpan data banner. Pastikan format gambar dan ukuran sesuai.')
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus banner ini?')) return
  try {
    await axios.delete(`/api/admin/banner/${id}`)
    fetchItems()
  } catch (e) {
    alert('Gagal menghapus data')
  }
}
</script>

<template>
  <div class="pa-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-image-edit-line" color="primary" />
          Manajemen Banner & Iklan
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Atur gambar banner slider yang akan ditampilkan di halaman utama.</p>
      </div>
      <VBtn color="primary" prepend-icon="ri-add-line" @click="openAddModal" class="rounded-lg font-weight-bold">Tambah Banner</VBtn>
    </div>

    <!-- Data Table Card -->
    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data banner...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold" style="width: 200px">Preview Banner</th>
            <th class="text-uppercase text-caption font-weight-bold">Judul & Deskripsi</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="transition-swing">
            <td class="py-3">
              <VCard elevation="2" class="rounded-lg overflow-hidden" max-width="180">
                <VImg :src="item.gambar_banner ? `/images/${item.gambar_banner}` : 'https://placehold.co/400x200'" height="80" cover />
              </VCard>
            </td>
            <td>
              <div class="font-weight-bold text-high-emphasis text-truncate" style="max-width: 350px;">{{ item.judul }}</div>
              <div class="text-body-2 text-medium-emphasis text-truncate mt-1" style="max-width: 350px;">
                {{ item.deskripsi || 'Tidak ada deskripsi' }}
              </div>
            </td>
            <td class="text-center">
              <VChip :color="item.is_aktif ? 'success' : 'secondary'" size="small" variant="elevated" class="font-weight-bold">
                {{ item.is_aktif ? 'Ditayangkan' : 'Disembunyikan' }}
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
            <td colspan="4" class="text-center pa-8">
              <VIcon icon="ri-image-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada banner</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Edit/Tambah -->
    <VDialog v-model="isDialogVisible" max-width="600">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold">
          {{ isEdit ? 'Edit Banner' : 'Tambah Banner Baru' }}
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isDialogVisible = false" />
        </VCardTitle>
        <VCardText class="px-6 pb-6 pt-4">
          <VForm @submit.prevent="saveItem">
            <VTextField v-model="form.judul" label="Judul Banner / Campaign" required variant="outlined" density="comfortable" class="mb-4" />
            <VTextarea v-model="form.deskripsi" label="Deskripsi Singkat" rows="2" variant="outlined" density="comfortable" class="mb-4" />
            <VTextField v-model="form.link_tujuan" label="Link Tujuan (Opsional)" placeholder="https://..." variant="outlined" density="comfortable" class="mb-4" prepend-inner-icon="ri-link-m" />
            
            <VFileInput 
              label="Upload Gambar Banner (Format: JPG/PNG, Max: 5MB)" 
              accept="image/*" 
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-image-add-line"
              prepend-icon=""
              class="mb-4" 
              @change="handleFile" 
              :required="!isEdit" 
            />
            
            <VSwitch v-model="form.is_aktif" label="Status Penayangan (Aktif/Tidak)" color="primary" class="mb-6 font-weight-medium" inset />
            
            <div class="d-flex justify-end gap-3">
              <VBtn variant="tonal" color="secondary" @click="isDialogVisible = false" class="px-6 rounded-lg">Batal</VBtn>
              <VBtn type="submit" color="primary" variant="elevated" class="px-6 rounded-lg font-weight-bold">Simpan Banner</VBtn>
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
