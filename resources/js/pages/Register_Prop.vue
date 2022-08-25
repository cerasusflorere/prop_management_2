<template>
<!-- 登録-使用シーン上ではid=overay と id=contentを有効にする-->
  <div v-bind:id="[val === 1 ? 'overlay' : '']">
    <div v-bind:id="[val === 1 ? 'content' : '']" class="panel">
        <form class="form"  @submit.prevent="register_prop">
          <!-- エラー表示 -->
          <div class="errors" v-if="errors.error">
            <ul v-if="errors.error.photo">
             <li v-for="msg in errors.error.photo" :key="msg">{{ msg }}</li>
            </ul>
          </div>
          <!-- 小道具名 -->
          <div>
            <label for="prop_input">小道具</label>
            <div class="form__button">
              <button type="button" @click="openModal_listProps()" class="button button--inverse">小道具リスト</button>
            </div>
          </div>
         
          <input type="text" class="form__item" id="prop_input" v-model="registerForm.prop" @input="handleNameInput" required>
          <lavel for="furigana">ふりがな</lavel>
          <input type="text" name="furigana" id="furigana" v-model="registerForm.kana" required>

          <!-- 所有者 -->
          <label for="owner">持ち主</label>
          <select id="owner" class="form__item"  v-model="registerForm.owner">
            <option disabled value="">持ち主一覧</option>
            <option v-for="owner in optionOwners" v-bind:value="owner.id">
              {{ owner.name }}
            </option>
          </select>
          <!-- コメント -->
          <label for="comment_prop">コメント</label>
          <textarea class="form__item" id="comment_prop" v-model="registerForm.comment"></textarea>

          <!-- 写真 -->
          <label for="photo_input">写真</label>
          <div v-if="errors.photo">{{ errors.photo }}</div>
          <input id="photo_photo" class="form__item" type="file" @change="onFileChange">
          <output class="form__output" v-if="preview">
            <img :src="preview" alt="" style="max-height: 12em">
          </output>

          <!--- 送信ボタン -->
          <div class="form__button">
            <button type="submit" class="button button--inverse">登録</button>
          </div>
        </form>
        <listProps :val="postFlag" v-show="showContent" @close="closeModal_listProps" />
        <!-- 登録- 使用シーンでは閉じるボタンを出現させる -->
        <button type="button" v-if="val===1" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
</template>

<script>
import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

// 小道具リスト
import listProps from '../components/List_Props.vue'
// 予測変換
import VueSuggestInput from 'vue-suggest-input'
import 'vue-suggest-input/dist/vue-suggest-input.css'
// ふりがな
import * as AutoKana from 'vanilla-autokana';

let autokana;

export default {
  // モーダルとして表示
  name: 'registerProp',
  props: {
    val: {
      required: false,
      type: Number      
    }
  },
  // 表示するコンポーネント
  components: {
    listProps
  },
  // データ
  data() {
    return {
      // 持ち主リスト
      optionOwners: [],
      // 小道具リスト
      showContent: false,
      postFlag: "",
      // 小道具候補
      props: [],
      // 写真プレビュー
      preview: null,
      // エラー
      errors: {
        photo: null,
        error: null,
      },
      // 登録内容
      registerForm: {
        prop: '',
        kana: '',
        owner: '',
        comment: '',
        // 写真
        photo: ''
      },
      // 登録状態
      loading: false
    }
  },
  mounted() {
    // ふりがなのinput要素のidは省略可能
    // 使用シーン登録時のid=propと被るから
    autokana = AutoKana.bind('#prop_input');
  },
  methods: {
      // 持ち主を取得
    async fetchOwners () {
      const response = await axios.get('/api/informations/owners')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.optionOwners = response.data
    },

    // 小道具一覧を取得
    async fetchProps () {
      const response = await axios.get('/api/props')

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.props = response.data
    },

    handleNameInput() {
      this.registerForm.kana = autokana.getFurigana();
    },

    // 小道具リストのモーダル表示 
    openModal_listProps () {
      this.showContent = true
      this.postFlag = 1;
    },
    // 小道具リストのモーダル非表示
    closeModal_listProps (){
      this.showContent = false
    },
    
    // フォームでファイルが選択されたら実行される
    onFileChange (event) {
      this.errors.photo = null;
      // 何も選択されていなかったら処理中断
      if (event.target.files.length === 0) {
        this.reset_photo()
        return false
      }

      // ファイルが画像ではなかったら処理中断
      if (! event.target.files[0].type.match('image.*')) {
        this.reset_photo()
        this.errors.photo = '写真データを選択してください'
        return false
      }

      // FileReaderクラスのインスタンスを取得
      const reader = new FileReader()

      // ファイルを読み込み終わったタイミングで実行する処理
      reader.onload = e => {
        // previewに読み込み結果（データURL）を代入する
        // previewに値が入ると<output>につけたv-ifがtrueと判定される
        // また<output>内部の<img>のsrc属性はpreviewの値を参照しているので
        // 結果として画像が表示される
        this.preview = e.target.result
      }

      // ファイルを読み込む
      // 読み込まれたファイルはデータURL形式で受け取れる（上記onload参照）
      reader.readAsDataURL(event.target.files[0])

      this.registerForm.photo = event.target.files[0]
    },
     
    // 画像をクリアするメソッド
    reset_photo () {
      this.preview = null
      this.registerForm.photo = ''
      this.$el.querySelector('input[type="file"]').value = null
    },

    // 入力欄の値とプレビュー表示をクリアするメソッド
    reset () {
      this.registerForm.prop = ''
      this.registerForm.kana = ''
      this.registerForm.owner = ''
      this.registerForm.comment = ''
      this.preview = null
      this.registerForm.photo = ''
      this.$el.querySelector('input[type="file"]').value = null,
      this.errors.photo = null
    },

    // 登録する
    async register_prop () {
      const formData = new FormData()
      formData.append('name', this.registerForm.prop)
      formData.append('kana', this.registerForm.kana)
      formData.append('owner_id', this.registerForm.owner)
      formData.append('memo', this.registerForm.comment)
      formData.append('usage', '')
      formData.append('photo', this.registerForm.photo)
      const response = await axios.post('/api/props', formData)

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }

      // // 諸々データ削除        
      this.reset()

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '小道具が投稿されました！',
        timeout: 6000
      })
      
      // this.$router.push('')
    }
  },
  watch: {
    $route: {
      async handler () {
        await this.fetchOwners()
        await this.fetchProps()
      },
      immediate: true
    }
  }
}
</script>

<style>
#overlay{
  overflow-y: scroll;
  z-index: 9999;
  position:fixed;
  top:0;
  left:0;
  width:100%;
  height:100%;
  background-color:rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

#content{
  z-index: 2;
  width: 50%;
  background-color: white;
}
</style>