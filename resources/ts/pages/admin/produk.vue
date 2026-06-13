<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref<any[]>([])
const categories = ref<any[]>([])
const loading = ref(true)

const selectedCategoryFilter = ref<any>(null)

const fetchItems = async () => {
  try {
    let url = '/api/admin/produk'
    if (selectedCategoryFilter.value) {
      url += `?kategori_game_id=${selectedCategoryFilter.value}`
    }
    const res = await axios.get(url)
    items.value = res.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/admin/kategori')
    categories.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  if (!localStorage.getItem('admin_token')) {
    router.push('/login')
    return
  }
  fetchCategories()
  fetchItems()
})

const isDialogVisible = ref(false)
const form = ref({
  id: '',
  kategori_game_id: null,
  nominal: '',
  harga_beli: 0,
  harga_jual: 0,
  is_aktif: true
})
const isEdit = ref(false)

const openAddModal = () => {
  isEdit.value = false
  form.value = { id: '', kategori_game_id: null, nominal: '', harga_beli: 0, harga_jual: 0, is_aktif: true }
  isDialogVisible.value = true
}

const openEditModal = (item: any) => {
  isEdit.value = true
  form.value = { 
    id: item.id, 
    kategori_game_id: item.kategori_game_id, 
    nominal: item.nominal, 
    harga_beli: item.harga_beli, 
    harga_jual: item.harga_jual, 
    is_aktif: item.is_aktif == 1 
  }
  isDialogVisible.value = true
}

