<script setup lang="ts">
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import authV2RegisterIllustrationBorderedDark from '@images/pages/auth-v2-register-illustration-bordered-dark.png'
import authV2RegisterIllustrationBorderedLight from '@images/pages/auth-v2-register-illustration-bordered-light.png'
import authV2RegisterIllustrationDark from '@images/pages/auth-v2-register-illustration-dark.png'
import authV2RegisterIllustrationLight from '@images/pages/auth-v2-register-illustration-light.png'
import authV2LoginMaskDark from '@images/pages/auth-v2-login-mask-dark.png'
import authV2LoginMaskLight from '@images/pages/auth-v2-login-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

import { useRouter } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const router = useRouter()
const form = ref({
  nama_lengkap: '',
  username: '',
  no_telepon: '',
  password: '',
})

const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleRegister = async () => {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    const res = await axios.post('/api/register', form.value)

    if (res.data.success) {
      // Simpan token
      localStorage.setItem('admin_token', res.data.data.token)
      localStorage.setItem('user_data', JSON.stringify(res.data.data.user))
      
      successMessage.value = 'Registrasi berhasil! Mengalihkan...'
      
      setTimeout(() => {
        router.push('/')
      }, 1500)
    }
  } catch (error: any) {
    if (error.response && error.response.data) {
      errorMessage.value = error.response.data.message || 'Registrasi gagal'
    } else {
      errorMessage.value = 'Terjadi kesalahan pada server'
    }
  } finally {
    isLoading.value = false
  }
}

const isPasswordVisible = ref(false)
const authV2LoginMask = useGenerateImageVariant(authV2LoginMaskLight, authV2LoginMaskDark)
const authV2RegisterIllustration = useGenerateImageVariant (authV2RegisterIllustrationLight, authV2RegisterIllustrationDark, authV2RegisterIllustrationBorderedLight, authV2RegisterIllustrationBorderedDark, true)
</script>

<template>
  <a href="javascript:void(0)">
    <div class="app-logo auth-logo">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="app-logo-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </a>

  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="8"
      class="d-none d-md-flex align-center justify-center position-relative"
    >
      <div class="d-flex align-center justify-center pa-10">
        <img
          :src="authV2RegisterIllustration"
          class="auth-illustration w-100"
          alt="auth-illustration"
        >
      </div>
      <VImg
        :src="authV2LoginMask"
        class="d-none d-md-flex auth-footer-mask"
        alt="auth-mask"
      />
    </VCol>
    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
      style="background-color: rgb(var(--v-theme-surface));"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-5 pa-lg-7"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Daftar Akun Baru 🚀
          </h4>

          <p class="mb-0">
            Bergabunglah dengan kami untuk menikmati top up mudah dan murah!
          </p>
        </VCardText>

        <VCardText>
          <VAlert v-if="errorMessage" type="error" class="mb-4" variant="tonal">
            {{ errorMessage }}
          </VAlert>
          
          <VAlert v-if="successMessage" type="success" class="mb-4" variant="tonal">
            {{ successMessage }}
          </VAlert>

          <VForm @submit.prevent="handleRegister">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.nama_lengkap"
                  autofocus
                  label="Nama Lengkap"
                  placeholder="John Doe"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.username"
                  label="Username"
                  placeholder="johndoe"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.no_telepon"
                  label="Nomor WhatsApp"
                  placeholder="08123456789"
                  required
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  required
                />

                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  class="mt-6"
                >
                  Daftar
                </VBtn>

                <div class="text-center mt-4 text-body-2">
                  Sudah punya akun? <RouterLink to="/login" class="text-primary font-weight-bold">Login</RouterLink>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
