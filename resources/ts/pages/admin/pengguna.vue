<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref<any[]>([])
const loading = ref(true)
const searchQuery = ref('')

const fetchItems = async () => {
  try {
    const res = await axios.get('/api/admin/pengguna')
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

const filteredItems = computed(() => {
  if (!searchQuery.value) return items.value
  const query = searchQuery.value.toLowerCase()
  return items.value.filter((item: any) => 
    item.nama_lengkap?.toLowerCase().includes(query) || 
    item.username?.toLowerCase().includes(query) ||
    item.email?.toLowerCase().includes(query)
  )
})

const formatRupiah = (angka: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka)
}

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

// Generate random color for avatar based on name
const getAvatarColor = (name: string) => {
  const colors = ['primary', 'success', 'info', 'warning', 'error']
  const index = name.length % colors.length
  return colors[index]
}
</script>

<template>
  <div class="pa-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-group-line" color="primary" />
          Data Member
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Kelola dan pantau seluruh pelanggan terdaftar di platform Anda.</p>
      </div>
      
      <div style="max-width: 300px; width: 100%;">
        <VTextField
          v-model="searchQuery"
          placeholder="Cari nama, username, email..."
          prepend-inner-icon="ri-search-line"
          variant="outlined"
          density="compact"
          hide-details
          class="bg-surface rounded-lg"
        />
      </div>
    </div>

    <!-- Data Table Card -->
    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data member...</div>
      </VCardText>
      
      <VTable v-else hover class="text-no-wrap custom-table">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Member</th>
            <th class="text-uppercase text-caption font-weight-bold">Kontak</th>
            <th class="text-uppercase text-caption font-weight-bold text-right">Saldo Wallet</th>
            <th class="text-uppercase text-caption font-weight-bold text-right">Loyalty Poin</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Bergabung</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredItems" :key="item.id" class="transition-swing">
            <td class="py-3">
              <div class="d-flex align-center gap-3">
                <VAvatar :color="getAvatarColor(item.nama_lengkap)" variant="tonal" size="40" class="font-weight-bold">
                  <VImg v-if="item.foto_profil" :src="`/uploads/profil/${item.foto_profil}`" cover />
                  <span v-else>{{ item.nama_lengkap.charAt(0).toUpperCase() }}</span>
                </VAvatar>
                <div>
                  <div class="font-weight-bold text-high-emphasis">{{ item.nama_lengkap }}</div>
                  <div class="text-caption text-medium-emphasis d-flex align-center gap-1 mt-n1">
                    <VIcon icon="ri-at-line" size="12" />
                    {{ item.username }}
                  </div>
                </div>
              </div>
            </td>
            <td>
              <div class="text-body-2 font-weight-medium">{{ item.email }}</div>
              <div class="text-caption text-medium-emphasis d-flex align-center gap-1">
                <VIcon icon="ri-whatsapp-line" size="12" class="text-success" />
                {{ item.no_telepon || 'Belum diatur' }}
              </div>
            </td>
            <td class="text-right">
              <div class="font-weight-bold text-primary">{{ formatRupiah(item.saldo || 0) }}</div>
            </td>
            <td class="text-right">
              <VChip color="warning" variant="elevated" size="small" class="font-weight-bold">
                <VIcon start icon="ri-vip-diamond-fill" size="12" />
                {{ Number(item.poin || 0).toLocaleString('id-ID') }} Pts
              </VChip>
            </td>
            <td class="text-center">
              <div class="text-caption font-weight-medium">{{ formatDate(item.created_at) }}</div>
            </td>
          </tr>
          <tr v-if="filteredItems.length === 0">
            <td colspan="5" class="text-center pa-8">
              <VIcon icon="ri-search-eye-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Tidak ada member ditemukan</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
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
