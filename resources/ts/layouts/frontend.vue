<script lang="ts" setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const { injectSkinClasses } = useSkins()
injectSkinClasses()

const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref<any>(null)
const isMobileDrawerOpen = ref(false)

const webSettings = ref({
  app_name: themeConfig.app.title,
  app_description: 'Platform Top Up Game Termurah & Terpercaya di Indonesia. Proses instan, otomatis, dan beroperasi 24 jam nonstop.',
  wa_number: '08123456789'
})

const userData = ref<any>(null)

onMounted(async () => {
  try {
    const res = await axios.get('/api/settings')
    if (res.data.success && res.data.data) {
      if (res.data.data.app_name) webSettings.value.app_name = res.data.data.app_name
      if (res.data.data.app_description) webSettings.value.app_description = res.data.data.app_description
      if (res.data.data.wa_number) webSettings.value.wa_number = res.data.data.wa_number
    }
    
    // Check user data
    const user = localStorage.getItem('user_data')
    if (user) {
      userData.value = JSON.parse(user)
    }
  } catch (e) {
    console.error('Failed to load settings', e)
  }
})

const handleLogout = async () => {
  try {
    await axios.post('/api/logout')
  } catch (e) {
    console.error(e)
  } finally {
    localStorage.removeItem('admin_token')
    localStorage.removeItem('user_data')
    userData.value = null
    window.location.href = '/'
  }
}

watch([isFallbackStateActive, refLoadingIndicator], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()

  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })
</script>

<template>
  <AppLoadingIndicator ref="refLoadingIndicator" />

  <VApp class="premium-layout" theme="light">
    <!-- Top Navigation Bar - Glassmorphism -->
    <VAppBar elevation="0" height="80" class="glass-navbar border-b border-opacity-10">
      <VContainer class="d-flex align-center w-100 h-100" style="max-width: 1200px; padding: 0 16px;">
        <RouterLink to="/" class="d-flex align-center gap-x-3 text-decoration-none">
          <VNodeRenderer :nodes="themeConfig.app.logo" />
          <span class="text-h5 font-weight-black text-primary text-uppercase tracking-wider" style="color: #1e293b !important;">
            {{ webSettings.app_name }}
          </span>
        </RouterLink>

        <VSpacer />

        <!-- Nav Items -->
        <div class="d-none d-md-flex align-center gap-4">
          <VBtn variant="text" to="/" prepend-icon="ri-home-4-line" class="text-white font-weight-medium nav-btn">
            Home
          </VBtn>
          <VBtn variant="text" to="/#marketplace" prepend-icon="ri-store-2-line" class="text-white font-weight-medium nav-btn">
            Marketplace
          </VBtn>
          <VBtn variant="text" to="/blog" prepend-icon="ri-article-line" class="text-white font-weight-medium nav-btn">
            Kabar Game
          </VBtn>
          
          <template v-if="userData">
            <VBtn variant="text" to="/member/jual-akun" prepend-icon="ri-store-2-line" class="text-white font-weight-medium nav-btn">
              Jual Akun
            </VBtn>
            <VBtn variant="text" to="/member/dashboard" prepend-icon="ri-dashboard-line" class="text-white font-weight-medium nav-btn">
              Dashboard Saya
            </VBtn>
            <VBtn variant="flat" color="error" @click="handleLogout" prepend-icon="ri-logout-box-r-line" class="font-weight-bold rounded-pill px-6 login-btn">
              Logout
            </VBtn>
          </template>
          <template v-else>
            <VBtn variant="text" to="/register" prepend-icon="ri-user-add-line" class="text-white font-weight-medium nav-btn">
              Daftar
            </VBtn>
            <VBtn variant="flat" color="primary" to="/login" prepend-icon="ri-user-settings-line" class="font-weight-bold rounded-pill px-6 login-btn">
              Login
            </VBtn>
          </template>
        </div>
        <!-- Mobile Menu Icon -->
        <div class="d-md-none d-flex align-center gap-2">
          <VBtn v-if="!userData" variant="flat" color="primary" size="small" to="/login" class="rounded-pill font-weight-bold mr-2">Login</VBtn>
          <VBtn icon="ri-menu-line" variant="tonal" color="primary" @click="isMobileDrawerOpen = !isMobileDrawerOpen" />
        </div>
      </VContainer>
    </VAppBar>

    <!-- Mobile Drawer -->
    <VNavigationDrawer v-model="isMobileDrawerOpen" location="right" temporary class="d-md-none">
      <div class="pa-4 d-flex justify-space-between align-center border-b border-opacity-10">
        <span class="text-h6 font-weight-bold text-primary">{{ webSettings.app_name }}</span>
        <VBtn icon="ri-close-line" variant="text" size="small" @click="isMobileDrawerOpen = false" />
      </div>
      
      <VList class="pa-2">
        <VListItem to="/" prepend-icon="ri-home-4-line" title="Home" rounded="lg" class="mb-1" @click="isMobileDrawerOpen = false" />
        <VListItem to="/#marketplace" prepend-icon="ri-store-2-line" title="Marketplace" rounded="lg" class="mb-1" @click="isMobileDrawerOpen = false" />
        <VListItem to="/blog" prepend-icon="ri-article-line" title="Kabar Game" rounded="lg" class="mb-1" @click="isMobileDrawerOpen = false" />
        
        <template v-if="userData">
          <VDivider class="my-2" />
          <VListItem to="/member/jual-akun" prepend-icon="ri-store-2-line" title="Jual Akun" rounded="lg" class="mb-1" @click="isMobileDrawerOpen = false" />
          <VListItem to="/member/dashboard" prepend-icon="ri-dashboard-line" title="Dashboard Saya" rounded="lg" class="mb-1" @click="isMobileDrawerOpen = false" />
          <VListItem @click="handleLogout" prepend-icon="ri-logout-box-r-line" title="Logout" base-color="error" rounded="lg" class="mb-1 mt-4 text-error font-weight-bold" />
        </template>
        
        <template v-else>
          <VDivider class="my-2" />
          <VListItem to="/register" prepend-icon="ri-user-add-line" title="Daftar" rounded="lg" class="mb-1" @click="isMobileDrawerOpen = false" />
          <VListItem to="/login" prepend-icon="ri-login-box-line" title="Login" rounded="lg" class="mb-1 text-primary font-weight-bold" @click="isMobileDrawerOpen = false" />
        </template>
      </VList>
    </VNavigationDrawer>

    <!-- Main Content Area -->
    <VMain class="main-content-area">
      <!-- Background elements for aesthetic -->
      <div class="bg-glow bg-glow-1"></div>
      <div class="bg-glow bg-glow-2"></div>
      
      <VContainer class="py-12 position-relative" style="max-width: 1200px; min-height: 80vh; z-index: 1;">
        <RouterView #="{Component}">
          <Suspense
            :timeout="0"
            @fallback="isFallbackStateActive = true"
            @resolve="isFallbackStateActive = false"
          >
            <Component :is="Component" />
          </Suspense>
        </RouterView>
      </VContainer>
    </VMain>
    
    <!-- Footer -->
    <VFooter class="premium-footer d-flex flex-column align-center justify-center pa-8 border-t border-opacity-10 mt-auto">
      <div class="text-h6 font-weight-bold mb-2 text-white">
        {{ webSettings.app_name }}
      </div>
      <div class="text-body-2 text-grey-lighten-1 text-center mb-4 max-w-600">
        {{ webSettings.app_description }}
      </div>
      <div class="text-caption text-grey-darken-1">
        &copy; {{ new Date().getFullYear() }} <span class="text-primary">{{ webSettings.app_name }}</span>. All rights reserved.
      </div>
    </VFooter>
  </VApp>
