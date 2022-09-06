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
        <label for="prop_select">小道具</label>
        <select id="prop_select" class="form__item"  v-model="registerForm.prop" required>
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
          <label for="usage_scene" class="form__check__label">中間発表での使用</label>
          <input type="checkbox" id="usage_scene" class="form__check__input" v-model="registerForm.usage" checked></input>          
        </div>
        
        <!-- コメント -->
        <label for="comment_scene">コメント</label>
        <textarea class="form__item" id="comment_scene" v-model="registerForm.comment"></textarea>

        <!--- 送信ボタン -->
        <div class="form__button">
          <button type="submit" class="button button--inverse">登録</button>
        </div>
      </form>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

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
        pages: '',
        usage: '',
        comment: ''
      }
    }
  },
  methods: {
    // 登場人物を取得
    async fetchCharacters () {
      const response = await axios.get('/api/informations/characters')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

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

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

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

    reset() {
      this.selectedAttr = '';
      this.registerForm.character = '';
      this.registerForm.prop = '';
      this.registerForm.pages = '';
      this.registerForm.usage = '';
      this.registerForm.comment = '';
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
      let first_pages = [];
      let final_pages = [];
      first_pages[0] = 0;
      final_pages[0] = 0;
      if(this.registerForm.pages){
      let pages_before = this.registerForm.pages.split(/,|、|，|\s+/);
        pages_before.forEach(page => {
          page = page.replaceAll(/\s+/g, '');
        });
        let pages_after = pages_before.filter(Boolean);
  
        let pattern = /-|ー|‐|―|⁻|－|～|—|₋|ｰ|~/;

        pages_after.forEach((page, index) => {
          if(index === 0){
            if ( pattern.test(page) ) {
              let pages = this.first_finalDivide(page);
              first_pages[index] = (parseInt(this.hankaku2Zenkaku(pages[0])));
              final_pages[index] = (parseInt(this.hankaku2Zenkaku(pages[1])));
            }else{
              first_pages[index] = (parseInt(this.hankaku2Zenkaku(page)));
              final_pages[index] = (0);
            }
          }else{
            if ( pattern.test(page) ) {
              let pages = this.first_finalDivide(page);
              first_pages.push(parseInt(this.hankaku2Zenkaku(pages[0])));
              final_pages.push(parseInt(this.hankaku2Zenkaku(pages[1])));
            }else{
              first_pages.push(parseInt(this.hankaku2Zenkaku(page)));
              final_pages.push(0);
            }
          }          
        });
      }  
      
      const character = this.registerForm.character;
      const prop = this.registerForm.prop;
      const usage = this.registerForm.usage;
      const comment = this.registerForm.comment;
      let last_flag = false;
      first_pages.forEach(async function(page, index) {
        const response = await axios.post('/api/scenes', {
          character_id: character,
          prop_id: prop,
          first_page: page,
          final_page: final_pages[index],
          usage: usage,
          memo: comment
        })
        
        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors
          return false
        }

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status)
          return false
        }
        if(index === first_pages.length-1){
          if(response.statusText === 'Created' && usage){
            // 小道具の使用有無変更
            const response_prop = axios.post('/api/props/'+ prop, {
              method: 'usage_change',
              usage: usage
            })

            if (response_prop.statusText === 'Unprocessable Entity') {
              this.errors.error = response.data.errors
              return false
            }

            if (response_prop.statusText !== 'No Content') {
              this.$store.commit('error/setCode', response.status)
              return false
            }
          }
        }        
      });

      // 諸々データ削除        
      this.reset()
      
      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '使用シーンが投稿されました！',
        timeout: 6000
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