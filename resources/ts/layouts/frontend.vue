<script lang="ts" setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const { injectSkinClasses } = useSkins()
injectSkinClasses()

const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref<any>(null)

watch([isFallbackStateActive, refLoadingIndicator], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()

  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })
</script>

<template>
  <AppLoadingIndicator ref="refLoadingIndicator" />

  <VApp class="premium-layout theme--dark">
    <!-- Top Navigation Bar - Glassmorphism -->
    <VAppBar elevation="0" height="80" class="glass-navbar border-b border-opacity-10">
      <VContainer class="d-flex align-center w-100 h-100" style="max-width: 1200px; padding: 0 16px;">
        <RouterLink to="/" class="d-flex align-center gap-x-3 text-decoration-none">
          <VNodeRenderer :nodes="themeConfig.app.logo" />
          <span class="text-h5 font-weight-bold text-white text-uppercase tracking-wider">
            {{ themeConfig.app.title }}
          </span>
        </RouterLink>

        <VSpacer />

        <!-- Nav Items -->
        <div class="d-none d-md-flex align-center gap-4">
          <VBtn variant="text" to="/" prepend-icon="ri-gamepad-line" class="text-white font-weight-medium nav-btn">
            Semua Game
          </VBtn>
          <VBtn variant="flat" color="primary" to="/login" prepend-icon="ri-user-settings-line" class="font-weight-bold rounded-pill px-6 login-btn">
            Login Admin
          </VBtn>
        </div>
        
        <!-- Mobile Menu Icon -->
        <VBtn icon="ri-user-settings-line" variant="tonal" color="primary" class="d-md-none" to="/login" />
      </VContainer>
    </VAppBar>

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
        {{ themeConfig.app.title }}
      </div>
      <div class="text-body-2 text-grey-lighten-1 text-center mb-4 max-w-600">
        Platform Top Up Game Termurah & Terpercaya di Indonesia. Proses instan, otomatis, dan beroperasi 24 jam nonstop.
      </div>
      <div class="text-caption text-grey-darken-1">
        &copy; {{ new Date().getFullYear() }} <span class="text-primary">{{ themeConfig.app.title }}</span>. All rights reserved.
      </div>
    </VFooter>
  </VApp>
</template>

<style lang="scss">
.premium-layout {
  background-color: #0b0f19 !important; /* Deep dark blue */
  color: #ffffff;
}

/* Override default typography colors for dark theme */
.premium-layout .text-high-emphasis,
.premium-layout .text-medium-emphasis {
  color: rgba(255, 255, 255, 0.85) !important;
}

.main-content-area {
  position: relative;
  overflow: hidden;
}

/* Glassmorphism Navbar */
.glass-navbar {
  background: rgba(11, 15, 25, 0.7) !important;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}

/* Glowing Orbs Background */
.bg-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  z-index: 0;
  opacity: 0.4;
}

.bg-glow-1 {
  top: -10%;
  left: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(98,54,255,0.6) 0%, rgba(0,0,0,0) 70%);
}

.bg-glow-2 {
  bottom: 10%;
  right: -5%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(255,54,129,0.4) 0%, rgba(0,0,0,0) 70%);
}

/* Premium Footer */
.premium-footer {
  background-color: #080b13 !important;
  border-top-color: rgba(255, 255, 255, 0.05) !important;
}

.max-w-600 {
  max-width: 600px;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

.nav-btn {
  opacity: 0.8;
  transition: all 0.3s ease;
  &:hover {
    opacity: 1;
    transform: translateY(-2px);
  }
}

.login-btn {
  transition: all 0.3s ease;
  &:hover {
    box-shadow: 0 0 15px rgba(var(--v-theme-primary), 0.5);
    transform: translateY(-2px);
  }
}
</style>
