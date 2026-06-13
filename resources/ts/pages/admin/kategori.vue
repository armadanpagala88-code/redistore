<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref<any[]>([])
const loading = ref(true)

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/admin/kategori')
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
  nama_game: '',
  deskripsi: '',
  is_aktif: true
})
const fileInput = ref<any>(null)
const isEdit = ref(false)

const openAddModal = () => {
  isEdit.value = false
  form.value = { id: '', nama_game: '', deskripsi: '', is_aktif: true }
  fileInput.value = null
  isDialogVisible.value = true
}

const openEditModal = (item: any) => {
  isEdit.value = true
  form.value = { 
    id: item.id, 
    nama_game: item.nama_game, 
    deskripsi: item.deskripsi || '', 
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
  formData.append('nama_game', form.value.nama_game)
  formData.append('deskripsi', form.value.deskripsi)
  formData.append('is_aktif', form.value.is_aktif ? '1' : '0')
  if (fileInput.value) {
    formData.append('gambar_logo', fileInput.value)
  }

  try {
    if (isEdit.value) {
      // Gunakan spoofing karena formData untuk image di PHP seringkali bermasalah pada verb PUT langsung
      formData.append('_method', 'PUT')
      await axios.post(`/api/admin/kategori/${form.value.id}`, formData)
    } else {
      await axios.post('/api/admin/kategori', formData)
    }
    isDialogVisible.value = false
    fetchItems()
  } catch (e) {
    alert('Gagal menyimpan data')
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus kategori game ini?')) return
  try {
    await axios.delete(`/api/admin/kategori/${id}`)
    fetchItems()
    alert('Game berhasil dihapus')
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal menghapus data')
    }
  }
}
</script>

<template>
  <div class="pa-4">
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4 font-weight-bold">Master Kategori Game</h2>
      <VBtn color="primary" @click="openAddModal">Tambah Game</VBtn>
    </div>

    <VCard elevation="3">
      <VCardText v-if="loading" class="text-center pa-6">
        <VProgressCircular indeterminate color="primary" />
      </VCardText>
      
      <VTable v-else hover>
        <thead>
          <tr>
            <th class="text-uppercase">Logo</th>
            <th class="text-uppercase">Nama Game</th>
            <th class="text-uppercase">Deskripsi</th>
            <th class="text-uppercase text-center">Status</th>
            <th class="text-uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>
              <VAvatar rounded size="48">
                <VImg :src="item.gambar_logo ? `/images/${item.gambar_logo}` : 'https://placehold.co/100x100'" />
              </VAvatar>
            </td>
            <td class="font-weight-medium">{{ item.nama_game }}</td>
            <td>{{ item.deskripsi }}</td>
            <td class="text-center">
              <VChip :color="item.is_aktif ? 'success' : 'error'" size="small">
                {{ item.is_aktif ? 'Aktif' : 'Non-aktif' }}
              </VChip>
            </td>
            <td class="text-center">
              <VBtn icon="ri-edit-line" variant="text" size="small" color="info" @click="openEditModal(item)" />
              <VBtn icon="ri-delete-bin-line" variant="text" size="small" color="error" @click="deleteItem(item.id)" />
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="5" class="text-center pa-4">Belum ada data game</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="isDialogVisible" max-width="500">
      <VCard>
        <VCardTitle>{{ isEdit ? 'Edit Game' : 'Tambah Game Baru' }}</VCardTitle>
        <VCardText>
          <VForm @submit.prevent="saveItem">
            <VTextField v-model="form.nama_game" label="Nama Game" required class="mb-4" />
            <VTextarea v-model="form.deskripsi" label="Deskripsi" rows="3" class="mb-4" />
            <VFileInput label="Logo Game (Opsional)" accept="image/*" class="mb-4" @change="handleFile" />
            <VSwitch v-model="form.is_aktif" label="Status Aktif" class="mb-4" color="primary" />
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
