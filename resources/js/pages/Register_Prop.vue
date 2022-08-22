<template>
<!-- 登録-使用シーン上ではid=overay と id=contentを有効にする-->
  <div v-bind:id="[val === 1 ? 'overlay' : '']">
    <div v-bind:id="[val === 1 ? 'content' : '']" class="panel">
        <form class="form"  @submit.prevent="register_prop">
          <!-- 小道具名 -->
          <div>
            <label for="prop">小道具</label>
            <div class="form__button">
              <button type="button" @click="openModal_listProps()" class="button button--inverse">小道具リスト</button>
            </div>
            
          </div>
         
          <!-- <input type="text" class="form__item" id="prop" v-model="registerForm.prop" required> -->
          <div v-if="errors.prop !== null">{{ errors.prop }}</div>
          <vue-suggest-input class="form__item" id="prop" v-model="registerForm.prop" :items="props"/>

          <!-- 所有者 -->
          <label for="owner">持ち主</label>
          <select id="owner" class="form__item"  v-model="registerForm.owner">
            <option disabled value="">持ち主一覧</option>
            <option v-for="owner in optionOwners" 
              v-bind:value="owner.id">
              {{ owner.name }}
            </option>
          </select>
          <!-- コメント -->
          <label for="comment">コメント</label>
          <textarea class="form__item" id="comment" v-model="registerForm.comment"></textarea>

          <!-- 写真 -->
          <label for="photo">写真</label>
          <input class="form__item" type="file" @change="onFileChange">
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
// 小道具リスト
import listProps from '../components/List_Props.vue'
// 予測変換
import VueSuggestInput from 'vue-suggest-input'
import 'vue-suggest-input/dist/vue-suggest-input.css'

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
    listProps,
    VueSuggestInput
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
      photo: null,
      // エラー達
      errors: {
        prop: ''
      },
      // 登録内容
      registerForm: {
        prop: null,
        owner: null,
        comment: null,
        // 写真
        photo: null
      },
      // 登録状態
      loading: false
    }
  },
  methods: {
      // 持ち主を取得
    async fetchOwners () {
      const response = await axios.get('/api/informations/owners')

      // if (response.statusText !== OK) {
      //   this.$store.commit('error/setCode', response.status)
      //   return false
      // }

      this.optionOwners = response.data
    },

    // 小道具一覧を取得
    async fetchProps () {
      const response = await axios.get('/api/props')

      // if (response.statusText !== OK) {
      //   this.$store.commit('error/setCode', response.status)
      //   return false
      // }

      this.props = response.data
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
      // 何も選択されていなかったら処理中断
      if (event.target.files.length === 0) {
        this.reset()
        return false
      }

      // ファイルが画像ではなかったら処理中断
      if (! event.target.files[0].type.match('image.*')) {
        this.reset()
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

    // 入力欄の値とプレビュー表示をクリアするメソッド
    reset () {
      this.preview = null
      this.registerForm.photo = null
      this.$el.querySelector('input[type="file"]').value = null
    },

    // 登録する
    async register_prop () {
      if(this.registerForm.prop === null){
        this.errors.prop = '小道具名を入力してください'
      }else{
        const formData = new FormData()
        formData.append('name', this.registerForm.prop)
        formData.append('owner_id', this.registerForm.owner)
        formData.append('memo', this.registerForm.comment)
        formData.append('usage', null)
        formData.append('photo', this.registerForm.photo)
        const response = await axios.post('/api/props', formData)
        // const response = await axios.post('/api/props', {
        //   name: this.registerForm.prop,
        //   owner_id: this.registerForm.owner,
        //   memo: this.registerForm.comment,
        //   usage: null,
        //   photo: formData,
        // })

        // if (response.status === UNPROCESSABLE_ENTITY) {
        //   this.errors = response.data.errors
        //   return false
        // }

        // // 諸々データ削除
        // this.registerForm = ''
        // this.errors.prop = null
        this.reset()

        this.$emit('input', false)
        // if (response.status !== CREATED) {
        //   this.$store.commit('error/setCode', response.status)
        //   return false
        // }
        // // メッセージ登録
        // this.$store.commit('message/setContent', {
        //   content: '小道具が投稿されました！',
        //   timeout: 6000
        // })
        // this.$router.push('')
        
      }      
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