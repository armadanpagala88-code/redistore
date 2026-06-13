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

  <div class="layout-wrapper">
    <VApp>
      <!-- Top Navigation Bar -->
      <VAppBar elevation="2" color="surface" height="70">
        <VContainer class="d-flex align-center w-100" style="max-width: 1200px; padding: 0;">
          <RouterLink to="/" class="d-flex align-center gap-x-2 text-decoration-none">
            <VNodeRenderer :nodes="themeConfig.app.logo" />
            <span class="text-h5 font-weight-bold text-primary">{{ themeConfig.app.title }}</span>
          </RouterLink>

          <VSpacer />

          <!-- Nav Items -->
          <div class="d-none d-md-flex align-center">
            <VBtn variant="text" to="/" prepend-icon="ri-home-smile-2-line" class="text-high-emphasis font-weight-medium">Beranda</VBtn>
            <VBtn variant="outlined" color="primary" to="/login" prepend-icon="ri-user-line" class="ms-4 font-weight-bold">Login Admin</VBtn>
          </div>
          
          <!-- Mobile Menu Icon (Placeholder) -->
          <VBtn icon="ri-menu-line" variant="text" class="d-md-none" />
        </VContainer>
      </VAppBar>

      <!-- Main Content Area -->
      <VMain class="bg-background">
        <VContainer class="py-8" style="max-width: 1200px; min-height: 80vh;">
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
      <VFooter color="surface" class="d-flex flex-column align-center justify-center pa-6 mt-auto">
        <div class="text-body-1 font-weight-medium mb-1">
          &copy; {{ new Date().getFullYear() }} <span class="text-primary">{{ themeConfig.app.title }}</span>. All rights reserved.
        </div>
        <div class="text-caption text-medium-emphasis text-center">
          Platform Top Up Game Termurah & Terpercaya.<br>
          Proses Cepat, Aman, dan Buka 24 Jam.
        </div>
      </VFooter>
    </VApp>
  </div>
</template>

<style lang="scss">
.layout-wrapper {
  display: flex;
  min-height: 100vh;
}
</style>
