<template>
  <div class="panel">
      <form class="form"  @submit.prevent="register">
        <!-- 登場人物 -->
        <label for="character_attr">登場人物</label>
        <select class="form__item" v-model="selectedAttr" v-on:change="selected">
          <option disabled value="">登場人物属性</option>
          <option v-for="(value, key) in optionCharacters">
            {{ key }}
          </option>
        </select>

        <select class="form__item" v-model="registerForm.character" required>
          <option disabled value="">登場人物一覧</option>
          <option v-if="selectedCharacters" v-for="characters in selectedCharacters">
            {{ characters.name }}
          </option>
        </select>

        <!-- 小道具名 -->
        <label for="prop">小道具</label>
        <select id="prop" class="form__item"  v-model="registerForm.prop" required>
          <option disabled value="">小道具一覧</option>
          <option v-for="prop in optionProps" 
            v-bind:value="prop.id" 
            v-bind:key="prop.id">
            {{ prop.name }}
          </option>
        </select>
        <div class="form__button">
          <button type="button" @click="openModal_register()" class="button button--inverse">新たな小道具追加</button>
        </div>
        <registerProp :val="postFlag" v-show="showContent" @close="closeModal_register" />

        <!-- ページ数 -->
        <label for="page">ページ数</label>
        <small>例) 22, 24-25</small>
        <input type="text"  id="page" class="form__item" v-model="registerForm.page">

        <!-- 使用するか -->
        <div class="form__check">
          <input type="checkbox" id="usage" class="form__check__input" v-model="registerForm.usage" checked></input>
          <label for="usage" class="form__check__label">中間発表での使用</label>
        </div>
        
        <!-- コメント -->
        <label for="comment">コメント</label>
        <textarea class="form__item" id="comment" v-model="registerForm.comment"></textarea>

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>
  </div>
</template>

<script>
import registerProp from './Register_Prop.vue'

export default {
  components: {
    registerProp
  },
  data() {
    return {
      // 取得するデータ
      characters: [],
      optionProps: [],
      // 連動プルダウン
      selectedAttr: '',
      selectedCharacters: '',
      optionCharacters: {
        移民たち: [
          { id: 1, name: 'アン' }, 
          { id: 2, name: 'メアリー' }, 
          { id: 3, name: 'アンジェラ' },
          { id: 4, name: 'エマ' }, 
          { id: 5, name: 'フィオナ' }, 
          { id: 6, name: 'モイラ' },
          { id: 7, name: 'イアン' }, 
          { id: 8, name: 'ハリー' }, 
          { id: 9, name: 'ジェニファー' },
          { id: 10, name: 'マルグレット' }, 
          { id: 11, name: 'ステファン' }, 
          { id: 12, name: 'ポーラ' },
          { id: 13, name: 'ジョー' }, 
          { id: 14, name: 'エドナ' }, 
        ],
        村に残った人々: [
          { id: 15, name: 'ブレンダ' },
          { id: 16, name: 'エドワード' }, 
          { id: 17, name: 'グレン' }, 
          { id: 18, name: 'ジョナサン' },
          { id: 19, name: 'サラ' }, 
          { id: 20, name: 'ケリー' }, 
          { id: 21, name: 'モーリーン' },
          { id: 22, name: 'リザ' }, 
          { id: 23, name: 'ジョセフ' }, 
        ],
        船の人々: [
          { id: 24, name: 'クリス' },
          { id: 25, name: 'スーザン' }, 
          { id: 26, name: 'フェルディナンド' },
          { id: 27, name: '未定' },
        ],
      },
      // 小道具登録
      showContent: false,
      postFlag: "",
      // 登録内容
      registerForm: {
        character: '',
        prop: '',
        page: null,
        usage: null,
        comment: null
      }
    }
  },
  methods: {
    // 登場人物を取得
    async fetchCharacters () {
      const response = await axios.get('/api/informations/characters')

      // if (response.statusText !== OK) {
      //   this.$store.commit('error/setCode', response.status)
      //   return false
      // }

      this.characters = response.data
    },

    // 小道具を取得
    async fetchProps () {
      const response = await axios.get('/api/props')

      // if (response.statusText !== OK) {
      //   this.$store.commit('error/setCode', response.status)
      //   return false
      // }

      this.optionProps = response.data
    },

    // 連動プルダウン
    selected: function(){
      this.selectedCharacters = this.optionCharacters[this.selectedAttr];
    },
    // 小道具登録のモーダル表示 
    openModal_register () {
      this.showContent = true
      this.postFlag = 1;
    },
    // 小道具登録のモーダル非表示
    closeModal_register (){
      this.showContent = false
    },
    // 登録する
    async register () {
      const response = await axios.post('/api/scenes', registerForm)
    }
  },
  watch: {
    $route: {
      async handler () {
        await this.fetchCharacters()
        await this.fetchProps()
      },
      immediate: true
    }
  }
}
</script>