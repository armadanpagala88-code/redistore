<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'frontend',
    public: false,
  },
})

const route = useRoute()
const ticketId = route.params.id

const ticket = ref<any>(null)
const loading = ref(true)
const newMessage = ref('')
const isSending = ref(false)
const userData = ref<any>(null)
const messagesContainer = ref<any>(null)

onMounted(async () => {
  const userStr = localStorage.getItem('user_data')
  if (userStr) {
    userData.value = JSON.parse(userStr)
  }

  await fetchTicket()
})

const fetchTicket = async () => {
  try {
    const res = await axios.get(`/api/tickets/${ticketId}`)
    ticket.value = res.data.data
    scrollToBottom()
  } catch (error) {
    console.error('Error fetching ticket:', error)
  } finally {
    loading.value = false
  }
}

const sendReply = async () => {
  if (!newMessage.value.trim()) return

  isSending.value = true
  try {
    const res = await axios.post(`/api/tickets/${ticketId}/reply`, {
      pesan: newMessage.value
    })
    ticket.value.replies.push(res.data.data)
    newMessage.value = ''
    scrollToBottom()
  } catch (error) {
    console.error('Error sending reply:', error)
  } finally {
    isSending.value = false
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

const isMe = (userId: number) => {
  return userData.value && userData.value.id === userId
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'Open': return 'warning'
    case 'In Progress': return 'info'
    case 'Closed': return 'success'
    default: return 'secondary'
  }
}
</script>

<template>
  <VContainer>
    <VRow justify="center">
      <VCol cols="12" md="10">
        <VBtn variant="text" prepend-icon="ri-arrow-left-line" to="/member/ticket" class="mb-4">
          Kembali ke Riwayat
        </VBtn>

        <VCard v-if="loading" elevation="3" class="rounded-lg pa-10 text-center">
          <VProgressCircular indeterminate color="primary" />
        </VCard>

        <VCard v-else-if="ticket" elevation="4" class="rounded-lg d-flex flex-column" height="80vh">
          <VToolbar color="surface" class="border-b">
            <div class="px-4 w-100 d-flex justify-space-between align-center">
              <div>
                <h3 class="text-h6 font-weight-bold mb-1">{{ ticket.subjek }}</h3>
                <div class="d-flex gap-2 align-center text-caption text-medium-emphasis">
                  <VChip size="x-small" :color="getStatusColor(ticket.status)">{{ ticket.status }}</VChip>
                  <span>ID Tiket: #{{ ticket.ticket_number }}</span>
                  <span v-if="ticket.transaksi_id">| Transaksi: {{ ticket.transaksi_id }}</span>
                </div>
              </div>
              <VBtn icon="ri-refresh-line" variant="text" @click="fetchTicket" />
            </div>
          </VToolbar>

          <VCardText 
            ref="messagesContainer" 
            class="flex-grow-1 overflow-y-auto bg-grey-lighten-4 pa-6"
          >
            <div class="d-flex flex-column gap-4">
              <div 
                v-for="reply in ticket.replies" 
                :key="reply.id"
                class="d-flex"
                :class="isMe(reply.user_id) ? 'justify-end' : 'justify-start'"
              >
                <div class="d-flex gap-3" :class="isMe(reply.user_id) ? 'flex-row-reverse' : 'flex-row'" style="max-width: 80%;">
                  
                  <VAvatar color="primary" variant="tonal" size="40">
                    <span class="text-caption font-weight-bold">{{ reply.user?.nama_lengkap.charAt(0) || 'U' }}</span>
                  </VAvatar>

                  <div 
                    class="message-bubble rounded-lg pa-4 elevation-1"
                    :class="[
                      isMe(reply.user_id) ? 'bg-primary text-white' : 'bg-white text-high-emphasis',
                      reply.user?.role === 'Admin' || reply.user?.role === 'Owner' ? 'border-warning border-opacity-100 border-2' : ''
                    ]"
                  >
                    <div class="d-flex justify-space-between align-center mb-2 gap-4">
                      <div class="text-caption font-weight-bold opacity-90 d-flex align-center gap-1">
                        {{ isMe(reply.user_id) ? 'Anda' : reply.user?.nama_lengkap }}
                        <VIcon v-if="reply.user?.role === 'Admin' || reply.user?.role === 'Owner'" icon="ri-verified-badge-fill" color="warning" size="small" />
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

          <VCardActions class="pa-4 bg-white rounded-b-lg" v-if="ticket.status !== 'Closed'">
            <VForm @submit.prevent="sendReply" class="w-100 d-flex align-start gap-2">
              <VTextarea
                v-model="newMessage"
                placeholder="Tulis balasan Anda di sini..."
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
                Kirim
                <VIcon icon="ri-send-plane-fill" class="ml-2" />
              </VBtn>
            </VForm>
          </VCardActions>
          
          <VCardText v-else class="bg-grey-lighten-3 text-center py-4 rounded-b-lg text-medium-emphasis">
            <VIcon icon="ri-lock-line" class="mr-2" />
            Tiket ini telah ditutup dan tidak dapat menerima balasan baru.
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.message-bubble {
  word-wrap: break-word;
}
.border-2 {
  border-width: 2px !important;
  border-style: solid !important;
}
</style>
