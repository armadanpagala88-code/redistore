<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'

definePage({
  meta: {
    layout: 'default', // Admin layout
  },
})

const tickets = ref<any[]>([])
const loading = ref(true)

// Dialog State
const isDialogVisible = ref(false)
const selectedTicket = ref<any>(null)
const selectedTicketDetails = ref<any>(null)
const isLoadingDetails = ref(false)
const newMessage = ref('')
const isSending = ref(false)
const messagesContainer = ref<any>(null)

const fetchTickets = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/tickets', {
      headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
    })
    tickets.value = res.data.data
  } catch (error) {
    console.error('Error fetching tickets:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTickets()
})

const openTicketDetails = async (ticket: any) => {
  selectedTicket.value = ticket
  isDialogVisible.value = true
  isLoadingDetails.value = true
  selectedTicketDetails.value = null
  
  try {
    const res = await axios.get(`/api/tickets/${ticket.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
    })
    selectedTicketDetails.value = res.data.data
    scrollToBottom()
  } catch (error) {
    console.error('Error fetching ticket details', error)
  } finally {
    isLoadingDetails.value = false
  }
}

const sendReply = async () => {
  if (!newMessage.value.trim() || !selectedTicketDetails.value) return

  isSending.value = true
  try {
    const res = await axios.post(`/api/tickets/${selectedTicketDetails.value.id}/reply`, {
      pesan: newMessage.value
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
    })
    selectedTicketDetails.value.replies.push(res.data.data)
    selectedTicketDetails.value.status = 'In Progress' // Optimistic UI
    newMessage.value = ''
    scrollToBottom()
    
    // Refresh list in background
    fetchTickets()
  } catch (error) {
    console.error('Error sending reply:', error)
  } finally {
    isSending.value = false
  }
}

const closeTicket = async () => {
  if (!selectedTicketDetails.value) return
  if (!confirm('Apakah Anda yakin ingin menutup tiket ini?')) return

  try {
    await axios.post(`/api/admin/tickets/${selectedTicketDetails.value.id}/close`, {}, {
      headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
    })
    selectedTicketDetails.value.status = 'Closed'
    fetchTickets()
  } catch (error) {
    console.error('Error closing ticket', error)
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      const container = messagesContainer.value.$el || messagesContainer.value
      container.scrollTop = container.scrollHeight
    }
  })
}

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
</script>

<template>
  <div>
    <VCard title="Manajemen Support Ticket">
      <VCardText>
        Kelola dan balas keluhan pelanggan dari sini. Tiket yang dibalas oleh Admin akan otomatis berstatus In Progress.
      </VCardText>

      <VCardText>
        <VTable class="text-no-wrap border rounded">
          <thead>
            <tr>
              <th class="text-uppercase">ID Tiket</th>
              <th class="text-uppercase">Pengguna</th>
              <th class="text-uppercase">Subjek</th>
              <th class="text-uppercase">Prioritas</th>
              <th class="text-uppercase">Status</th>
              <th class="text-uppercase">Diperbarui</th>
              <th class="text-uppercase">Aksi</th>
            </tr>
          </thead>
          
          <tbody v-if="loading">
            <tr>
              <td colspan="7" class="text-center pa-4">
                <VProgressCircular indeterminate color="primary" />
              </td>
            </tr>
          </tbody>
          
          <tbody v-else-if="tickets.length > 0">
            <tr v-for="ticket in tickets" :key="ticket.id">
              <td class="font-weight-medium">#{{ ticket.ticket_number }}</td>
              <td>
                <div class="font-weight-medium">{{ ticket.user?.nama_lengkap }}</div>
                <div class="text-caption text-medium-emphasis">{{ ticket.user?.email || '-' }}</div>
              </td>
              <td>
                <div class="text-truncate" style="max-width: 200px;">{{ ticket.subjek }}</div>
                <div class="text-caption text-medium-emphasis">{{ ticket.replies_count }} Balasan</div>
              </td>
              <td>
                <VChip size="small" :color="getPriorityColor(ticket.prioritas)" variant="tonal">
                  {{ ticket.prioritas }}
                </VChip>
              </td>
              <td>
                <VChip size="small" :color="getStatusColor(ticket.status)">
                  {{ ticket.status }}
                </VChip>
              </td>
              <td>{{ new Date(ticket.updated_at).toLocaleDateString('id-ID') }}</td>
              <td>
                <VBtn size="small" color="primary" variant="tonal" @click="openTicketDetails(ticket)">
                  Balas
                </VBtn>
              </td>
            </tr>
          </tbody>
          
          <tbody v-else>
            <tr>
              <td colspan="7" class="text-center pa-8 text-medium-emphasis">
                Belum ada tiket bantuan.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>

    <!-- Dialog Reply Ticket -->
    <VDialog v-model="isDialogVisible" max-width="800">
      <VCard :loading="isLoadingDetails" class="d-flex flex-column" height="85vh">
        <VToolbar color="surface" class="border-b" density="compact">
          <VToolbarTitle class="font-weight-bold text-h6">
            {{ selectedTicket?.subjek }}
          </VToolbarTitle>
          <VSpacer />
          <VBtn 
            v-if="selectedTicketDetails && selectedTicketDetails.status !== 'Closed'" 
            color="error" 
            variant="tonal" 
            size="small" 
            class="mr-4"
            prepend-icon="ri-close-circle-line"
            @click="closeTicket"
          >
            Tutup Tiket
          </VBtn>
          <VBtn icon="ri-close-line" variant="text" @click="isDialogVisible = false" />
        </VToolbar>

        <VCardText 
          ref="messagesContainer" 
          class="flex-grow-1 overflow-y-auto bg-grey-lighten-4 pa-6"
        >
          <div v-if="isLoadingDetails" class="text-center mt-10">
            <VProgressCircular indeterminate color="primary" />
          </div>

          <div v-else-if="selectedTicketDetails" class="d-flex flex-column gap-4">
            <div 
              v-for="reply in selectedTicketDetails.replies" 
              :key="reply.id"
              class="d-flex"
              :class="reply.user?.role === 'Admin' || reply.user?.role === 'Owner' ? 'justify-end' : 'justify-start'"
            >
              <div class="d-flex gap-3" :class="reply.user?.role === 'Admin' || reply.user?.role === 'Owner' ? 'flex-row-reverse' : 'flex-row'" style="max-width: 80%;">
                
                <VAvatar :color="reply.user?.role === 'Admin' || reply.user?.role === 'Owner' ? 'warning' : 'primary'" variant="tonal" size="40">
                  <span class="text-caption font-weight-bold">{{ reply.user?.nama_lengkap.charAt(0) || 'U' }}</span>
                </VAvatar>

                <div 
                  class="rounded-lg pa-4 elevation-1"
                  :class="[
                    reply.user?.role === 'Admin' || reply.user?.role === 'Owner' ? 'bg-warning text-white' : 'bg-white text-high-emphasis'
                  ]"
                >
                  <div class="d-flex justify-space-between align-center mb-2 gap-4">
                    <div class="text-caption font-weight-bold opacity-90">
                      {{ reply.user?.nama_lengkap }} ({{ reply.user?.role }})
                    </div>
                    <div class="text-caption opacity-70" style="font-size: 0.7rem !important;">
                      {{ new Date(reply.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                  </div>
                  
                  <div class="text-body-1" style="white-space: pre-wrap;">{{ reply.pesan }}</div>
                </div>
              </div>
            </div>
          </div>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-4 bg-white" v-if="selectedTicketDetails && selectedTicketDetails.status !== 'Closed'">
          <VForm @submit.prevent="sendReply" class="w-100 d-flex align-start gap-2">
            <VTextarea
              v-model="newMessage"
              placeholder="Ketik balasan admin di sini..."
              variant="outlined"
              density="compact"
              hide-details
              bg-color="grey-lighten-4"
              rows="2"
              auto-grow
              class="flex-grow-1"
            />
            <VBtn 
              type="submit" 
              color="primary" 
              variant="elevated" 
              height="56"
              class="px-6"
              :loading="isSending"
              :disabled="!newMessage.trim()"
            >
              Balas
              <VIcon icon="ri-send-plane-fill" class="ml-2" />
            </VBtn>
          </VForm>
        </VCardActions>
        
        <VCardText v-else-if="selectedTicketDetails?.status === 'Closed'" class="bg-grey-lighten-3 text-center py-4 text-medium-emphasis">
          <VIcon icon="ri-lock-line" class="mr-2" />
          Tiket ini telah ditutup.
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
