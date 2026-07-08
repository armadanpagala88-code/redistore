<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const items = ref<any[]>([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)

const isEditDialogVisible = ref(false)
const editingItem = ref<any>(null)
const editForm = ref({
  judul_akun: '',
  login_via: '',
  harga: 0
})
const newCoverImage = ref<File | null>(null)
const isSubmittingEdit = ref(false)
const searchQuery = ref('')
let searchTimeout: any = null

const fetchItems = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/admin/akun-game?page=${page.value}&search=${searchQuery.value}`)
    if (res.data.data.data) {
      items.value = res.data.data.data
      totalPages.value = res.data.data.last_page || 1
    } else {
      items.value = res.data.data
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchItems()
})

const onSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    fetchItems()
  }, 500)
}

const updateStatus = async (id: string, status: string) => {
  if (!confirm(`Yakin ingin mengubah status akun ini menjadi ${status}?`)) return
  try {
    const res = await axios.put(`/api/admin/akun-game/${id}/status`, { status })
    alert(res.data.message)
    fetchItems()
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal mengubah status')
    }
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus postingan akun ini? Data akan hilang selamanya.')) return
  try {
    const res = await axios.delete(`/api/admin/akun-game/${id}`)
    alert(res.data.message)
    fetchItems()
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal menghapus akun')
    }
  }
}

const formatRupiah = (value: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}

const statusColor = (status: string) => {
  switch (status) {
    case 'Pending': return 'warning'
    case 'Tersedia': return 'success'
    case 'Terjual': return 'info'
    case 'Ditolak': return 'error'
    default: return 'grey'
  }
}

const getGameColor = (nama_game: string) => {
  const name = (nama_game || '').toLowerCase()
  if (name.includes('mobile legends') || name.includes('mlbb')) return 'info'
  if (name.includes('free fire') || name.includes('ff')) return 'warning'
  if (name.includes('genshin')) return 'success'
  if (name.includes('pubg')) return 'secondary'
  if (name.includes('valorant')) return 'error'
  return 'primary'
}

const openEditDialog = (item: any) => {
  editingItem.value = item
  editForm.value = {
    judul_akun: item.judul_akun,
    login_via: item.login_via,
    harga: item.harga
  }
  newCoverImage.value = null
  isEditDialogVisible.value = true
}

const submitEdit = async () => {
  if (!editingItem.value) return
  isSubmittingEdit.value = true
  
  const formData = new FormData()
  formData.append('judul_akun', editForm.value.judul_akun)
  formData.append('login_via', editForm.value.login_via)
  formData.append('harga', editForm.value.harga.toString())
  
  if (newCoverImage.value) {
    formData.append('gambar_utama', newCoverImage.value)
  }
  
  try {
    const res = await axios.post(`/api/admin/akun-game/${editingItem.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    alert(res.data.message)
    isEditDialogVisible.value = false
    fetchItems()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal menyimpan perubahan')
  } finally {
    isSubmittingEdit.value = false
  }
}
</script>

