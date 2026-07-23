<script setup lang="ts">
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2LoginMaskDark from '@images/pages/auth-v2-login-mask-dark.png'
import authV2LoginMaskLight from '@images/pages/auth-v2-login-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const router = useRouter()
const route = useRoute()
const redirectPath = computed(() => (route.query.redirect as string) || null)

const form = ref({
  username: '',
  password: '',
  remember: false,
})

const isLoading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const res = await axios.post('/api/login', {
      username: form.value.username,
      password: form.value.password
    })

    if (res.data.success) {
      // Simpan token
      localStorage.setItem('admin_token', res.data.data.token)
      localStorage.setItem('user_data', JSON.stringify(res.data.data.user))
      
      // Redirect sesuai role
      if (redirectPath.value) {
        // Jika ada redirect param (misal dari halaman produk), balik ke sana
        router.push(decodeURIComponent(redirectPath.value))
      } else if (res.data.data.user.role === 'Pelanggan') {
        router.push('/')
      } else {
        router.push('/admin/dashboard')
      }
    }
  } catch (error: any) {
    if (error.response && error.response.data) {
      errorMessage.value = error.response.data.message || 'Login gagal'
    } else {
      errorMessage.value = 'Terjadi kesalahan pada server'
    }
  } finally {
    isLoading.value = false
  }
}

const isPasswordVisible = ref(false)
const authV2LoginMask = useGenerateImageVariant(authV2LoginMaskLight, authV2LoginMaskDark)
const authV2LoginIllustration = useGenerateImageVariant (authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
</script>

<template>
  <div class="auth-wrapper-container">
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
            :src="authV2LoginIllustration"
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
              Selamat Datang di <span class="text-capitalize">{{ themeConfig.app.title }}! 👋🏻</span>
            </h4>

            <p class="mb-0">
              Login ke akun Anda untuk bertransaksi dan mengelola pesanan.
            </p>
          </VCardText>

          <VCardText>
            <VAlert v-if="errorMessage" type="error" class="mb-4" variant="tonal">
              {{ errorMessage }}
            </VAlert>

            <VForm @submit.prevent="handleLogin">
              <VRow>
                <!-- username -->
                <VCol cols="12">
                  <VTextField
                    v-model="form.username"
                    autofocus
                    label="Username"
                    type="text"
                    placeholder="admin"
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
                    autocomplete="password"
                    :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                    required
                  />

                  <!-- remember me checkbox -->
                  <div class="d-flex align-center justify-space-between flex-wrap my-6 gap-x-2">
                    <VCheckbox
                      v-model="form.remember"
                      label="Remember me"
                    />
                  </div>

                  <!-- login button -->
                  <VBtn
                    block
                    type="submit"
                    :loading="isLoading"
                  >
                    Login
                  </VBtn>

                  <div class="text-center mt-4 text-body-2">
                    Belum punya akun? <RouterLink to="/register" class="text-primary font-weight-bold">Buat Akun</RouterLink>
                  </div>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