const saveItem = async () => {
  try {
    const payload = {
      ...form.value,
      is_aktif: form.value.is_aktif ? 1 : 0
    }

    if (isEdit.value) {
      await axios.put(`/api/admin/produk/${form.value.id}`, payload)
    } else {
      await axios.post('/api/admin/produk', payload)
    }
    isDialogVisible.value = false
    fetchItems()
  } catch (e) {
    alert('Gagal menyimpan data')
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus produk voucher ini?')) return
  try {
    await axios.delete(`/api/admin/produk/${id}`)
    fetchItems()
    alert('Produk berhasil dihapus')
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Gagal menghapus data')
    }
  }
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const isSyncDialogVisible = ref(false)
const syncLoading = ref(false)
const syncForm = ref({
  margin_tipe: 'flat',
  margin_nilai: 2000
})

const openSyncModal = () => {
  isSyncDialogVisible.value = true
}

const doSyncDigiflazz = async () => {
  if (!confirm('Proses ini akan menarik ratusan produk dari Digiflazz. Lanjutkan?')) return
  syncLoading.value = true
  try {
    const res = await axios.post('/api/admin/digiflazz/sync', syncForm.value)
    alert(`Sync Berhasil!\n\nKategori Baru: ${res.data.data.kategori_baru}\nProduk Baru: ${res.data.data.produk_baru}\nProduk Diupdate: ${res.data.data.produk_diupdate}`)
    isSyncDialogVisible.value = false
    fetchCategories()
    fetchItems()
  } catch (e: any) {
    if (e.response && e.response.data && e.response.data.message) {
      alert(e.response.data.message)
    } else {
      alert('Gagal melakukan sinkronisasi')
    }
  } finally {
    syncLoading.value = false
  }
}
</script>

<template>
  <div class="pa-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-price-tag-3-line" color="primary" />
          Master Produk Voucher
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Kelola daftar nominal voucher dan harga jual dari masing-masing game.</p>
      </div>
      
      <div class="d-flex flex-wrap gap-3 align-center">
        <VSelect
          v-model="selectedCategoryFilter"
          :items="categories"
          item-title="nama_game"
          item-value="id"
          label="Filter Game"
          clearable
          variant="outlined"
          density="compact"
          hide-details
          class="bg-surface rounded-lg"
          style="width: 200px"
          prepend-inner-icon="ri-filter-3-line"
          @update:model-value="fetchItems"
        />
        <VBtn color="success" prepend-icon="ri-refresh-line" @click="openSyncModal" class="rounded-lg font-weight-bold" variant="tonal">Sync Digiflazz</VBtn>
        <VBtn color="primary" prepend-icon="ri-add-line" @click="openAddModal" class="rounded-lg font-weight-bold">Tambah Produk</VBtn>
      </div>
    </div>

    <!-- Data Table Card -->
    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data produk...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Game</th>
            <th class="text-uppercase text-caption font-weight-bold">Nominal</th>
            <th class="text-uppercase text-caption font-weight-bold text-right">Harga Beli</th>
            <th class="text-uppercase text-caption font-weight-bold text-right">Harga Jual</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="transition-swing">
            <td class="font-weight-bold py-3">
              <div class="d-flex align-center gap-2">
                <VAvatar size="32" variant="tonal" color="primary" rounded>
                  <VImg :src="item.kategori?.gambar_logo ? `/images/${item.kategori.gambar_logo}` : 'https://placehold.co/100x100'" cover />
                </VAvatar>
                {{ item.kategori?.nama_game }}
              </div>
            </td>
            <td>
              <div class="font-weight-medium text-high-emphasis">{{ item.nominal }}</div>
            </td>
            <td class="text-right">
              <div class="text-body-2 text-medium-emphasis">{{ formatRupiah(item.harga_beli) }}</div>
            </td>
            <td class="text-right">
              <div class="font-weight-bold text-primary">{{ formatRupiah(item.harga_jual) }}</div>
            </td>
            <td class="text-center">
              <VChip :color="item.is_aktif ? 'success' : 'error'" size="small" variant="elevated" class="font-weight-bold">
                {{ item.is_aktif ? 'Aktif' : 'Non-aktif' }}
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
              <VIcon icon="ri-price-tag-3-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada data produk</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Edit/Tambah -->
    <VDialog v-model="isDialogVisible" max-width="500">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold">
          {{ isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isDialogVisible = false" />
        </VCardTitle>
        <VCardText class="px-6 pb-6 pt-4">
          <VForm @submit.prevent="saveItem">
            <VSelect
              v-model="form.kategori_game_id"
              :items="categories"
              item-title="nama_game"
              item-value="id"
              label="Pilih Game"
              required
              variant="outlined"
              density="comfortable"
              class="mb-4"
            />
            <VTextField v-model="form.nominal" label="Nominal (Contoh: 100 Diamonds)" required variant="outlined" density="comfortable" class="mb-4" />
            
            <VRow>
              <VCol cols="12" sm="6" class="pb-0 pb-sm-3">
                <VTextField v-model.number="form.harga_beli" label="Harga Beli (Modal)" type="number" required variant="outlined" density="comfortable" class="mb-4" prepend-inner-icon="ri-money-dollar-circle-line" />
              </VCol>
              <VCol cols="12" sm="6">
                <VTextField v-model.number="form.harga_jual" label="Harga Jual" type="number" required variant="outlined" density="comfortable" class="mb-4" prepend-inner-icon="ri-money-dollar-circle-line" />
              </VCol>
            </VRow>

            <VSwitch v-model="form.is_aktif" label="Status Aktif" color="primary" class="mb-6 font-weight-medium" inset />
            
            <div class="d-flex justify-end gap-3">
              <VBtn variant="tonal" color="secondary" @click="isDialogVisible = false" class="px-6 rounded-lg">Batal</VBtn>
              <VBtn type="submit" color="primary" variant="elevated" class="px-6 rounded-lg font-weight-bold">Simpan Data</VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Dialog Sync Digiflazz -->
    <VDialog v-model="isSyncDialogVisible" max-width="500" persistent>
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold text-success">
          <div class="d-flex align-center gap-2">
            <VIcon icon="ri-download-cloud-line" />
            Sync Digiflazz
          </div>
          <VBtn v-if="!syncLoading" icon="ri-close-line" variant="text" size="small" @click="isSyncDialogVisible = false" />
        </VCardTitle>
        <VCardText class="px-6 pb-6 pt-4">
          <VAlert type="info" variant="tonal" class="mb-6 rounded-lg text-body-2" icon="ri-information-line">
            Tarik ratusan daftar harga secara instan. Atur seberapa besar keuntungan (margin) yang ingin Anda tambahkan dari harga asli Digiflazz.
          </VAlert>
          
          <VForm @submit.prevent="doSyncDigiflazz">
            <VRadioGroup v-model="syncForm.margin_tipe" inline label="Tipe Margin Keuntungan" class="mb-2 font-weight-medium text-high-emphasis">
              <VRadio label="Flat (Rupiah Tetap)" value="flat" color="success"></VRadio>
              <VRadio label="Persentase (%)" value="persen" color="success"></VRadio>
            </VRadioGroup>
            
            <VTextField 
              v-model.number="syncForm.margin_nilai" 
              :label="syncForm.margin_tipe === 'flat' ? 'Nominal Margin (Contoh: 2000)' : 'Persentase Margin (Contoh: 10)'" 
              type="number" 
              required 
              variant="outlined"
              density="comfortable"
              class="mb-6" 
              :prepend-inner-icon="syncForm.margin_tipe === 'flat' ? 'ri-money-dollar-circle-line' : 'ri-percent-line'"
            />

            <div class="d-flex justify-end gap-3">
              <VBtn variant="tonal" color="secondary" @click="isSyncDialogVisible = false" :disabled="syncLoading" class="px-6 rounded-lg">Batal</VBtn>
              <VBtn type="submit" color="success" variant="elevated" :loading="syncLoading" class="px-6 rounded-lg font-weight-bold">
                Mulai Sinkronisasi
              </VBtn>
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
