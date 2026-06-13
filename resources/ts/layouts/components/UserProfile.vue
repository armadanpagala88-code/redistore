<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'

const router = useRouter()
const userData = ref<any>({
  nama_lengkap: 'Admin',
  role: 'Admin'
})

onMounted(() => {
  const storedUser = localStorage.getItem('user_data')
  if (storedUser) {
    userData.value = JSON.parse(storedUser)
  }
})

const logout = async () => {
  try {
    await axios.post('/api/logout')
  } catch (e) {
    console.error(e)
  } finally {
    localStorage.removeItem('admin_token')
    localStorage.removeItem('user_data')
    router.push('/login')
  }
}
</script>

<template>
  <VBadge
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
      color="primary"
    >
      <VIcon icon="ri-user-line" color="white" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4">
            <div class="d-flex gap-x-2 align-center">
              <VAvatar color="primary">
                <VIcon icon="ri-user-line" color="white" />
              </VAvatar>

              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ userData.nama_lengkap }}
                </div>
                <div class="text-capitalize text-caption text-disabled">
                  {{ userData.role }}
                </div>
              </div>
            </div>
          </VListItem>

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <VDivider class="my-1" />

            <VListItem class="px-4">
              <VBtn
                block
                color="error"
                size="small"
                append-icon="ri-logout-box-r-line"
                @click="logout"
              >
                Logout
              </VBtn>
            </VListItem>
          </PerfectScrollbar>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>
