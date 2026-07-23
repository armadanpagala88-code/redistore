<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { themeConfig } from '@themeConfig'
import { compressImage } from '@/utils/imageCompressor'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const items = ref<any[]>([])
const loading = ref(true)

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/member/akun-game')
    items.value = res.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const kategoris = ref<any[]>([])
const fetchKategori = async () => {
  try {
    const res = await axios.get('/api/kategori-game')
    kategoris.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  fetchItems()
  fetchKategori()
})

const isDialogVisible = ref(false)
const editId = ref<string | null>(null)
const form = ref({
  kategori_game_id: null,
  judul_akun: '',
  deskripsi_akun: '',
  harga: 0,
  login_via: '',
  email_akun: '',
  password_akun: '',
  catatan_akun: ''
})
const fileInput = ref<any[]>([[]])
const isSubmitting = ref(false)

const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

const openAddModal = () => {
  editId.value = null
  editingItemData.value = null
  form.value = { 
    kategori_game_id: null,
    judul_akun: '',
    deskripsi_akun: '',
    harga: 0,
    login_via: '',
    email_akun: '',
    password_akun: '',
    catatan_akun: ''
  }
  fileInput.value = [[]]
  isDialogVisible.value = true
}

const editingItemData = ref<any>(null)

const editItem = (item: any) => {
  editingItemData.value = item
  editId.value = item.id
  form.value = {
    kategori_game_id: item.kategori_game_id,
    judul_akun: item.judul_akun,
    deskripsi_akun: item.deskripsi_akun,
    harga: item.harga,
    login_via: item.login_via,
    email_akun: item.email_akun,
    password_akun: item.password_akun,
    catatan_akun: item.catatan_akun || ''
  }
  fileInput.value = [[]]
  isDialogVisible.value = true
}