</template>

<style lang="scss">
.premium-layout {
  /* Layer 1 (Atas): bg-orange.jpg, Layer 2 (Bawah): bg-pattern.png */
  background-image: url('/images/bg-orange.jpg'), url('/images/bg-pattern.png') !important;
  background-position: top center, top left !important;
  background-size: 100% auto, 400px !important;
  background-attachment: fixed, fixed !important;
  background-repeat: no-repeat, repeat !important;
  background-blend-mode: multiply, normal !important;
  background-color: #f8fafc !important;
  color: #0f172a;
}

/* Override default typography colors for light theme */
.premium-layout .text-high-emphasis,
.premium-layout .text-medium-emphasis {
  color: #334155 !important;
}

.main-content-area {
  position: relative;
  overflow: hidden;
}

/* Glassmorphism Navbar (Light) */
.glass-navbar {
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
}

/* Premium Footer (Light) */
.premium-footer {
  background-color: #ffffff !important;
  border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
  color: #475569 !important;
}

.premium-footer .text-white {
  color: #0f172a !important; /* Make footer title dark */
}

.max-w-600 {
  max-width: 600px;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

.nav-btn {
  color: #334155 !important;
  font-weight: 600 !important;
  transition: all 0.3s ease;
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
    transform: translateY(-2px);
  }
}

.login-btn {
  transition: all 0.3s ease;
  color: white !important;
  &:hover {
    box-shadow: 0 0 20px rgba(var(--v-theme-primary), 0.4);
    transform: translateY(-2px);
  }
}

/* Remove dark glows */
.bg-glow {
  display: none;
}
</style>
