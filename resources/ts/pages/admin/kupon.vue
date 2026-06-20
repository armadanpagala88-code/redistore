<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref<any[]>([])
const loading = ref(true)

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/admin/kupon')
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
  kode_voucher: '',
  tipe: 'Persen',
  jumlah_potongan: 0,
  minimal_beli: 0,
  kuota: 1,
  tgl_kadaluarsa: '',
  is_aktif: true
})
const isEdit = ref(false)

const openAddModal = () => {
  isEdit.value = false
  form.value = { 
    id: '', 
    kode_voucher: '', 
    tipe: 'Persen', 
    jumlah_potongan: 0, 
    minimal_beli: 0, 
    kuota: 1, 
    tgl_kadaluarsa: '', 
    is_aktif: true 
  }
  isDialogVisible.value = true
}

const openEditModal = (item: any) => {
  isEdit.value = true
  form.value = { 
    id: item.id, 
    kode_voucher: item.kode_voucher, 
    tipe: item.tipe, 
    jumlah_potongan: item.jumlah_potongan, 
    minimal_beli: item.minimal_beli, 
    kuota: item.kuota, 
    tgl_kadaluarsa: item.tgl_kadaluarsa, 
    is_aktif: item.is_aktif == 1 
  }
  isDialogVisible.value = true
}

const saveItem = async () => {
  try {
    const payload = { ...form.value, is_aktif: form.value.is_aktif ? 1 : 0 }
    
    if (isEdit.value) {
      await axios.put(`/api/admin/kupon/${form.value.id}`, payload)
    } else {
      await axios.post('/api/admin/kupon', payload)
    }
    isDialogVisible.value = false
    fetchItems()
  } catch (e: any) {
    if (e.response && e.response.data && e.response.data.message) {
      alert(e.response.data.message)
    } else {
      alert('Gagal menyimpan data')
    }
  }
}

const deleteItem = async (id: string) => {
  if (!confirm('Yakin ingin menghapus kupon ini?')) return
  try {
    await axios.delete(`/api/admin/kupon/${id}`)
    fetchItems()
    alert('Kupon berhasil dihapus')
  } catch (error: any) {
    alert('Gagal menghapus data')
  }
}

const formatRupiah = (value: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}
</script>

<template>
  <div class="pa-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-ticket-2-line" color="primary" />
          Manajemen Kupon (Voucher)
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Kelola kode diskon/kupon untuk member.</p>
      </div>
      <VBtn color="primary" prepend-icon="ri-add-line" @click="openAddModal" class="rounded-lg font-weight-bold">Tambah Kupon</VBtn>
    </div>

    <!-- Data Table Card -->
    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data kupon...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Kode Voucher</th>
            <th class="text-uppercase text-caption font-weight-bold">Potongan</th>
            <th class="text-uppercase text-caption font-weight-bold">Minimal Beli</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Kuota</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Expired</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="transition-swing">
            <td class="py-3 font-weight-bold text-primary">{{ item.kode_voucher }}</td>
            <td>
              <div class="text-body-2 text-high-emphasis">
                {{ item.tipe === 'Persen' ? `${item.jumlah_potongan}%` : formatRupiah(item.jumlah_potongan) }}
              </div>
            </td>
            <td>{{ formatRupiah(item.minimal_beli) }}</td>
            <td class="text-center">{{ item.kuota }}</td>
            <td class="text-center">{{ item.tgl_kadaluarsa }}</td>
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
            <td colspan="7" class="text-center pa-8">
              <VIcon icon="ri-ticket-2-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada data kupon</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Edit/Tambah -->
    <VDialog v-model="isDialogVisible" max-width="500">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold">
          {{ isEdit ? 'Edit Kupon' : 'Tambah Kupon Baru' }}
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isDialogVisible = false" />
        </VCardTitle>
        <VCardText class="px-6 pb-6 pt-4">
          <VForm @submit.prevent="saveItem">
            <VTextField v-model="form.kode_voucher" label="Kode Kupon" placeholder="PROMO2024" required variant="outlined" density="comfortable" class="mb-4" />
            
            <VRow>
              <VCol cols="6">
                <VSelect v-model="form.tipe" :items="['Persen', 'Nominal']" label="Tipe Diskon" required variant="outlined" density="comfortable" class="mb-4" />
              </VCol>
              <VCol cols="6">
                <VTextField v-model.number="form.jumlah_potongan" type="number" label="Jumlah Diskon" required variant="outlined" density="comfortable" class="mb-4" />
              </VCol>
            </VRow>

            <VTextField v-model.number="form.minimal_beli" type="number" label="Minimal Pembelian (Rp)" required variant="outlined" density="comfortable" class="mb-4" />
            
            <VRow>
              <VCol cols="6">
                <VTextField v-model.number="form.kuota" type="number" label="Kuota Penggunaan" required variant="outlined" density="comfortable" class="mb-4" />
              </VCol>
              <VCol cols="6">
                <VTextField v-model="form.tgl_kadaluarsa" type="date" label="Tgl Kadaluarsa" required variant="outlined" density="comfortable" class="mb-4" />
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
