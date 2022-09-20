<template>
<!-- 登録-使用シーン上ではclass=overay と class=contentを有効にする-->
  <div v-bind:class="[val === 1 ? 'overlay' : '', (overlay_class === 0 && val === 1) ? 'overlay-custom' : '']" @click.self="val === 1 ? $emit('close') : null">
    <div v-bind:class="[val === 1 ? 'content content-confirm-dialog' : '']" class="panel"  ref="content_register_prop">
      <div class="checkbox-area--together">
        <input type="radio" id="prop_passo" v-model="season_prop" value="passo">
        <label for="prop_passo">中間公演</label>       

        <input type="radio" id="prop_guraduation" v-model="season_prop" value="guradution">
        <label for="prop_guraduation">卒業公演</label>
      </div>

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
            <button type="button" @click="openModal_listProps(1)" class="button button--inverse"><i class="fas fa-list-ul fa-fw"></i>小道具リスト</button>
          </div>
        </div>
         
        <input type="text" class="form__item" id="prop_input" v-model="registerForm.prop" @input="handleNameInput" required>
        <label for="furigana">ふりがな</label>
        <input type="text" name="furigana" id="furigana" v-model="registerForm.kana" class="form__item form__item--furigana" required>

        <!-- 所有者 -->
        <label for="owner">持ち主</label>
        <select id="owner" class="form__item"  v-model="registerForm.owner">
          <option disabled value="">持ち主一覧</option>
          <option v-for="owner in optionOwners" v-bind:value="owner.id">
            {{ owner.name }}
          </option>
        </select>

        <!-- 使用するか -->
        <div>
          <div v-show="season_tag_prop === 1" class="checkbox-area--together">
            <label for="prop_usage_scene">中間発表での使用</label>
            <input type="checkbox" id="prop_usage_scene" v-model="registerForm.usage_prop"></input>    
          </div>
          <div v-show="season_tag_prop === 2">
            <div class="checkbox-area--together">
              <label for="prop_usage_scene_guradutaion">卒業公演での使用</label>
              <input type="checkbox" id="prop_usage_scene_guradutaion" v-model="registerForm.usage_guraduation_prop" @change="selectGuraduation_Prop">
            </div>
            <div v-if="guradutaion_tag_prop" class="checkbox-area--together">
              <input type="radio" id="prop_usage_scene_left" value="usage_left" v-model="registerForm.usage_stage_prop">            
              <label for="prop_usage_scene_left">上手</label>

              <input type="radio" id="prop_usage_scene_right" value="usage_right" v-model="registerForm.usage_stage_prop">
              <label for="prop_usage_scene_right">下手</label>
            </div>
          </div>
        </div>
     
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
          <button type="submit" class="button button--inverse"><i class="fas fa-paper-plane fa-fw"></i>登録</button>
        </div>
      </form>
      <listProps :postFlag="postFlag" v-show="showContent" @close="closeModal_listProps" />
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
      // 中間公演or卒業公演
      season_prop: null,
      season_tag_prop: null,
      // 卒業公演
      guradutaion_tag_prop: 0,
      // 写真プレビュー
      preview: null,
      // overlayのクラス
      overlay_class: 1, // 1のときはつかない
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
        usage_prop: '',
        usage_guraduation_prop: 0,
        usage_stage_prop: null,
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
      const response = await axios.get('/api/informations/owners');

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.optionOwners = response.data;
    },

    // 小道具一覧を取得
    async fetchProps () {
      const response = await axios.get('/api/props');

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.props = response.data;
    },

    handleNameInput() {
      this.registerForm.kana = autokana.getFurigana();
    },

    // どちらの公演か取得
    async choicePerformance() {
      let today = new Date();
      const month = today.getMonth()+1;
      const day = today.getDate();
      if(3 < month && month < 11){
        this.season_prop = "passo";
      }else if(month === 11){
        const year = today.getFullYear();
        const passo_day = await this.getDateFromWeek(year, month, 1, 0); // 11月第1日曜日
        if(passo_day <= day){
          this.season_prop = "passo";
        }else{
          this.season_prop = "guradutaion";
        }
      }else if(month > 11 && month < 3){
        this.season_prop = "guradutaion";
      }else if(month === 3){
        const year = today.getFullYear();
        const guraduation_day = await this.getDateFromWeek(year, month, 1, 0); // 11月第1日曜日
        if(guraduation_day <= day){          
          this.season_prop = "guradutaion";
        }else{
          this.season_prop = "passo";
        }
      }
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
    },

    // 卒業公演の使用にチェックが付いたか
    selectGuraduation_Prop() {
      if(!this.guradutaion_tag_prop){
        this.guradutaion_tag_prop = 1;
      }else{
        this.guradutaion_tag_prop = 0;
        this.registerForm.usage_stage_prop = null;
      }
    },

    // 小道具リストのモーダル表示 
    openModal_listProps (number) {
      this.showContent = true;
      this.postFlag = 1;;
    },
    // 小道具リストのモーダル非表示
    closeModal_listProps (){
      this.showContent = false;
      this.postFlag = "";
    },
    
    // フォームでファイルが選択されたら実行される
    onFileChange (event) {
      this.errors.photo = null;
      // 何も選択されていなかったら処理中断
      if (event.target.files.length === 0) {
        this.reset_photo();
        return false;
      }

      // ファイルが画像ではなかったら処理中断
      if (! event.target.files[0].type.match('image.*')) {
        this.reset_photo();
        this.errors.photo = '写真データを選択してください';
        return false;
      }

      // FileReaderクラスのインスタンスを取得
      const reader = new FileReader();

      // ファイルを読み込み終わったタイミングで実行する処理
      reader.onload = e => {
        // previewに読み込み結果（データURL）を代入する
        // previewに値が入ると<output>につけたv-ifがtrueと判定される
        // また<output>内部の<img>のsrc属性はpreviewの値を参照しているので
        // 結果として画像が表示される
        this.preview = e.target.result;
      }

      // ファイルを読み込む
      // 読み込まれたファイルはデータURL形式で受け取れる（上記onload参照）
      reader.readAsDataURL(event.target.files[0]);
  
      this.registerForm.photo = event.target.files[0];      

      if(this.val){
        // 調整
        this.$nextTick(() => {
          const content_dom = this.$refs.content_register_prop;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }
        });
      }     
    },
     
    // 画像をクリアするメソッド
    reset_photo () {
      this.preview = null;
      this.registerForm.photo = '';
      this.$el.querySelector('input[type="file"]').value = null;

      if(this.val){
        // 調整
        this.$nextTick(() => {
          const content_dom = this.$refs.content_register_prop;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }
        });
      }
    },

    // 入力欄の値とプレビュー表示をクリアするメソッド
    reset () {
      this.registerForm.prop = '';
      this.registerForm.kana = '';
      this.registerForm.owner = '';
      this.registerForm.usage_prop = '';
      this.registerForm.usage_guraduation_prop = '';
      this.registerForm.usage_stage_prop = null;
      this.registerForm.comment = '';
      this.preview = null;
      this.registerForm.photo = '';
      this.$el.querySelector('input[type="file"]').value = null;
      this.errors.photo = null;

      if(this.val){
        // 調整
        this.$nextTick(() => {
          const content_dom = this.$refs.content_register_prop;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }
        });
      }
    },

    // 登録する
    async register_prop () {
      const formData = new FormData();
      formData.append('name', this.registerForm.prop);
      formData.append('kana', this.registerForm.kana);
      formData.append('owner_id', this.registerForm.owner);
      formData.append('memo', this.registerForm.comment);
      formData.append('usage', this.registerForm.usage_prop);
      formData.append('usage_guraduation', this.registerForm.usage_guraduation_prop);
      if(this.registerForm.usage_stage_prop === "usage_left"){
        formData.append('usage_left', 1);
        formData.append('usage_right', '');
      }else if(this.registerForm.usage_stage_prop === "usage_right"){
        formData.append('usage_right', 1);
        formData.append('usage_left', '');
      }else{
        formData.append('usage_left', '');
        formData.append('usage_right', '');
      }
      formData.append('photo', this.registerForm.photo);
      const response = await axios.post('/api/props', formData);
  
      if (response.status === 422) {
        this.errors.error = response.data.errors;
        return false;
      }

      if (response.status !== 201) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      // 諸々データ削除
      this.reset();

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '小道具が投稿されました！',
        timeout: 6000
      });
    }
  },
  watch: {
    $route: {
      async handler () {
        await this.fetchOwners();
        await this.fetchProps();
        await this.choicePerformance();
      },
      immediate: true
    },
    season_prop: {
      async handler(season_prop) {
        if(this.season_prop === "passo"){
          this.season_tag_prop = 1;
        }else if(this.season_prop === "guradution"){
          this.season_tag_prop = 2;
        }
      },
      immediate: true
    },
    val: {
      async handler(val) {
        if(this.val){
          this.$nextTick(() => {
            const content_dom = this.$refs.content_register_prop;
            const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
  
            if(content_rect.top < 0){
              this.overlay_class = 0;
            }else{
              this.overlay_class = 1;
            }
          });
        }        
      },
      immediate: true
    }
  }
}
</script>