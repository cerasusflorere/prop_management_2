<template>
  <nav class="navbar">
    <div class="navbar__brand" @click="scrollTop">
      小道具管理アプリ
    </div>
    <div class="navbar__menu">
      <div class="navbar__item">
        <RouterLink class="button" to="/">
          閲覧- 使用シーン一覧
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/show_prop">
          閲覧- 小道具一覧
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/register_scene">
          登録- 使用シーン
        </RouterLink>
      </div>
      <div class="navbar__item">
        <RouterLink class="button" to="/register_prop">
          登録- 小道具
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