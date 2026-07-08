<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const users = ref<any[]>([])
const loading = ref(true)

const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/admin/users')
    users.value = res.data.data
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
  fetchUsers()
})

const isDialogVisible = ref(false)
const isEdit = ref(false)
const saving = ref(false)

const form = ref({
  id: '',
  username: '',
  nama_lengkap: '',
  email: '',
  password: '',
  role: 'Pelanggan',
  is_aktif: true
})

const openAddModal = () => {
  isEdit.value = false
  form.value = {
    id: '',
    username: '',
    nama_lengkap: '',
    email: '',
    password: '',
    role: 'Pelanggan',
    is_aktif: true
  }
  isDialogVisible.value = true
}

const openEditModal = (user: any) => {
  isEdit.value = true
  form.value = {
    id: user.id,
    username: user.username,
    nama_lengkap: user.nama_lengkap,
    email: user.email,
    password: '', // Kosongkan saat edit, hanya diisi jika ingin diganti
    role: user.role,
    is_aktif: user.is_aktif == 1
  }
  isDialogVisible.value = true
}

const saveUser = async () => {
  saving.value = true
  try {
    const payload: any = {
      username: form.value.username,
      nama_lengkap: form.value.nama_lengkap,
      email: form.value.email,
      role: form.value.role,
      is_aktif: form.value.is_aktif
    }

    if (form.value.password) {
      payload.password = form.value.password
    }

    if (isEdit.value) {
      await axios.put(`/api/admin/users/${form.value.id}`, payload)
    } else {
      await axios.post('/api/admin/users', payload)
    }
    
    isDialogVisible.value = false
    fetchUsers()
  } catch (e: any) {
    const msg = e.response?.data?.message || 'Gagal menyimpan user'
    alert(msg)
  } finally {
    saving.value = false
  }
}

const deleteUser = async (id: string) => {
  if (!confirm('Yakin ingin menghapus user ini?')) return
  try {
    await axios.delete(`/api/admin/users/${id}`)
    fetchUsers()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus user')
  }
}
</script>

<template>
  <div class="pa-4">
    <div class="d-flex flex-column flex-md-row justify-space-between align-md-center mb-6 gap-4">
      <div>
        <h2 class="text-h4 font-weight-bold d-flex align-center gap-2">
          <VIcon icon="ri-user-settings-line" color="primary" />
          Manajemen User & Role
        </h2>
        <p class="text-body-2 text-medium-emphasis mb-0 mt-1">Kelola daftar Admin dan Pelanggan sistem Anda.</p>
      </div>
      <VBtn color="primary" prepend-icon="ri-user-add-line" @click="openAddModal" class="rounded-lg font-weight-bold">Tambah User</VBtn>
    </div>

    <VCard elevation="10" class="border-t-primary rounded-lg overflow-hidden">
      <VCardText v-if="loading" class="text-center pa-10">
        <VProgressCircular indeterminate color="primary" size="48" width="4" />
        <div class="mt-4 text-medium-emphasis font-weight-medium">Memuat data user...</div>
      </VCardText>
      
      <VTable v-else hover class="custom-table text-no-wrap">
        <thead class="bg-grey-lighten-4">
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">Pengguna</th>
            <th class="text-uppercase text-caption font-weight-bold">Kontak</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Role</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Status</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="transition-swing">
            <td class="py-3">
              <div class="d-flex align-center gap-3">
                <VAvatar color="primary" variant="tonal" rounded="lg" size="40">
                  <span class="font-weight-bold">{{ user.nama_lengkap.charAt(0).toUpperCase() }}</span>
                </VAvatar>
                <div>
                  <div class="font-weight-bold text-high-emphasis">{{ user.nama_lengkap }}</div>
                  <div class="text-caption text-medium-emphasis">@{{ user.username }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="text-body-2">{{ user.email }}</div>
              <div class="text-caption text-medium-emphasis" v-if="user.no_telepon">{{ user.no_telepon }}</div>
            </td>
            <td class="text-center">
              <VChip
                :color="user.role === 'Admin' ? 'error' : 'success'"
                size="small"
                variant="tonal"
                class="font-weight-bold"
              >
                {{ user.role }}
              </VChip>
            </td>
            <td class="text-center">
              <VChip :color="user.is_aktif ? 'success' : 'grey'" size="small" variant="elevated" class="font-weight-bold">
                {{ user.is_aktif ? 'Aktif' : 'Non-aktif' }}
              </VChip>
            </td>
            <td class="text-center">
              <div class="d-flex justify-center gap-2">
                <VBtn icon="ri-edit-line" variant="tonal" size="small" color="info" @click="openEditModal(user)" />
                <VBtn icon="ri-delete-bin-line" variant="tonal" size="small" color="error" @click="deleteUser(user.id)" />
              </div>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="5" class="text-center pa-8">
              <VIcon icon="ri-group-line" size="48" color="grey-lighten-1" class="mb-3" />
              <div class="text-h6 text-medium-emphasis">Belum ada data user</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog Edit/Tambah User -->
    <VDialog v-model="isDialogVisible" max-width="500">
      <VCard class="rounded-lg">
        <VCardTitle class="px-6 pt-6 d-flex justify-space-between align-center text-h5 font-weight-bold">
          {{ isEdit ? 'Edit User' : 'Tambah User Baru' }}
          <VBtn icon="ri-close-line" variant="text" size="small" @click="isDialogVisible = false" />
        </VCardTitle>
        <VCardText class="px-6 pb-6 pt-4">
          <VForm @submit.prevent="saveUser">
            <VTextField v-model="form.nama_lengkap" label="Nama Lengkap" required variant="outlined" density="comfortable" class="mb-4" />
            
            <VRow>
              <VCol cols="12" md="6" class="pb-0">
                <VTextField v-model="form.username" label="Username" required variant="outlined" density="comfortable" class="mb-4" />
              </VCol>
              <VCol cols="12" md="6" class="pb-0">
                <VTextField v-model="form.email" label="Email" type="email" required variant="outlined" density="comfortable" class="mb-4" />
              </VCol>
            </VRow>

            <VSelect
              v-model="form.role"
              :items="['Admin', 'Pelanggan']"
              label="Role (Hak Akses)"
              variant="outlined"
              density="comfortable"
              class="mb-4"
              required
            />

            <VTextField 
              v-model="form.password" 
              label="Password" 
              :placeholder="isEdit ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password'"
              type="password" 
              :required="!isEdit"
              variant="outlined" 
              density="comfortable" 
              class="mb-4" 
            />
            
            <VSwitch v-model="form.is_aktif" label="Akun Aktif" color="primary" class="mb-6 font-weight-medium" inset />
            
            <div class="d-flex justify-end gap-3">
              <VBtn variant="tonal" color="secondary" @click="isDialogVisible = false" class="px-6 rounded-lg">Batal</VBtn>
              <VBtn type="submit" color="primary" variant="elevated" :loading="saving" class="px-6 rounded-lg font-weight-bold">Simpan User</VBtn>
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
