<template>
  <nav class="navbar">
    <div class="navbar__brand" @click="scrollTop">
      小道具管理アプリ
    </div>
    <!-- ここからハンバーガーメニュー -->
    <div class="hamburger-menu">
      <input type="checkbox" id="menu-btn-check"/>
      <label for="menu-btn-check" class="menu-btn"><span></span></label>
      <div class="menu-content">
        <ul>
          <li>
            <RouterLink class="button" to="/">
              <i class="fas fa-eye fa-fw"></i>使用シーン
            </RouterLink>
          </li>

          <li>
            <RouterLink class="button" to="/show_prop">
              <i class="fas fa-eye fa-fw"></i>小道具
            </RouterLink>
          </li>

          <li>
            <RouterLink class="button" to="/register_scene">
              <i class="fas fa-pen fa-fw"></i>使用シーン
            </RouterLink>
          </li>

          <li>
            <RouterLink class="button" to="/register_prop">
              <i class="fas fa-pen fa-fw"></i>小道具
            </RouterLink>
          </li>

          <li>
            <RouterLink class="button" to="/setting">
              <i class="fas fa-eye fa-fw"></i>設定
            </RouterLink>
          </li>
        </ul>
      </div>
        <!-- ここまでハンバーガーメニュー -->
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