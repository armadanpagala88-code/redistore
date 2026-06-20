<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const router = useRouter()
const tickets = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await axios.get('/api/tickets')
    tickets.value = res.data.data
  } catch (error) {
    console.error('Error fetching tickets:', error)
  } finally {
    loading.value = false
  }
})

const getStatusColor = (status: string) => {
  switch (status) {
    case 'Open': return 'warning'
    case 'In Progress': return 'info'
    case 'Closed': return 'success'
    default: return 'secondary'
  }
}

const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'High': return 'error'
    case 'Medium': return 'warning'
    case 'Low': return 'info'
    default: return 'secondary'
  }
}

const openTicket = (id: number) => {
  router.push(`/member/ticket/${id}`)
}
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12" class="d-flex justify-space-between align-center mb-6">
        <h2 class="text-h4 font-weight-bold">Pusat Bantuan (Tiket)</h2>
        <VBtn color="primary" prepend-icon="ri-add-line" to="/member/ticket/create">Buat Tiket Baru</VBtn>
      </VCol>
      
      <VCol cols="12">
        <VCard elevation="3" class="rounded-lg">
          <VCardText v-if="loading" class="text-center pa-8">
            <VProgressCircular indeterminate color="primary" />
          </VCardText>
          
          <VTable v-else-if="tickets.length > 0" class="text-no-wrap">
            <thead>
              <tr>
                <th class="text-uppercase">ID Tiket</th>
                <th class="text-uppercase">Subjek</th>
                <th class="text-uppercase">Prioritas</th>
                <th class="text-uppercase">Status</th>
                <th class="text-uppercase">Diperbarui Pada</th>
                <th class="text-uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ticket in tickets" :key="ticket.id" class="cursor-pointer" @click="openTicket(ticket.id)">
                <td class="font-weight-medium text-primary">#{{ ticket.ticket_number }}</td>
                <td>
                  <div class="font-weight-medium">{{ ticket.subjek }}</div>
                  <div class="text-caption text-medium-emphasis">
                    ID Transaksi: {{ ticket.transaksi_id || '-' }} | {{ ticket.replies_count }} Balasan
                  </div>
                </td>
                <td>
                  <VChip size="small" :color="getPriorityColor(ticket.prioritas)" variant="tonal">
                    {{ ticket.prioritas }}
                  </VChip>
                </td>
                <td>
                  <VChip size="small" :color="getStatusColor(ticket.status)" variant="elevated">
                    {{ ticket.status }}
                  </VChip>
                </td>
                <td>{{ new Date(ticket.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</td>
                <td>
                  <VBtn icon variant="text" size="small" color="primary">
                    <VIcon icon="ri-eye-line" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
          
          <VCardText v-else class="text-center pa-10 text-medium-emphasis">
            <VIcon icon="ri-customer-service-2-line" size="64" class="mb-4 opacity-50" />
            <h3 class="text-h6">Belum ada tiket bantuan</h3>
            <p>Jika Anda memiliki kendala pesanan, silakan buat tiket baru.</p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.cursor-pointer {
  cursor: pointer;
  transition: background-color 0.2s;
}
.cursor-pointer:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}
</style>