<template>
  <div class="pa-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-checkbox-multiple-line" color="primary" />
          Persetujuan Akun Game
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Review dan setujui postingan akun game dari member.</p>
      </div>
      <div style="min-width: 300px;">
        <VTextField
          v-model="searchQuery"
          prepend-inner-icon="ri-search-line"
          placeholder="Cari nama akun, penjual, game..."
          variant="outlined"
          density="comfortable"
          hide-details
          clearable
          @input="onSearch"
          @click:clear="onSearch"
        />
      </div>
    </div>

    <!-- Data Table Card -->
    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data akun...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Akun Game</th>
            <th class="text-uppercase text-caption font-weight-bold">Game</th>
            <th class="text-uppercase text-caption font-weight-bold">Penjual</th>
            <th class="text-uppercase text-caption font-weight-bold">Harga</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="transition-swing">
            <td class="py-3">
              <div class="d-flex align-center gap-3">
                <VAvatar rounded="lg" size="48" color="surface-variant" variant="tonal" class="elevation-1">
                  <VImg :src="`/images/akun/${item.gambar_utama}`" cover />
                </VAvatar>
                <div>
                  <div class="font-weight-bold text-high-emphasis">{{ item.judul_akun }}</div>
                  <div class="text-caption text-medium-emphasis">Login via: {{ item.login_via }}</div>
                </div>
              </div>
            </td>
            <td><VChip size="small" variant="elevated" class="font-weight-bold" :color="getGameColor(item.kategori?.nama_game)">{{ item.kategori?.nama_game }}</VChip></td>
            <td>
              <div class="font-weight-medium">{{ item.penjual?.nama_lengkap }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.penjual?.username }}</div>
            </td>
            <td class="font-weight-bold text-success">{{ formatRupiah(item.harga) }}</td>
            <td class="text-center">
              <VChip :color="statusColor(item.status)" size="small" variant="elevated" class="font-weight-bold">
                {{ item.status }}
              </VChip>
            </td>
            <td class="text-center">
              <div class="d-flex justify-center gap-2">
                <VBtn v-if="item.status === 'Pending'" size="small" color="success" variant="elevated" @click="updateStatus(item.id, 'Tersedia')" prepend-icon="ri-check-line">
                  Setujui
                </VBtn>
                <VBtn v-if="item.status === 'Pending'" size="small" color="error" variant="elevated" @click="updateStatus(item.id, 'Ditolak')" prepend-icon="ri-close-line">
                  Tolak
                </VBtn>
                
                <VBtn v-if="item.status !== 'Terjual'" icon="ri-pencil-line" variant="tonal" size="small" color="primary" @click="openEditDialog(item)" />
                <VBtn v-if="item.status !== 'Terjual' && item.status !== 'Pending'" icon="ri-delete-bin-line" variant="tonal" size="small" color="error" @click="deleteItem(item.id)" />
              </div>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="6" class="text-center pa-8">
              <VIcon icon="ri-search-eye-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">
                {{ searchQuery ? 'Tidak ada hasil untuk pencarian ini' : 'Belum ada akun yang diposting' }}
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
      
      <VDivider />
      <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3" v-if="totalPages > 1">
        <VPagination
          v-model="page"
          :length="totalPages"
          :total-visible="5"
          @update:model-value="fetchItems"
          active-color="primary"
        />
      </VCardText>
    </VCard>

    <!-- Dialog Edit Produk -->
    <VDialog v-model="isEditDialogVisible" max-width="600">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold bg-primary text-white">
          Edit Data Akun
          <VBtn icon="ri-close-line" variant="text" size="small" color="white" @click="isEditDialogVisible = false" />
        </VCardTitle>
        <VCardText class="pa-6">
          <VForm @submit.prevent="submitEdit">
            <VTextField 
              v-model="editForm.judul_akun" 
              label="Judul Akun" 
              variant="outlined" 
              class="mb-4"
              required 
            />
            
            <VTextField 
              v-model="editForm.login_via" 
              label="Login Via (Contoh: Facebook, VK, Moonton)" 
              variant="outlined" 
              class="mb-4"
              required 
            />
            
            <VTextField 
              v-model="editForm.harga" 
              label="Harga" 
              type="number"
              variant="outlined" 
              class="mb-4"
              required 
            />

            <VFileInput 
              v-model="newCoverImage"
              label="Ganti Cover Gambar (Kosongkan jika tidak ingin ganti)"
              accept="image/*"
              variant="outlined"
              class="mb-6"
              prepend-icon=""
              prepend-inner-icon="ri-image-add-line"
            />
            
            <VBtn 
              type="submit" 
              color="primary" 
              block 
              size="large" 
              class="font-weight-bold"
              :loading="isSubmittingEdit"
            >
              Simpan Perubahan
            </VBtn>
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
