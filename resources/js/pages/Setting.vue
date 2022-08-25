<template>
  <div class="panel">
      <!-- 区分登録 -->
      <form class="form"  @submit.prevent="register_section">
        <!-- 区分 -->
        <label for="section">区分</label>
        <input id="section" type="text" class="form__item" v-model="registerForm_section">

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>

      <!-- 登場人物登録 -->
      <form class="form" @submit.prevent="register_character">
        <!-- 区分-->
        <label for="character_attr">区分</label>
        <select class="form__item" v-model="registerForm_character.section">
          <option disabled value="">区分</option>
          <option v-for="section in optionSections" v-bind:value="section.id">
            {{ section.section }}
          </option>
        </select>

        <!-- 登場人物 -->
        <label for="character">登場人物</label>
        <input id="character" type="text" class="form__item" v-model="registerForm_character.character">

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>

      <!-- 持ち主登録 -->
      <form class="form" @submit.prevent="register_owner">
        <!-- 持ち主 -->
        <label for="owner">持ち主</label>
        <input id="owner" type="text" class="form__item" v-model="registerForm_owner">

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>
  </div>
</template>

<script>

export default {
  data() {
    return {
      // 取得するデータ
      optionSections: [],
      // 登録内容
      registerForm_section: null,
      registerForm_character: {
        character: null,
        section: null
      },
      registerForm_owner: null
    }
  },
  methods: {
    // 区分を取得
    async fetchSections () {
      const response = await axios.get('/api/informations/sections')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.optionSections = response.data
    },

    // 登録する
    async register_section () {
      const response = await axios.post('/api/informations/sections', {
        section: this.registerForm_section
      })

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }

      this.registerForm_section = null

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '区分が登録されました！',
        timeout: 6000
      })
    },

    async register_character () {
      const response = await axios.post('/api/informations/characters', {
        section_id: this.registerForm_character.section,
        name: this.registerForm_character.character
      })

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }
      
      this.registerForm_character.section = null
      this.registerForm_character.character = null

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '登場人物が登録されました！',
        timeout: 6000
      })
    },
    
    async register_owner () {
      const response = await axios.post('/api/informations/owners', {
        name: this.registerForm_owner
      })

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }      
      
      this.registerForm_owner = null

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '持ち主が登録されました！',
        timeout: 6000
      })
      
    }
  },
  watch: {
    $route: {
      async handler () {
        await this.fetchSections()
      },
      immediate: true
    }
  }
}
</script>