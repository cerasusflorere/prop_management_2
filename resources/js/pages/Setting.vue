<template>
<div class="container--small">
    <ul class="tab">
      <li
        class="tab__item"
        :class="{'tab__item--active': tab === 1 }"
        @click="tab = 1"
      >一覧</li>
      <li
        class="tab__item"
        :class="{'tab__item--active': tab === 2 }"
        @click="tab = 2"
      >登録</li>
    </ul>

    <!-- 閲覧 -->
    <div class="panel" v-show="tab === 1">
      <!-- 登場人物 -->
      <label for="section_view">登場人物</label>
      <div id="section_view">
        <ul v-if="gainSet.characters.length">
          <li v-for="section in gainSet.characters">
            <div type="button" @click="openModal_sectionEdit(section.id)">{{ section.section }}</div>
            <ul v-if="section.characters.length">
              <li v-for="name in section.characters">
                <div type="button" @click="openModal_characterEdit(name.id)">{{ name.name }}</div>                
              </li>
            </ul>
          </li>
        </ul>
      </div>
      <editSection :getSection="postSection" v-show="showContent_section" @close="closeModal_sectionEdit" />
      <editCharacter :getCharacter="postCharacter" v-show="showContent_character" @close="closeModal_characterEdit" />
      
      <!-- 持ち主 -->
      <label for="owner_view">持ち主</label>
      <div id="owner_view">
        <ul v-if="gainSet.owners.length">
          <li v-for="owner in gainSet.owners">
            <div type="button" @click="openModal_ownerEdit(owner.id)">{{ owner.name }}</div> 
          </li>
        </ul>
      </div>
      <editOwner :getOwner="postOwner" v-show="showContent_owner" @close="closeModal_ownerEdit" />
      
    </div>

    <!-- 登録 -->
    <div class="panel" v-show="tab === 2">
      <!-- 区分登録 -->
      <form class="form"  @submit.prevent="register_section">
        <!-- 区分 -->
        <label for="section_input">区分</label>
        <input id="section_input" type="text" class="form__item" v-model="registerForm_section" required>

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
        <label for="character_input">登場人物</label>
        <input id="character_input" type="text" class="form__item" v-model="registerForm_character.character" required>

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>

      <!-- 持ち主登録 -->
      <form class="form" @submit.prevent="register_owner">
        <!-- 持ち主 -->
        <label for="owner_input">持ち主</label>
        <input id="owner_input" type="text" class="form__item" v-model="registerForm_owner" required>

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

import editSection from '../components/Edit_Section.vue'
import editCharacter from '../components/Edit_Character.vue'
import editOwner from '../components/Edit_Owner.vue'

export default {
  // このページの上で表示するコンポーネント
  components: {
    editSection,
    editCharacter,
    editOwner
  },
  data() {
    return {
      // タブ切り替え
      tab: 1,
      // 取得するデータ
      optionSections: [],
      gainSet: {
        characters: [],
        owners: []
      },
      // 区分編集
      showContent_section: false,
      postSection: "",
      // 登場人物編集
      showContent_character: false,
      postCharacter: "",
      // 持ち主編集
      showContent_owner: false,
      postOwner: "",
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

    // 登場人物を取得
    async fetchCharacters () {
      const response = await axios.get('/api/informations/characters')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.gainSet.characters = response.data
    },

    // 持ち主を取得
    async fetchOwners () {
      const response = await axios.get('/api/informations/owners')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.gainSet.owners = response.data
    },

    // 区分編集のモーダル表示 
    openModal_sectionEdit (id) {
      this.showContent_section = true
      this.postSection = id;
    },
    // 区分編集のモーダル非表示
    async closeModal_sectionEdit() {
      await this.fetchSections()
      await this.fetchCharacters()
      this.showContent_section = false
    },

    // 登場人物編集のモーダル表示 
    openModal_characterEdit (id) {
      this.showContent_character = true
      this.postCharacter = id;
    },
    // 登場人物編集のモーダル非表示
    async closeModal_characterEdit() {
      await this.fetchSections() // なぜかこれをつけるとうまくいく
      await this.fetchCharacters()
      this.showContent_character = false
    },

    // 持ち主編集のモーダル表示 
    openModal_ownerEdit (id) {
      this.showContent_owner = true
      this.postOwner = id;
    },
    // 持ち主編集のモーダル非表示
    async closeModal_ownerEdit() {
      await this.fetchSections() // なぜかこれをつけるとうまくいく
      await this.fetchOwners()
      this.showContent_owner = false
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
        await this.fetchCharacters()
        await this.fetchOwners()
      },
      immediate: true
    }
  }
}
</script>