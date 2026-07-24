<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { formatRupiah } from '@/utils/formatRupiah'
import { compressImage } from '@/utils/imageCompressor'

const items = ref<any[]>([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)

const stats = ref({
  total: 0,
  pending: 0,
  tersedia: 0
})

const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

const isEditDialogVisible = ref(false)
const editingItem = ref<any>(null)
const editForm = ref({
  judul_akun: '',
  login_via: '',
  harga: 0
})
const newCoverImage = ref<any[]>([[]])
const isSubmittingEdit = ref(false)
const searchQuery = ref('')
let searchTimeout: any = null

const isRejectDialogVisible = ref(false)
const rejectingId = ref('')
const rejectReason = ref('')
const isSubmittingReject = ref(false)

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

const fetchStats = async () => {
  try {
    const res = await axios.get('/api/admin/akun-game/stats')
    if (res.data.success) {
      stats.value = res.data.data
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

onMounted(() => {
  fetchItems()
  fetchStats()
})

const onSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    fetchItems()
  }, 500)
}

const updateStatus = async (id: string, status: string) => {
  if (status === 'Ditolak') {
    rejectingId.value = id
    rejectReason.value = ''
    isRejectDialogVisible.value = true
    return
  }

  if (!confirm(`Yakin ingin mengubah status akun ini menjadi ${status}?`)) return
  try {
    const res = await axios.put(`/api/admin/akun-game/${id}/status`, { status })
    alert(res.data.message)
    fetchItems()
    fetchStats()
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal mengubah status')
    }
  }
}

const submitReject = async () => {
  if (!rejectReason.value.trim()) {
    alert('Alasan penolakan harus diisi')
    return
  }
  
  isSubmittingReject.value = true
  try {
    const res = await axios.put(`/api/admin/akun-game/${rejectingId.value}/status`, { 
      status: 'Ditolak',
      alasan_ditolak: rejectReason.value
    })
    alert(res.data.message)
    isRejectDialogVisible.value = false
    fetchItems()
    fetchStats()
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal mengubah status')
    }
  } finally {
    isSubmittingReject.value = false
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
  newCoverImage.value = [[]]
  isEditDialogVisible.value = true
}

