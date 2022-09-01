<template>
  <div id="overlay">
    <div id="content" class="panel">
      <!--- 閲覧/編集 -->
      <div class="form__button">
        <button type="button" v-show="tab === 1" class="button button--inverse" @click="alterTab"><i class="fas fa-edit fa-fw"></i>編集</button>
        <button type="button" v-show="tab === 2" class="button button--inverse" @click="alterTab"><i class="fas fa-eye fa-fw"></i>閲覧</button>
      </div>

      <div v-show="tab === 1">
        <div class="detail-box">
          <!-- 写真 -->
          <div>
            <img :src="prop.url" :alt="prop.name">
          </div>
          <!-- 詳細 -->
          <div>
            <!--- 削除ボタン -->
            <div class="form__button">
              <button type="button" class="button button--inverse" @click="openModal_confirmDelete"><i class="fas fa-eraser fa-fw"></i>削除</button>
            </div>
            <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
            
            <div>
              <h1 style="display: inline">{{ prop.name }}</h1>
              <div v-if="prop.usage"><i class="fas fa-tag"></i></div>
            </div>
            <div>所有者: <span v-if="prop.owner">{{ prop.owner.name }}</span></div>

            <div>
              <label>メモ:</label>
              <ul v-if="prop.prop_comments.length" >
                <li v-for="comment in prop.prop_comments">
                  <div>{{ comment.memo }}</div>
                </li>
              </ul>
            </div>

            <div>
              <label>シーン:</label>
                <ol v-if="prop.scenes.length">
                  <li v-for="scene in prop.scenes">
                    <!-- 名前 -->
                    <span>{{ scene.character.name }}</span>
                    <!-- 何ページ -->
                    <span v-if="scene !== null && scene.first_page !== null"> : p. {{ scene.first_page }} 
                      <span v-if="scene !== null && scene.final_page !== null"> ~ p. {{ scene.final_page}}</span>
                    </span>
                    <!-- メモ -->
                    <div>
                      <ul v-if="scene.scene_comments.length">
                        <li v-for="comment in scene.scene_comments">
                          <div>{{ comment.memo }}</div>
                        </li>
                      </ul>
                    </div>
                  </li>                  
                </ol>
            </div> 
          </div>
        </div>
      </div>
    
      <!-- 編集 -->
      <div v-show="tab === 2">
        <form class="detail-box"  @submit.prevent="confirmProp">
          <!-- 写真 -->
          <div v-show="editForm_prop.url && editForm_prop.photo === 1">
            <img :src="prop.url" :alt="prop.name">
            <button type="button" class="button button--inverse" @click="deletePhoto">×</button>
          </div>
          <div v-show="!editForm_prop.photo">
            <input id="photo_edit" class="form__item" type="file" @change="onFileChange">
          </div>
          <div v-show="editForm_prop.photo !== 1 && editForm_prop.photo">
            <output class="form__output" v-if="preview">
              <img :src="preview" alt="" style="max-height: 12em">
            </output>
            <button type="button" class="button button--inverse" @click="resetPhoto">×</button>
          </div>
          

          <!-- 詳細 -->
          <div>
            <div>
              <label for="prop_name_edit">小道具名</label>
              <input type="text" id="prop_name_edit" class="form__item" v-model="editForm_prop.name" @input="handleNameInput" required>
              <input type="text" name="furigana" id="prop_furigana_edit" v-model="editForm_prop.kana" required>
            </div>            
            <input type="checkbox" v-model="editForm_prop.usage">
            <div>所有者: 
              <select id="prop_owner_edit" class="form__item"  v-model="editForm_prop.owner_id">
                <option disabled value="">持ち主一覧</option>
                <option v-for="owner in optionOwners" v-bind:value="owner.id">
                  {{ owner.name }}
                </option>
              </select>
            </div>

            <div>
              <label for="prop_comment_edit">メモ:</label>
              <ul v-if="editForm_prop.prop_comments.length" >
                <li v-for="comment in editForm_prop.prop_comments">
                  <textarea>{{ comment.memo }}</textarea>
                </li>
              </ul>
              <div v-else>
                <textarea id="prop_comment_edit" class="form__item" v-model="editForm_prop.prop_comments[0].memo"></textarea>
              </div>
            </div>

            <div>
              <label>シーン:</label>
              <ol v-if="editForm_prop.scenes.length">
                <li v-for="scene in editForm_prop.scenes">
                  <!-- 名前 -->
                  <span>{{ scene.character.name }}</span>
                  <!-- 何ページ -->
                  <span v-if="scene !== null && scene.first_page !== null"> : p. {{ scene.first_page }} 
                    <span v-if="scene !== null && scene.final_page !== null"> ~ p. {{ scene.final_page}}</span>
                  </span>
                  <!-- メモ -->
                  <div>
                    <ul v-if="scene.scene_comments.length">
                      <li v-for="comment in scene.scene_comments">
                        <div>{{ comment.memo }}</div>
                      </li>
                    </ul>
                  </div>
                </li>                  
              </ol>
            </div> 
          </div>
          <!--- 送信ボタン -->
          <div class="form__button">
            <button type="submit" class="button button--inverse">編集</button>
          </div>
        </form>
        <confirmDialog_Edit :confirm_dialog_edit_message="postMessage_Edit" v-show="showContent_confirmEdit" @Cancel_Edit="closeModal_confirmEdit_Cancel" @OK_Edit="closeModal_confirmEdit_OK"/>
      </div>
      
      <button type="button" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
