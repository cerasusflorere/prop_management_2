<template>
  <nav class="navbar">
    <div class="navbar__brand" @click="scrollTop">
      小道具管理アプリ
    </div>
    <div class="countdown_and_hamburger">
      <!-- カウントダウン -->
      <div class="countdown__box">
        <div class="countdown__message_area">
          <div v-if="!countdown_message">
            <small>あと</small>
            <span :class="(countdown_day < 8) ? 'countdown_number countdown_number_red' : 'countdown_number'">{{ countdown_day }}</span>
            <span class="countdown_day">日</span>
          </div>
          <div v-else class="countdown_message">{{ countdown_message }}</div>
          
        </div>
        <div class="countdown__image_area">
          <img v-if="countdown_message" src='image/Ireland.png'></img>
          <img v-else-if="countdown_day < 8" src='image/red.png'></img>
          <img v-else src='image/gray.png'></img>
        </div>
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
                <i class="fas fa-paper-plane fa-fw"></i>使用シーン
              </RouterLink>
            </li>

            <li>
              <RouterLink class="button" to="/register_prop">
                <i class="fas fa-paper-plane fa-fw"></i>小道具
              </RouterLink>
            </li>

            <li>
              <RouterLink class="button" to="/setting">
                <i class="fas fa-cog fa-fw"></i>設定
              </RouterLink>
            </li>
          </ul>
        </div>
        <!-- ここまでハンバーガーメニュー -->
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  // データ
  data() {
    return {
      countdown_day: 0,
      countdown_message: null
    }    
  },

  watch: {
    $route: {
      async handler () {
        await this.getCountDown();
      },
      immediate: true
    }
  },

  methods: {
    scrollTop: function(){
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    },

    // 今日の日付と公演までの日数
    async getCountDown() {
      let today = new Date();
      const year = today.getFullYear();
      const month = today.getMonth()+1;
      const day = today.getDate();
      today = new Date(year, month-1, day); // 時刻があるとずれる
      // today = new Date(year, month-1-1, day+1); // 時刻があるとずれる

      let passo_day;
      let passo;
      let guraduation_day;
      let guraduation; 
      if(month<4){
        // 1月～3月
        passo_day = await this.getDateFromWeek(year-1, 11, 1, 0); // 11月第1日曜日
        passo = new Date(year-1, 11-1, passo_day);
        guraduation_day = await this.getDateFromWeek(year, 3, 1, 0); // 3月第1日曜日
        guraduation = new Date(year, 3-1, guraduation_day);
      }else{
        // 4月～12月
        passo_day = await this.getDateFromWeek(year, 11, 1, 0); // 11月第1日曜日
        passo = new Date(year, 11-1, passo_day);
        guraduation_day = await this.getDateFromWeek(year+1, 3, 1, 0); // 3月第1日曜日
        guraduation = new Date(year+1, 3-1, guraduation_day);
      }

      if(today <= passo){
        // 中間発表まで
        this.countdown_day = Math.floor((passo.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
      }else{
        // 卒業公演まで
        this.countdown_day = Math.floor((guraduation.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
      }

      if(this.countdown_day === 1){
        // 発表1日目
        this.countdown_message = '1日目!';
      }else if(this.countdown_day === 0){
        // 発表2日目
        this.countdown_message = '最終日!';
      }

      this.countdown_day--;
    },

    // 第1日曜日の日付を返す
    async getDateFromWeek(year, month_origin, turn, weekday) {
      const month = month_origin - 1;
      // 月初の日
      const firstDateOfMonth = new Date(year, month, 1);
      // 月初の曜日
      const firstDayOfWeek = firstDateOfMonth.getDay();
 
      // 指定された曜日が最初に出現する日付を求める
      let firstWeekdayDate = null;
      if (firstDayOfWeek == weekday) {
        // 月初の曜日が指定曜日の時
        firstWeekdayDate = new Date(year, month, 1);
      } else if (firstDayOfWeek < weekday) {
        // 月初の曜日 < 指定の曜日の時
        firstWeekdayDate = new Date(year, month, 1 + (weekday - firstDayOfWeek));
      } else if (weekday < firstDayOfWeek) {
        // 指定の曜日 < 月初の曜日の時
        firstWeekdayDate = new Date(year, month, 1 + (7 - (firstDayOfWeek - weekday)));
      }

      // 第○の指定の分だけ日数を足す
      const firstWeekDay = firstWeekdayDate.getDate();
      const specifiedDate = new Date(year, month, firstWeekDay + 7 * (turn - 1)); // yyyy年mm月dd日
      if (specifiedDate.getMonth() != month) {
        return null;
      }
      return firstWeekDay + 7 * (turn - 1);
    }
  }
}  
</script>