const submitEdit = async () => {
  if (!editingItem.value) return
  isSubmittingEdit.value = true
  
  const formData = new FormData()
  formData.append('judul_akun', editForm.value.judul_akun || '')
  formData.append('login_via', editForm.value.login_via || '')
  formData.append('harga', editForm.value.harga ? editForm.value.harga.toString() : '')
  
  let fileCount = 0
  if (newCoverImage.value && newCoverImage.value.length > 0) {
    for (const fileVal of newCoverImage.value) {
      if (fileVal) {
        if (Array.isArray(fileVal)) {
          for (const f of fileVal) {
            if (f && typeof f === 'object' && f.size !== undefined) {
              const compressed = await compressImage(f as File)
              formData.append('gambar_utama[]', compressed)
              fileCount++
            }
          }
        } else if (typeof fileVal === 'object' && fileVal.size !== undefined) {
          const compressed = await compressImage(fileVal as File)
          formData.append('gambar_utama[]', compressed)
          fileCount++
        }
      }
    }
  }
  
  formData.append('debug_file_count', fileCount.toString())
  
  try {
    const res = await axios.post(`/api/admin/akun-game/${editingItem.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    snackbar.value = { show: true, message: res.data.message || 'Berhasil memperbarui data', color: 'success' }
    isEditDialogVisible.value = false
    fetchItems()
  } catch (error: any) {
    snackbar.value = { show: true, message: error.response?.data?.message || 'Gagal menyimpan perubahan', color: 'error' }
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

    <!-- Stats Widgets -->
    <VRow class="mb-6">
      <VCol cols="12" md="4">
        <VCard elevation="4" class="rounded-lg bg-primary text-white border-none">
          <VCardText class="d-flex align-center justify-space-between pa-5">
            <div>
              <h4 class="text-h6 font-weight-medium mb-1 text-white opacity-90">Total Akun</h4>
              <div class="text-h4 font-weight-bold">{{ stats.total }}</div>
            </div>
            <VAvatar color="rgba(255,255,255,0.2)" size="56" rounded>
              <VIcon icon="ri-file-list-3-line" size="32" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol cols="12" md="4">
        <VCard elevation="4" class="rounded-lg bg-warning text-white border-none">
          <VCardText class="d-flex align-center justify-space-between pa-5">
            <div>
              <h4 class="text-h6 font-weight-medium mb-1 text-white opacity-90">Belum Disetujui</h4>
              <div class="text-h4 font-weight-bold">{{ stats.pending }}</div>
            </div>
            <VAvatar color="rgba(255,255,255,0.2)" size="56" rounded>
              <VIcon icon="ri-time-line" size="32" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard elevation="4" class="rounded-lg bg-success text-white border-none">
          <VCardText class="d-flex align-center justify-space-between pa-5">
            <div>
              <h4 class="text-h6 font-weight-medium mb-1 text-white opacity-90">Disetujui (Tersedia)</h4>
              <div class="text-h4 font-weight-bold">{{ stats.tersedia }}</div>
            </div>
            <VAvatar color="rgba(255,255,255,0.2)" size="56" rounded>
              <VIcon icon="ri-checkbox-circle-line" size="32" color="white" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

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
                  <VImg :src="`/img/akun/${item.gambar_utama}`" cover />
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
              <div v-if="item.status === 'Ditolak' && item.alasan_ditolak" class="text-caption text-error mt-1" style="max-width: 150px; line-height: 1.2;">
                Alasan: {{ item.alasan_ditolak }}
              </div>
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

            <div v-if="editingItem && (editingItem.gambar_utama || editingItem.gambar_lainnya?.length)" class="mb-4">
              <div class="text-caption text-medium-emphasis mb-2">Gambar Saat Ini:</div>
              <div class="d-flex gap-2 flex-wrap">
                <VCard v-if="editingItem.gambar_utama" rounded="lg" class="elevation-1 overflow-hidden" width="80" height="80">
                  <VImg :src="`/img/akun/${editingItem.gambar_utama}`" cover height="100%" />
                </VCard>
                <VCard v-for="(img, idx) in editingItem.gambar_lainnya" :key="idx" rounded="lg" class="elevation-1 overflow-hidden" width="80" height="80">
                  <VImg :src="`/img/akun/${img}`" cover height="100%" />
                </VCard>
              </div>
              <div class="text-caption text-error mt-1">*Jika Anda mengupload gambar baru, gambar lama akan dihapus.</div>
            </div>

            <div v-for="(img, idx) in newCoverImage" :key="idx" class="d-flex align-center gap-2 mb-4">
              <VFileInput 
                v-model="newCoverImage[idx]"
                :label="idx === 0 ? 'Ganti Cover Gambar' : 'Gambar Tambahan'"
                accept="image/*"
                variant="outlined"
                hide-details
                prepend-icon=""
                prepend-inner-icon="ri-image-add-line"
              />
              <VBtn 
                v-if="newCoverImage.length > 1" 
                icon="ri-close-line" 
                variant="tonal" 
                color="error" 
                size="small" 
                @click="newCoverImage.splice(idx, 1)" 
              />
            </div>
            <VBtn 
              v-if="newCoverImage.length < 3" 
              variant="outlined" 
              color="primary" 
              class="mb-6 d-block" 
              prepend-icon="ri-add-line"
              @click="newCoverImage.push([])"
            >
              Tambah Gambar Lagi
            </VBtn>
            
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

    <VSnackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" location="top right">
      {{ snackbar.message }}
      <template v-slot:actions>
        <VBtn variant="text" icon="ri-close-line" @click="snackbar.show = false" />
      </template>
    </VSnackbar>
    <!-- Tolak Dialog -->
    <VDialog v-model="isRejectDialogVisible" max-width="500">
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center pa-4 bg-error text-white">
          <span class="text-h6 font-weight-bold">Tolak Postingan</span>
          <VBtn icon="ri-close-line" variant="text" @click="isRejectDialogVisible = false" color="white" />
        </VCardTitle>
        <VCardText class="pt-6">
          <div class="mb-4">Berikan alasan penolakan agar penjual dapat memperbaikinya.</div>
          <VTextarea
            v-model="rejectReason"
            label="Alasan Penolakan"
            placeholder="Contoh: Gambar kurang jelas, harga tidak sesuai..."
            variant="outlined"
            rows="3"
            color="error"
          />
        </VCardText>
        <VCardActions class="pa-4 pt-0">
          <VSpacer />
          <VBtn variant="tonal" color="secondary" @click="isRejectDialogVisible = false">
            Batal
          </VBtn>
          <VBtn variant="elevated" color="error" @click="submitReject" :loading="isSubmittingReject" class="px-6 font-weight-bold">
            Tolak Postingan
          </VBtn>
        </VCardActions>
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