</template>

<script>
import { OK, UNPROCESSABLE_ENTITY } from '../util'

import confirmDialog_Edit from './Confirm_Dialog_Edit.vue'
import confirmDialog_Delete from './Confirm_Dialog_Delete.vue'
// ふりがな
import * as AutoKana from 'vanilla-autokana';

let autokana;

export default {
  // モーダルとして表示
  name: 'detailProp',
  components: {
    confirmDialog_Edit,
    confirmDialog_Delete
  },
  props: {
    postProp: {
      type: Number,
      required: true
    }
  },
  // データ
  data() {
    return {
      // 表示する小道具のデータ
      prop: [],
      // 編集データ
      editForm_prop: [],
      // 取得するデータ
      optionOwners: [],
      props: [],
      // タブ
      tab: 1,
      // 写真プレビュー
      preview: null,
      // エラー
      errors: {
        photo: null,
        error: null,
      },
      // 編集confirm
      showContent_confirmEdit: false,
      postMessage_Edit: "",
      // 編集範囲
      editPropMode: "",
      // 削除confirm
      showContent_confirmDelete: false,
      postMessage_Delete: ""
    }
  },
  watch: {
    postProp: {
      async handler(postProp) {
        await this.fetchProp()
        await this.fetchOwners()
        await this.fetchProps()
      },
      immediate: true,
    }
  },
  mounted() {
    // ふりがなのinput要素のidは省略可能
    // 使用シーン登録時のid=propと被るから
    autokana = AutoKana.bind('#prop_furigana_edit');
  },
  methods: {
    // 小道具の詳細を取得
    async fetchProp () {
      this.tab = 1
      const response = await axios.get('/api/props/'+ this.postProp)

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.prop = response.data
      this.editForm_prop = JSON.parse(JSON.stringify(this.prop)) // そのままコピーするとコピー元も変更される
      if(this.editForm_prop.public_id){
        this.editForm_prop.photo = 1 // 写真が登録されている（可能性：1のまま、0に変更（この時public_idは存在する）、写真バイナリ代入（この時public_idは存在する））
      }else{
        this.editForm_prop.photo = 0 // 写真が登録されていない（可能性：0のまま（この時pubic_idは存在しない）、写真バイナリ代入（この時public_idは存在しない））
      }
    },

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
      this.editForm_prop.kana = autokana.getFurigana();
    },
    
    // タブ切り替え
    alterTab() {
      if(this.tab === 1){
        this.tab = 2
      }else{
        this.tab = 1
      }
    },

    // 写真を見せない
    deletePhoto(){
      this.editForm_prop.photo = 0
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

      this.editForm_prop.photo = event.target.files[0]
    },

    // 画像をクリアするメソッド
    resetPhoto () {
      this.preview = null
      this.editForm_prop.photo = 0
      this.$el.querySelector('input[type="file"]').value = null
    },

    // 編集エラー
    confirmProp () {
      if(this.prop.id === this.editForm_prop.id && (this.prop.name !== this.editForm_prop.name || this.prop.kana !== this.editForm_prop.kana || this.prop.owner_id !== this.editForm_prop.owner_id || this.prop.usage !== this.editForm_prop.usage) && ((this.prop.public_id && this.editForm_prop.photo === 1) || (!this.prop.public_id && !this.editForm_prop.photo))){
        // 写真をアップデートしない
        this.editPropMode_detail = 1 // 'photo_non_update'
        this.openModal_confirmEdit()
      }else if(this.prop.id === this.editForm_prop.id && !this.prop.public_id && this.editForm_prop.photo){
        // 写真新規
        this.editPropMode_detail = 2 // 'photo_store'
        this.openModal_confirmEdit()
      }else if(this.prop.id === this.editForm_prop.id && this.prop.public_id && !this.editForm_prop.photo){
        // 写真削除
        this.editPropMode_detail = 3 //'photo_delete'
        this.openModal_confirmEdit()
      }else if(this.prop.id === this.editForm_prop.id && this.prop.public_id && this.editForm_prop.photo){
        // 写真アップデート
        this.editPropMode_detail = 4 //'photo_update'
        this.openModal_confirmEdit()
      }

      if(this.prop.id === this.editForm_prop.id && !this.prop.prop_comments[0].id){
        // メモ新規投稿
        this.editPropMode_memo = 1 // 'memo_store'
        await this.editProp_memo()
      }else if(this.prop.id === this.editForm_prop.id && this.prop.prop_comments[0].id && this.prop.prop_comments[0].memo !== this.editForm_prop.prop_comments[0].memo){
        // メモアップデート
        this.editPropMode_memo = 2 // 'memo_update'
        await this.editProp_memo()
      }
    },

    // 編集confirmのモーダル表示 
    openModal_confirmEdit () {
      this.showContent_confirmEdit = true
      this.postMessage_Edit = '以下のように編集します。';
    },
    // 編集confirmのモーダル非表示_OKの場合
    async closeModal_confirmEdit_OK() {
      this.showContent_confirmEdit = false
      this.$emit('close')
      await this.editProp()
    },
    // 編集confirmのモーダル非表示_Cancelの場合
    closeModal_confirmEdit_Cancel() {
      this.showContent_confirmEdit= false
    },

    // 編集する
    async editProp () {
      if(this.editPropMode_detail === 1){
        // 写真は変更しない
        const response = await axios.post('/api/props/'+ this.prop.id, {
          method: 'photo_non_update',
          name: this.editForm_prop.name,
          kana: this.editForm_prop.kana,
          owner_id: this.editForm_prop.owner_id,
          usage: this.editForm_prop.usage
        })

        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors
          return false
        }

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status)
          return false
        }

        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '小道具が変更されました！',
          timeout: 6000
        })

        this.editPropMode_detail = 0
      }else if(this.editPropMode_detail === 2){
        // 写真新規投稿
        const formData = new FormData()
        formData.append('method', 'photo_store')
        formData.append('name', this.editForm_prop.name)
        formData.append('kana', this.editForm_prop.kana)
        formData.append('owner_id', this.editForm_prop.owner_id)
        formData.append('usage', this.editForm_prop.usage)
        formData.append('photo', this.editForm_prop.photo)
        const response = await axios.post('/api/props/'+ this.prop.id, formData)

        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors
          return false
        }

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status)
          return false
        }

        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '小道具が変更されました！',
          timeout: 6000
        })

        this.editPropMode_detail = 0
      }else if(this.editPropMode_detail === 3){
        // 写真削除
        const response = await axios.post('/api/props/'+ this.prop.id, {
          method: 'photo_delete',
          name: this.editForm_prop.name,
          kana: this.editForm_prop.kana,
          owner_id: this.editForm_prop.owner_id,
          public_id: this.editForm_prop.public_id,
          usage: this.editForm_prop.usage
        })

        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors
          return false
        }

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status)
          return false
        }

        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '小道具が変更されました！',
          timeout: 6000
        })

        this.editPropMode_detail = 0
      }if(this.editPropMode_detail === 4){
        // 写真アップデート
        const formData = new FormData()
        formData.append('method', 'photo_update')
        formData.append('name', this.editForm_prop.name)
        formData.append('kana', this.editForm_prop.kana)
        formData.append('owner_id', this.editForm_prop.owner_id)
        formData.append('public_id', this.editForm_prop.public_id)
        formData.append('usage', this.editForm_prop.usage)
        formData.append('photo', this.editForm_prop.photo)
        const response = await axios.post('/api/props/'+ this.prop.id, formData)

        if (response.statusText === 'Unprocessable Entity') {
          this.errors.error = response.data.errors
          return false
        }

        if (response.statusText !== 'Created') {
          this.$store.commit('error/setCode', response.status)
          return false
        }

        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '小道具が変更されました！',
          timeout: 6000
        })

        this.editPropMode_detail = 0
      }
      
      this.fetchProps()
    },

    // 削除confirmのモーダル表示 
    openModal_confirmDelete (id) {
      this.showContent_confirmDelete = true
      this.postMessage_Delete = 'これを行うと、紐づけられてたこの小道具を使用するシーンも全て削除されます。本当に削除しますか？';
    },
    // 削除confirmのモーダル非表示_OKの場合
    async closeModal_confirmDelete_OK() {
      this.showContent_confirmDelete = false
      this.$emit('close')
      await this.deletProp()
    },
    // 削除confirmのモーダル非表示_Cancelの場合
    closeModal_confirmDelete_Cancel() {
      this.showContent_confirmDelete = false
    },

    // 削除する
    async deletProp() {
      const response = await axios.delete('/api/props/'+ this.prop.id)

      if (response.statusText === 'Unprocessable Entity') {
        this.errors.error = response.data.errors
        return false
      }

      this.prop.id = null
      this.prop.kana = null
      this.prop.name = null
      this.prop.owner_id = null
      this.prop.owner = null
      this.prop.public_id = null
      this.prop.url = null
      this.prop.usage = null
      this.prop.prop_comments = null      
      this.prop.scenes = null
      

      if (response.statusText !== 'Created') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '小道具が1つ削除されました！',
        timeout: 6000
      })

      this.$emit('close')
    }
  }
}
</script>

<style scoped>
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

#content {
  z-index: 2;
  background-color: white;
  width: 80%;
  aspect-ratio: 2 / 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.detail-box {
  display: flex;
  height: 80%;
}
.detail-box>div {
  width:50%;
}
</style>