const saveItem = async () => {
  if (!editId.value && (!fileInput.value || fileInput.value.length === 0)) {
    alert('Gambar utama wajib diupload!')
    return
  }

  isSubmitting.value = true
  const formData = new FormData()
  formData.append('kategori_game_id', form.value.kategori_game_id || '')
  formData.append('judul_akun', form.value.judul_akun || '')
  formData.append('deskripsi_akun', form.value.deskripsi_akun || '')
  formData.append('harga', form.value.harga ? form.value.harga.toString() : '')
  formData.append('login_via', form.value.login_via || '')
  formData.append('email_akun', form.value.email_akun || '')
  formData.append('password_akun', form.value.password_akun || '')
  formData.append('catatan_akun', form.value.catatan_akun || '')
  
  let fileCount = 0
  if (fileInput.value && fileInput.value.length > 0) {
    for (const fileVal of fileInput.value) {
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
    const url = editId.value ? `/api/member/akun-game/${editId.value}` : '/api/member/akun-game'
    const res = await axios.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    snackbar.value = { show: true, message: res.data.message || 'Akun berhasil disimpan', color: 'success' }
    isDialogVisible.value = false
    fetchItems()
  } catch (e: any) {
    if (e.response && e.response.data && e.response.data.message) {
      snackbar.value = { show: true, message: e.response.data.message, color: 'error' }
    } else {
      snackbar.value = { show: true, message: 'Gagal memposting akun', color: 'error' }
    }
  } finally {
    isSubmitting.value = false
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus postingan ini?')) return
  try {
    const res = await axios.delete(`/api/member/akun-game/${id}`)
    alert(res.data.message)
    fetchItems()
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal menghapus data')
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
</script>

<template>
  <VContainer class="py-8">
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-store-2-line" color="primary" />
          Jual Akun Game
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Kelola daftar akun game yang Anda jual.</p>
      </div>
      <div class="d-flex gap-3">
        <VBtn color="secondary" variant="tonal" to="/member/dashboard" prepend-icon="ri-arrow-left-line">Kembali</VBtn>
        <VBtn color="primary" prepend-icon="ri-add-line" @click="openAddModal" class="rounded-lg font-weight-bold">Posting Akun Baru</VBtn>
      </div>
    </div>

    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Info Akun</th>
            <th class="text-uppercase text-caption font-weight-bold">Game</th>
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
            <td>
              <VChip size="small" variant="tonal" color="primary">{{ item.kategori?.nama_game }}</VChip>
            </td>
            <td class="font-weight-bold text-success">{{ formatRupiah(item.harga) }}</td>
            <td class="text-center">
              <VChip :color="statusColor(item.status)" size="small" variant="elevated" class="font-weight-bold">
                {{ item.status }}
              </VChip>
            </td>
            <td class="text-center">
              <div class="d-flex justify-center gap-2">
                <template v-if="item.status !== 'Terjual'">
                  <VBtn icon="ri-pencil-line" variant="tonal" size="small" color="primary" @click="editItem(item)" />
                  <VBtn icon="ri-delete-bin-line" variant="tonal" size="small" color="error" @click="deleteItem(item.id)" />
                </template>
                <span v-else class="text-caption text-medium-emphasis">Terkunci</span>
              </div>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="5" class="text-center pa-8">
              <VIcon icon="ri-store-3-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Anda belum memposting akun apapun</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Posting/Edit Akun -->
    <VDialog v-model="isDialogVisible" max-width="600" scrollable>
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold">
          {{ editId ? 'Edit Akun Game' : 'Posting Akun Baru' }}
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isDialogVisible = false" />
        </VCardTitle>
        <VDivider />
        <VCardText class="px-6 pb-6 pt-4" style="max-height: 70vh;">
          <VAlert type="info" variant="tonal" class="mb-4 text-body-2">
            Akun yang Anda posting akan di-review terlebih dahulu oleh Admin sebelum muncul di katalog publik. Pastikan Anda memasukkan data yang valid!
          </VAlert>
          
          <VForm @submit.prevent="saveItem">
            <VSelect 
              v-model="form.kategori_game_id" 
              :items="kategoris" 
              item-title="nama_game" 
              item-value="id" 
              label="Pilih Game" 
              required 
              variant="outlined" 
              density="comfortable" 
              class="mb-4" 
            />
            
            <VTextField 
              v-model="form.judul_akun" 
              label="Judul Postingan" 
              placeholder="Contoh: Akun MLBB Mythic Glory Murah" 
              required 
              variant="outlined" 
              density="comfortable" 
              class="mb-4" 
            />
            
            <VTextarea 
              v-model="form.deskripsi_akun" 
              label="Deskripsi Akun (Spek lengkap)" 
              rows="4" 
              required 
              variant="outlined" 
              density="comfortable" 
              class="mb-4" 
            />
            
            <VRow>
              <VCol cols="6">
                <VTextField 
                  v-model.number="form.harga" 
                  type="number" 
                  label="Harga Jual (Rp)" 
                  required 
                  variant="outlined" 
                  density="comfortable" 
                  class="mb-4" 
                />
              </VCol>
              <VCol cols="6">
                <VTextField 
                  v-model="form.login_via" 
                  label="Login Via (Moonton, FB, dll)" 
                  required 
                  variant="outlined" 
                  density="comfortable" 
                  class="mb-4" 
                />
              </VCol>
            </VRow>
            
            <VDivider class="mb-4" />
            <div class="text-subtitle-2 font-weight-bold mb-3 text-error">Data Privasi (Hanya diberikan ke pembeli setelah lunas)</div>
            
            <VRow>
              <VCol cols="6">
                <VTextField 
                  v-model="form.email_akun" 
                  label="Email Akun" 
                  required 
                  variant="outlined" 
                  density="comfortable" 
                  class="mb-4" 
                />
              </VCol>
              <VCol cols="6">
                <VTextField 
                  v-model="form.password_akun" 
                  label="Password Akun" 
                  required 
                  variant="outlined" 
                  density="comfortable" 
                  class="mb-4" 
                />
              </VCol>
            </VRow>
            
            <VTextField 
              v-model="form.catatan_akun" 
              label="Catatan Tambahan (Opsional)" 
              variant="outlined" 
              density="comfortable" 
              class="mb-4" 
            />

            <div v-if="editingItemData && (editingItemData.gambar_utama || editingItemData.gambar_lainnya?.length)" class="mb-4">
              <div class="text-caption text-medium-emphasis mb-2">Gambar Saat Ini:</div>
              <div class="d-flex gap-2 flex-wrap">
                <VCard v-if="editingItemData.gambar_utama" rounded="lg" class="elevation-1 overflow-hidden" width="80" height="80">
                  <VImg :src="`/img/akun/${editingItemData.gambar_utama}`" cover height="100%" />
                </VCard>
                <VCard v-for="(img, idx) in editingItemData.gambar_lainnya" :key="idx" rounded="lg" class="elevation-1 overflow-hidden" width="80" height="80">
                  <VImg :src="`/img/akun/${img}`" cover height="100%" />
                </VCard>
              </div>
              <div class="text-caption text-error mt-1">*Jika Anda mengupload gambar baru, gambar lama akan dihapus.</div>
            </div>
            
            <div v-for="(img, idx) in fileInput" :key="idx" class="d-flex align-center gap-2 mb-4">
              <VFileInput 
                v-model="fileInput[idx]"
                :label="idx === 0 ? (editId ? 'Ubah Screenshot Utama (Kosongkan jika tidak diubah)' : 'Screenshot Utama') : 'Screenshot Tambahan'"
                accept="image/*"
                variant="outlined"
                hide-details
                prepend-icon=""
                prepend-inner-icon="ri-image-add-line"
                :required="!editId && idx === 0"
              />
              <VBtn 
                v-if="fileInput.length > 1" 
                icon="ri-close-line" 
                variant="tonal" 
                color="error" 
                size="small" 
                @click="fileInput.splice(idx, 1)" 
              />
            </div>
            <VBtn 
              v-if="fileInput.length < 3" 
              variant="outlined" 
              color="primary" 
              class="mb-4 d-block" 
              prepend-icon="ri-add-line"
              @click="fileInput.push([])"
            >
              Tambah Gambar Lagi
            </VBtn>
            
            <div class="d-flex justify-end gap-3 mt-4">
              <VBtn variant="tonal" color="secondary" @click="isDialogVisible = false" class="px-6 rounded-lg">Batal</VBtn>
              <VBtn type="submit" color="primary" variant="elevated" :loading="isSubmitting" class="px-6 rounded-lg font-weight-bold">
                {{ editId ? 'Simpan Perubahan' : 'Posting Akun' }}
              </VBtn>
            </div>
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
  </VContainer>
</template>

<style scoped>
.border-t-primary {
  border-top: 4px solid rgb(var(--v-theme-primary)) !important;
}
.custom-table tbody tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.03) !important;
}
</style>
