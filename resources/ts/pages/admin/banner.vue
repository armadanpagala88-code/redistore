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
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4 font-weight-bold">Manajemen Banner & Iklan</h2>
      <VBtn color="primary" @click="openAddModal">Tambah Banner</VBtn>
    </div>

    <VCard elevation="3">
      <VCardText v-if="loading" class="text-center pa-6">
        <VProgressCircular indeterminate color="primary" />
      </VCardText>
      
      <VTable v-else hover>
        <thead>
          <tr>
            <th class="text-uppercase">Preview</th>
            <th class="text-uppercase">Judul Banner</th>
            <th class="text-uppercase">Deskripsi</th>
            <th class="text-uppercase text-center">Status</th>
            <th class="text-uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>
              <VCard elevation="1" class="ma-2 rounded" max-width="150">
                <VImg :src="item.gambar_banner ? `/images/${item.gambar_banner}` : 'https://placehold.co/400x200'" height="60" cover />
              </VCard>
            </td>
            <td class="font-weight-medium">{{ item.judul }}</td>
            <td>{{ item.deskripsi }}</td>
            <td class="text-center">
              <VChip :color="item.is_aktif ? 'success' : 'error'" size="small">
                {{ item.is_aktif ? 'Ditayangkan' : 'Disembunyikan' }}
              </VChip>
            </td>
            <td class="text-center">
              <VBtn icon="ri-edit-line" variant="text" size="small" color="info" @click="openEditModal(item)" />
              <VBtn icon="ri-delete-bin-line" variant="text" size="small" color="error" @click="deleteItem(item.id)" />
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="5" class="text-center pa-4">Belum ada banner, tambahkan banner sekarang.</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="isDialogVisible" max-width="600">
      <VCard>
        <VCardTitle>{{ isEdit ? 'Edit Banner' : 'Tambah Banner Baru' }}</VCardTitle>
        <VCardText>
          <VForm @submit.prevent="saveItem">
            <VTextField v-model="form.judul" label="Judul Banner / Campaign" required class="mb-4" />
            <VTextarea v-model="form.deskripsi" label="Deskripsi Singkat" rows="2" class="mb-4" />
            <VTextField v-model="form.link_tujuan" label="Link Tujuan (Opsional)" placeholder="https://..." class="mb-4" />
            <VFileInput label="Upload Gambar Banner (Format: JPG/PNG, Max: 5MB)" accept="image/*" class="mb-4" @change="handleFile" :required="!isEdit" />
            <VSwitch v-model="form.is_aktif" label="Status Penayangan (Aktif/Tidak)" class="mb-4" color="primary" />
            <div class="d-flex justify-end gap-2">
              <VBtn variant="outlined" color="secondary" @click="isDialogVisible = false">Batal</VBtn>
              <VBtn type="submit" color="primary" variant="elevated">Simpan</VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
