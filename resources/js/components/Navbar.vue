<template>
  <nav class="navbar">
    <div class="navbar__brand" @click="scrollTop">
      小道具管理アプリ
    </div>
    <div class="navbar__menu">
      <div class="navbar__item">
        <RouterLink class="button" to="/">
          <i class="fas fa-eye fa-fw"></i>使用シーン
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/show_prop">
          <i class="fas fa-eye fa-fw"></i>小道具
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/register_scene">
          <i class="fas fa-pen fa-fw"></i>使用シーン
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/register_prop">
          <i class="fas fa-pen fa-fw"></i>小道具
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/setting">
          <i class="fas fa-eye fa-fw"></i>設定
        </RouterLink>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  methods: {
    scrollTop: function(){
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    }
  }
}
  
</script>