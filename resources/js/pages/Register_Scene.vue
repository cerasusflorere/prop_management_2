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
          <option v-if="selectedCharacters" v-for="characters in selectedCharacters"
           v-bind:value="characters.id">
            {{ characters.name }}
          </option>
        </select>

        <!-- 小道具名 -->
        <label for="prop">小道具</label>
        <select id="prop" class="form__item"  v-model="registerForm.prop" required>
          <option disabled value="">小道具一覧</option>
          <option v-for="prop in optionProps" 
            v-bind:value="prop.id">
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
        <input type="text"  id="page" class="form__item" v-model="registerForm.pages">

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
      optionCharacters: null,
      // 小道具登録
      showContent: false,
      postFlag: "",
      // 登録内容
      registerForm: {
        character: '',
        prop: '',
        pages: null,
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

      // 区分と登場人物をオブジェクトに変換する
      let sections = new Object();
      this.characters.forEach(function(section){
        sections[section.section] = section.characters
      })
      this.optionCharacters = sections
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

    // first_pageとfinal_pageに分割する
    first_finalDivide(str) {
      return str.split(/-|ー|‐|―|⁻|－|～|—|₋|ｰ|~/)
    },

    // 全角→半角
    hankaku2Zenkaku(str) {
      return str.replace(/[０-９]/g, function(s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
      });
    },

    // 登録する
    register () {
      // ページを分割
      let pages_before = this.registerForm.pages.split(/,|、|，|\s+/);
      pages_before.forEach(page => {
        page = page.replaceAll(/\s+/g, '');
      });
      let pages_after = pages_before.filter(Boolean);

      let pattern = /-|ー|‐|―|⁻|－|～|—|₋|ｰ|~/;
      let first_pages = [];
      let final_pages = [];

      pages_after.forEach(page => {
        if ( pattern.test(page) ) {
          let pages = this.first_finalDivide(page);
          first_pages.push(parseInt(this.hankaku2Zenkaku(pages[0])));
          final_pages.push(parseInt(this.hankaku2Zenkaku(pages[1])));
        }else{
          first_pages.push(parseInt(this.hankaku2Zenkaku(page)))
          final_pages.push(null)
        }
      });
      
      const character = this.registerForm.character;
      const prop = this.registerForm.prop;
      const usage = this.registerForm.usage;
      const comment = this.registerForm.comment;
      first_pages.forEach(async function(page, index) {
        const response = await axios.post('/api/scenes', {
          character_id: character,
          prop_id: prop,
          first_page: page,
          final_page: final_pages[index],
          usage: usage,
          memo: comment
        })
      });

      if(usage){
        this.usageJudgement(usage);
      }

      this.registerForm.character = '';
      this.registerForm.prop = '';
      this.registerForm.pages = null;
      this.registerForm.usage = null;
      this.registerForm.comment = null;
    },
    async usageJudgement (usage){
      const response = await axios.post('/api/props/'+ this.registerForm.prop, {
        method: 'usage_change',
        usage: usage
      })
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