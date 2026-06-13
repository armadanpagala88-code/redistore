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
  } catch (e) {
    alert('Gagal menghapus data')
  }
}

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}
</script>

<template>
  <div class="pa-4">
    <div class="d-flex justify-space-between align-center mb-6">
      <h2 class="text-h4 font-weight-bold">Master Produk Voucher</h2>
      <div class="d-flex gap-4">
        <VSelect
          v-model="selectedCategoryFilter"
          :items="categories"
          item-title="nama_game"
          item-value="id"
          label="Filter Game"
          clearable
          density="compact"
          style="width: 200px"
          @update:model-value="fetchItems"
        />
        <VBtn color="primary" @click="openAddModal">Tambah Produk</VBtn>
      </div>
    </div>

    <VCard elevation="3">
      <VCardText v-if="loading" class="text-center pa-6">
        <VProgressCircular indeterminate color="primary" />
      </VCardText>
      
      <VTable v-else hover>
        <thead>
          <tr>
            <th class="text-uppercase">Game</th>
            <th class="text-uppercase">Nominal</th>
            <th class="text-uppercase text-right">Harga Beli</th>
            <th class="text-uppercase text-right">Harga Jual</th>
            <th class="text-uppercase text-center">Status</th>
            <th class="text-uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td class="font-weight-medium">
              {{ item.kategori?.nama_game }}
            </td>
            <td>{{ item.nominal }}</td>
            <td class="text-right text-medium-emphasis">{{ formatRupiah(item.harga_beli) }}</td>
            <td class="text-right font-weight-bold text-primary">{{ formatRupiah(item.harga_jual) }}</td>
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
            <td colspan="6" class="text-center pa-4">Belum ada data produk</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VDialog v-model="isDialogVisible" max-width="500">
      <VCard>
        <VCardTitle>{{ isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}</VCardTitle>
        <VCardText>
          <VForm @submit.prevent="saveItem">
            <VSelect
              v-model="form.kategori_game_id"
              :items="categories"
              item-title="nama_game"
              item-value="id"
              label="Pilih Game"
              required
              class="mb-4"
            />
            <VTextField v-model="form.nominal" label="Nominal (Contoh: 100 Diamonds)" required class="mb-4" />
            
            <VRow>
              <VCol cols="6">
                <VTextField v-model.number="form.harga_beli" label="Harga Beli (Modal)" type="number" required class="mb-4" />
              </VCol>
              <VCol cols="6">
                <VTextField v-model.number="form.harga_jual" label="Harga Jual" type="number" required class="mb-4" />
              </VCol>
            </VRow>

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
