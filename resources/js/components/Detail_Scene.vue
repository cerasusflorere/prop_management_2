<template>
  <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']" @click.self="$emit('close')">
    <div class="content content-detail panel"  ref="content_detail_scene">
      <!--- 閲覧/編集 -->
      <div class="form__button">
        <button type="button" v-show="tab_scene === 1" class="button button--inverse" @click="alterTab"><i class="fas fa-edit fa-fw"></i>編集</button>
        <button type="button" v-show="tab_scene === 2" class="button button--inverse" @click="alterTab"><i class="fas fa-eye fa-fw"></i>閲覧</button>
      </div>
       
      <div v-show="tab_scene === 1">
        <div class="detail-box">
          <!-- 写真 -->
          <div>
            <img :src="scene.prop.url" :alt="scene.prop.name">
          </div>
          <!-- 詳細 -->
          <div>
            <!--- 削除ボタン -->
            <div class="form__button">
              <button type="button" class="button button--inverse" @click="openModal_confirmDelete"><i class="fas fa-eraser fa-fw"></i>削除</button>
            </div>
            <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
            
            <!-- 登場人物と使用状況 -->
            <div>
              <h1 style="display: inline">{{ scene.character.name }}</h1>
              <div v-if="scene.usage">Ⓟ</div>
              <div v-if="scene.usage_guraduation">Ⓖ</div>
              <div v-if="scene.usage_left">㊤</div>
              <div v-if="scene.usage_right">㊦</div>
            </div>

            <!-- 何ページ -->
            <span v-if="scene !== null && scene.first_page !== null">p. {{ scene.first_page }} 
              <span v-if="scene !== null && scene.final_page !== null"> ~ p. {{ scene.final_page}}</span>
            </span>

            <div>小道具：{{ scene.prop.name }}</div>
            <div>所有者: <span v-if="scene.prop.owner">{{ scene.prop.owner.name }}</span></div>

            <!-- メモ -->
            <div>
              <label>メモ:</label>
              <ul v-if="scene.prop.prop_comments.length" >
                <li v-for="comment in scene.prop.prop_comments">
                  <div>{{ comment.memo }}</div>
                </li>
              </ul>
            </div>           

            <div>
              <label>メモ:</label>
              <ul v-if="scene.scene_comments.length" >
                <li v-for="comment in scene.scene_comments">
                  <div>{{ comment.memo }}</div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    
      <!-- 編集 -->
      <div v-show="tab_scene === 2">
        <form class="detail-box"  @submit.prevent="confirmScene">
          <!-- 写真 -->
          <div>
            <img :src="scene.prop.url" :alt="scene.prop.name">
          </div>          

          <!-- 詳細 -->
          <div>
            <div>
              <!-- 登場人物 -->
              <label for="character_attr">登場人物</label>
              <select class="form__item" v-model="editForm_scene.character.section.section" v-on:change="selected">
                <option disabled value="">登場人物属性</option>
                <option v-for="(value, key) in optionCharacters">
                  {{ key }}
                </option>
              </select>

              <select class="form__item" v-model="editForm_scene.character_id" required>
                <option disabled value="">登場人物一覧</option>
                <option v-if="selectedCharacters" v-for="characters in selectedCharacters"
                        v-bind:value="characters.id">
                  {{ characters.name }}
                </option>
              </select>
            </div>

            <!-- 使用するか -->
            <div class="form__check">
              <label for="scene_usage_scene_edit" class="form__check__label">中間発表での使用</label>
              <input type="checkbox" id="scene_usage_scene_edit" class="form__check__input" v-model="editForm_scene.usage">
              
              <label for="scene_usage_guraduation_scene_edit" class="form__check__label">卒業公演での使用</label>
              <input type="checkbox" id="scene_usage_guraduation_scene_edit" class="form__check__input" v-model="editForm_scene.usage_guraduation" @change="selectGuraduation">

              <div v-if="guradutaion_tag">
                <input type="radio" id="scene_usage_left_scene_edit" class="form__check__input" value="left" v-model="editForm_scene.usage_stage">
                <label for="scene_usage_left_scene_edit" class="form__check__label">上手</label>

                <input type="radio" id="scene_usage_right_scene_edit" class="form__check__input" value="right" v-model="editForm_scene.usage_stage"></input>
                <label for="scene_usage_right_scene_edit" class="form__check__label">下手</label>                
              </div>

              
            </div>

            <!-- ページ数 -->
            <label for="page">ページ数</label>
            <small>例) 22, 24-25</small>
            <div v-if="scene.first_page">
              p. <input class="form__item" v-model="editForm_scene.first_page">
              ~ p. <input class="form__item" v-model="editForm_scene.final_page">
            </div>
            <div v-else>
              <input type="text"  id="page" class="form__item" v-model="editForm_scene.pages"></input>
            </div>       
            
            <div>
              <!-- 小道具名 -->
              <label for="scene_prop_select_edit">小道具</label>
              <select id="scene_prop_select_edit" class="form__item"  v-model="editForm_scene.prop_id" required>
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
            </div>

            <div>
              <label for="scene_comment_edit">メモ:</label>
              <ul v-if="editForm_scene.scene_comments.length" >
                <li v-for="comment in editForm_scene.scene_comments">
                  <textarea v-model="comment.memo">{{ comment.memo }}</textarea>
                </li>
              </ul>
              <div v-else>
                <textarea id="prop_comment_edit" class="form__item" v-model="editForm_scene.memo"></textarea>
              </div>
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
  
  import registerProp from '../pages/Register_Prop.vue'
  import confirmDialog_Edit from './Confirm_Dialog_Edit.vue'
  import confirmDialog_Delete from './Confirm_Dialog_Delete.vue'
  
  export default {
    // モーダルとして表示
    name: 'detailScene',
    components: {
      registerProp,
      confirmDialog_Edit,
      confirmDialog_Delete
    },
    props: {
      postScene: {
        type: Number,
        required: true
      }
    },
    // データ
    data() {
      return {
        // 表示する小道具のデータ
        scene: [],
        // 編集データ
        editForm_scene: {
          id: null,
          character_id: null,
          character: {
            name: null,
            section: {
                section: null
            },
          },
          prop_id: null,
          prop: {
            name: null,
            owner_id: '',
            owner: {
                name: ''
            },
            url: '',
            prop_comments: ''
          },      
          first_page: '',
          final_page: '',
          pages: '',
          usage: 0,
          usage_guraduation: 0,
          usage_stage: null,
          scene_comments: [],
          memo: ''
        },
        // 取得するデータ
        optionProps: [],
        // 連動プルダウン
        selectedCharacters: [],
        optionCharacters: [],
        // タブ
        tab_scene: 1,
        // 卒業公演
        guradutaion_tag: 0,
        // overlayのクラス
        overlay_class: 1,
        // 小道具登録
        showContent: false,
        postFlag: "",
        // エラー
        errors: {
          error: null,
        },
        // 編集confirm
        showContent_confirmEdit: false,
        postMessage_Edit: "",
        // 編集範囲
        editSceneMode_detail: "",
        editSceneMode_memo: "",
        editSceneMode_prop: "",
        // 削除confirm
        showContent_confirmDelete: false,
        postMessage_Delete: ""
      }
    },
    watch: {
      postScene: {
        async handler(postScene) {
          if(this.postScene){
            await  this.fetchCharacters(); // 最初にしないと間に合わない            
            await  this.fetchProps();
            await  this.fetchScene();
            
            const content_dom = this.$refs.content_detail_scene;
            const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
            if(content_rect.top < 0){
              this.overlay_class = 0;
            }else{
              this.overlay_class = 1;
            }           
          }       
        },
        immediate: true,
      },
      editSceneMode_prop: {
        async handler(editSceneMode_prop){
          if(this.editSceneMode_prop === 100){
            await this.fetchScene();
            // メッセージ登録
            this.$store.commit('message/setContent', {
              content: '使用シーンが変更されました！',
              timeout: 6000
            });
  
          }else if(this.editSceneMode_detail || this.editSceneMode_memo){
            await this.openModal_confirmEdit();
          }else if(this.editSceneMode_detail === 0 && this.editSceneMode_memo === 0){
            alert('元のデータと同じです！変更してください');
            this.editSceneMode_detail = "";
            this.editSceneMode_memo = "";
            this.editSceneMode_prop = "";
          }
        },
        immediate: true,
      },
      editSceneMode_detail: {
        async handler(editSceneMode_detail){
          if(this.editSceneMode_detail === 100 && this.editSceneMode_prop === 1){
            await this.editProp_usage(this.editForm_scene.prop_id);
          }
        },
        immediate: true,
      }
    },

    // 中間発表のみ変えたらページ遷移しない
    methods: {
      // シーンの詳細を取得
      async fetchScene () {    
        this.resetScene();
        this.tab_scene = 1;
        const response = await axios.get('/api/scenes/'+ this.postScene);

        if (response.status !== 200) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.scene = response.data;
        this.editForm_scene.id = this.scene.id;
        this.editForm_scene.character_id = this.scene.character_id;
        this.editForm_scene.character.name = this.scene.character.name;
        this.editForm_scene.character.section.section = this.scene.character.section.section;
        this.selected();
        this.editForm_scene.prop_id = this.scene.prop_id;
        this.editForm_scene.prop.name = this.scene.prop.name;
        this.editForm_scene.prop.owner_id = this.scene.prop.owner_id;
        if(this.scene.prop.owner){
          this.editForm_scene.prop.owner.name = this.scene.prop.owner.name;
        }else{
          this.editForm_scene.prop.owner.name = '';
        }
        
        this.editForm_scene.prop.url = this.scene.prop.url;
        this.editForm_scene.prop.prop_comments = this.scene.prop.prop_comments;
        this.editForm_scene.first_page = this.scene.first_page;
        this.editForm_scene.final_page = this.scene.final_page;
        this.editForm_scene.usage = this.scene.usage;
        this.editForm_scene.usage_guraduation = this.scene.usage_guraduation;
        if(this.scene.usage_guraduation){
          this.guradutaion_tag = 1;
        }
        if(this.scene.usage_left){
          this.editForm_scene.usage_stage = "left";
        }else if(this.scene.usage_right){
          this.editForm_scene.usage_stage = "right";
        }
        if(this.scene.scene_comments.length){
          this.scene.scene_comments.forEach((comment, index) => {
            this.editForm_scene.scene_comments[index] = Object.assign({}, this.editForm_scene.scene_comments, {id: comment.id}, {memo: comment.memo}, {scene_id: comment.scene_id});
          })
        }
        this.editSceneMode_detail = "";
        this.editSceneMode_memo = "";
        this.editSceneMode_prop = "";
      },
  
      // 登場人物を取得
      async fetchCharacters () {
        const response = await axios.get('/api/informations/characters');

        if (response.status !== 200) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.characters = response.data;
  
        // 区分と登場人物をオブジェクトに変換する
        let sections = new Object();
        this.characters.forEach(function(section){
          sections[section.section] = section.characters;
        });
        this.optionCharacters = sections;        
      },
        
      // 小道具一覧を取得
      async fetchProps () {
        const response = await axios.get('/api/props');

        if (response.status !== 200) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.optionProps = response.data;
      },
      
      // タブ切り替え
      alterTab() {
        if(this.tab_scene === 1){
          this.tab_scene = 2;
        }else{
          this.tab_scene = 1;
        }
      },

      // 連動プルダウン
      selected() {
        this.selectedCharacters = this.optionCharacters[this.editForm_scene.character.section.section];
      },

      // 卒業公演の使用にチェックが付いたか
      selectGuraduation() {
        if(!this.guradutaion_tag){
          this.guradutaion_tag = 1;
        }else{
          this.guradutaion_tag = 0;
          this.editForm_scene.usage_stage = null;
        }
      },

      // 小道具登録のモーダル表示 
      openModal_register () {
        this.showContent = true;
        this.postFlag = 1;
      },
      // 小道具登録のモーダル非表示
      closeModal_register (){
        this.showContent = false;
      },

      // 諸々リセット
      resetScene() {
        this.scene = [];
        this.editForm_scene.id = null;
        this.editForm_scene.character_id = null;
        this.editForm_scene.character.name = null;
        this.editForm_scene.character.section.section = null;
        this.editForm_scene.prop_id = null;
        this.editForm_scene.prop.name = null;
        this.editForm_scene.prop.owner_id = '';
        this.editForm_scene.prop.owner.name = '';
        this.editForm_scene.prop.url = '';
        this.editForm_scene.prop.prop_comments = '';
        this.editForm_scene.first_page = '';
        this.editForm_scene.final_page = '';
        this.editForm_scene.pages = '';
        this.editForm_scene.usage = 0;
        this.editForm_scene.usage_guraduation = 0;
        this.editForm_scene.usage_stage = null;
        this.editForm_scene.scene_comments = [];
        this.editForm_scene.memo = '';
        // 卒業公演
        this.guradutaion_tag = 0;
      },
  
      // 編集エラー
      confirmScene () {
        if(this.scene.id === this.editForm_scene.id && (this.scene.character_id !== this.editForm_scene.character_id || this.scene.prop_id !== this.editForm_scene.prop_id || this.scene.first_page !== this.editForm_scene.first_page || this.scene.final_page !== this.editForm_scene.final_page || this.scene.usage != this.editForm_scene.usage || this.scene.usage_guraduation != this.editForm_scene.usage_guraduation || ((!this.scene.usage_left && !this.scene.usage_right) && this.editForm_scene.usage_stage) || ((this.scene.usage_left && !this.scene.usage_right) && this.editForm_scene.usage_stage === "right") || ((!this.scene.usage_left && this.scene.usage_right) && this.editForm_scene.usage_stage === "left") || ((this.scene.usage_left || this.scene.usage_right) && !this.editForm_scene.usage_stage))&& !this.editForm_scene.pages){
          // 元々何ページから何ページと指定があった // これはupdateだけでいい
          this.editSceneMode_detail = 1; // 'page_update'

          if(this.scene.usage != this.editForm_scene.usage || this.scene.usage_guraduation != this.editForm_scene.usage_guraduation || ((!this.scene.usage_left && !this.scene.usage_right) && this.editForm_scene.usage_stage) || ((this.scene.usage_left && !this.scene.usage_right) && this.editForm_scene.usage_stage === "right") || ((!this.scene.usage_left && this.scene.usage_right) && this.editForm_scene.usage_stage === "left") ||  ((this.scene.usage_left || this.scene.usage_right) && !this.editForm_scene.usage_stage)){
            // 小道具を更新する
            this.editSceneMode_prop = 1; // 'prop_update'
          }else{
            this.editSceneMode_prop = 0;
          }
        }else if(this.scene.id === this.editForm_scene.id && (this.scene.character_id !== this.editForm_scene.character_id || this.scene.prop_id !== this.editForm_scene.prop_id || this.scene.first_page !== this.editForm_scene.first_page || this.scene.final_page !== this.editForm_scene.final_page || this.scene.usage !== this.editForm_scene.usage) || (this.editForm_scene.pages)){
          // 新たにページ数追加 // これはupdateだけじゃだめ
          this.editSceneMode_detail = 2; // 'page_store'
          this.editSceneMode_prop = 0;
        }else{
          this.editSceneMode_detail = 0
          this.editSceneMode_prop = 0;
        }
  
        if(this.scene.id === this.editForm_scene.id && !this.scene.scene_comments.length && this.editForm_scene.memo){
          // メモ新規投稿
          this.editSceneMode_memo = 1; // 'memo_store'
          if(this.editSceneMode_prop !== 1){
            this.editSceneMode_prop = 0;
          }
        }else if(this.scene.id === this.editForm_scene.id && this.scene.scene_comments.length){
          if(this.scene.id === this.editForm_scene.id && this.scene.scene_comments[0].id && !this.editForm_scene.scene_comments[0].memo){
            // メモ削除
            this.editSceneMode_memo = 2; //'memo_delete'
            if(this.editSceneMode_prop !== 1){
            this.editSceneMode_prop = 0;
          }
          }else if(this.scene.id === this.editForm_scene.id && this.scene.scene_comments[0].id && this.scene.scene_comments[0].memo !== this.editForm_scene.scene_comments[0].memo){
            // メモアップデート
            this.editSceneMode_memo = 3; // 'memo_update'
            if(this.editSceneMode_prop !== 1){
            this.editSceneMode_prop = 0;
          }
          }else{
            this.editSceneMode_memo = 0;
            if(this.editSceneMode_prop !== 1){
            this.editSceneMode_prop = 0;
          }
          }
        }else{
          this.editSceneMode_memo = 0;
          if(this.editSceneMode_prop !== 1){
            this.editSceneMode_prop = 0;
          }
        }
      },
  
      // 編集confirmのモーダル表示 
      openModal_confirmEdit () {
        this.showContent_confirmEdit = true;
        this.postMessage_Edit = '以下のように編集します。';
      },
      // 編集confirmのモーダル非表示_OKの場合
      async closeModal_confirmEdit_OK() {
        this.showContent_confirmEdit = false;
        if(this.editSceneMode_detail){
          await this.editScene();
        }
        if(this.editSceneMode_memo){
          await this.editScene_memo();
        }
      },
      // 編集confirmのモーダル非表示_Cancelの場合
      closeModal_confirmEdit_Cancel() {
        this.showContent_confirmEdit= false;
      },

      // first_pageとfinal_pageに分割する
      first_finalDivide(str) {
        return str.split(/-|ー|‐|―|⁻|－|～|—|₋|ｰ|~/);
      },

      // 全角→半角
      hankaku2Zenkaku(str) {
        return str.replace(/[０-９]/g, function(s) {
          return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
        });
      },
  
      // 基本情報を編集する
      async editScene () {
        let usage_left = '';
        let usage_right = '';
        if(this.editForm_scene.usage_stage === "left"){
          usage_left = 1;
        }else if(this.editForm_scene.usage_stage === "right"){
          usage_right = 1;
        }
        if(this.editSceneMode_detail === 1){
          // 元々ページ数の指定があった
          this.editSceneMode_detail = "change"
          const response = await axios.post('/api/scenes/'+ this.scene.id, {
            character_id: this.editForm_scene.character_id,
            prop_id: this.editForm_scene.prop_id,
            first_page: this.editForm_scene.first_page,
            final_page: this.editForm_scene.final_page,
            usage: this.editForm_scene.usage,
            usage_guraduation: this.editForm_scene.usage_guraduation,
            usage_left: usage_left,
            usage_right: usage_right
          });
  
          if (response.status === 422) {
            this.errors.error = response.data.errors;
            return false;
          }

          if (response.status !== 204) {
            this.$store.commit('error/setCode', response.status);
            return false;
          }

          this.editSceneMode_detail = 100;
          if(this.editSceneMode_memo === 0 && this.editSceneMode_prop === 0){
            this.editSceneMode_memo = 100;
            this.editSceneMode_prop = 100;
          }

        }else if(this.editSceneMode_detail === 2){
          // ページ数を新たに指定
          this.editSceneMode_detail = "change";

          // ページを分割
          let first_pages = [];
          let final_pages = [];
          first_pages[0] = 0;
          final_pages[0] = 0;
          if(this.editForm_scene.pages){
            let pages_before = this.editForm_scene.pages.split(/,|、|，|\s+/);
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
      
          let memo = '';
          if(this.editForm_scene.memo){
            memo = this.editForm_scene.memo;
          }else if(this.editForm_scene.scene_comments.length){
            memo = this.editForm_scene.scene_comments[0].memo;
          }
          let last_flag = false;
          first_pages.forEach(async function(page, index) {
            if(index === 0){
              // update
              const response = await axios.post('/api/scenes/' + this.scene.id, {
                character_id: this.editForm_scene.character_id,
                prop_id: this.editForm_scene.prop_id,
                first_page: page,
                final_page: final_pages[index],
                usage: this.editForm_scene.usage,
                usage_left: usage_left,
                usage_right: usage_right
              });

              if (response.status === 422) {
                this.errors.error = response.data.errors;
                return false
              }
    
              if (response.status !== 204) {
                this.$store.commit('error/setCode', response.status);
                return false;
              }

              if(index === first_pages.length-1){
                this.editSceneMode_detail = 100;
                if(this.editSceneMode_memo === 0 && this.editSceneMode_prop === 0){
                  this.editSceneMode_memo = 100;
                  this.editSceneMode_prop = 100;
                }
              }
              
            }else{
              // store
              const response = await axios.post('/api/scenes', {
                character_id: this.editForm_scene.character_id,
                prop_id: this.editForm_scene.prop_id,
                first_page: page,
                final_page: final_pages[index],
                usage: this.editForm_scene.usage,
                usage_left: usage_left,
                usage_right: usage_right,
                memo: memo
              });

              if (response.status === 422) {
                this.errors.error = response.data.errors;
                return false;
              }
    
              if (response.status !== 201) {
                this.$store.commit('error/setCode', response.status);
                return false;
              }

              if(index === first_pages.length-1){
                this.editSceneMode_detail = 100;
                if(this.editSceneMode_memo === 0 && this.editSceneMode_prop === 0){
                  this.editSceneMode_memo = 100;
                  this.editSceneMode_prop = 100;
                }
              }
            }
          }, this);
        }
      },
      // メモを更新する
      async editScene_memo() {
        if(this.editSceneMode_memo === 1){
          // メモ新規投稿
          const response = await axios.post('/api/scene_comments', {
            scene_id: this.editForm_scene.id,
            memo: this.editForm_scene.memo
          });

          if (response.status === 422) {
            this.errors.error = response.data.errors;
            return false;
          }
  
          if (response.status !== 201) {
            this.$store.commit('error/setCode', response.status);
            return false;
          }

          this.editSceneMode_memo = 100;
          if(this.editSceneMode_prop === 0){
            this.editSceneMode_prop = 100;
          }
  
        }else if(this.editSceneMode_memo === 2){
          // メモ削除
          const response = await axios.delete('/api/scene_comments/'+ this.scene.scene_comments[0].id);

          if (response.status === 422) {
            this.errors.error = response.data.errors;
            return false;
          }
  
          if (response.status !== 204) {
            this.$store.commit('error/setCode', response.status);
            return false;
          }

          this.editSceneMode_memo = 100;
          if(this.editSceneMode_prop === 0){
            this.editSceneMode_prop = 100;
          }
  
        }else if(this.editSceneMode_memo === 3){
          // メモアップデート
          const response = await axios.post('/api/scene_comments/'+ this.scene.scene_comments[0].id, {
            memo: this.editForm_scene.scene_comments[0].memo
          });

          if (response.status === 422) {
            this.errors.error = response.data.errors;
            return false;
          }
  
          if (response.status !== 204) {
            this.$store.commit('error/setCode', response.status);
            return false;
          }
          
          this.editSceneMode_memo = 100;
          if(this.editSceneMode_prop === 0){
            this.editSceneMode_prop = 100;
          }
        }
      },

      // 小道具を更新する
      async editProp_usage() {
        // 同時に行う
        if(this.scene.usage != this.editForm_scene.usage){
          await this.editProp_usage_passo();
        }
        if(this.scene.usage_guraduation != this.editForm_scene.usage_guraduation || this.editForm_scene.usage_guraduation){
          await this.editProp_usage_guraduation();
        }
      },
      async editProp_usage_passo() {
        if(this.editForm_scene.usage){
          // 中間発表0→1
          const response_prop = await axios.post('/api/props/'+ this.editForm_scene.prop_id, {
            method: 'usage_change',
            usage: 1
          });

          if (response_prop.status === 422) {
            this.errors.error = response_prop.data.errors;
            return false;
          }
 
          if (response_prop.status !== 204) {
            this.$store.commit('error/setCode', response_prop.status);
            return false;
          }
          
          if(this.scene.usage_guraduation == this.editForm_scene.usage_guraduation && ((this.scene.usage_left && this.editForm_scene.usage_stage === "left") || (this.scene.usage_right && this.editForm_scene.usage_stage === "right") || ((!this.scene.usage_left && !this.scene.usage_right) && !this.editForm_scene.usage_stage))) {
            this.editSceneMode_prop = 100;
          }
          
        }else{
          // 中間発表1→0
          const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
            method: 'usage_0_change',
            id: this.scene.id,
            usage: 0
          });

          if (response_prop.status === 422) {
            this.errors.error = response_prop.data.errors;
            return false;
          }
 
          if (response_prop.status !== 204) {
            this.$store.commit('error/setCode', response_prop.status);
            return false;
          }
        
          if(this.scene.usage_guraduation == this.editForm_scene.usage_guraduation && ((this.scene.usage_left && this.editForm_scene.usage_stage === "left") || (this.scene.usage_right && this.editForm_scene.usage_stage === "right") || ((!this.scene.usage_left && !this.scene.usage_right) && !this.editForm_scene.usage_stage))) {
            this.editSceneMode_prop = 100;
          }
        }          
      },
      async editProp_usage_guraduation() {
        if(this.scene.usage_guraduation != this.editForm_scene.usage_guraduation){
          if(!this.scene.usage_guraduation && this.editForm_scene.usage_guraduation){
            // 卒業公演0→1
            if(this.scene.usage_left && this.editForm_scene.usage_stage === "right"){
              // 卒業公演0→1、上手→下手で使用
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_left_to_right_change',
                id: this.scene.id,
                usage_guraduation: 1,
                usage_left: 0,
                usage_right: 1,
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;

            }else if(this.scene.usage_right && this.editForm_scene.usage_stage === "left"){
              // 卒業公演0→1、下手→上手で使用
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_right_to_left_change',
                id: this.scene.id,
                usage_guraduation: 1,
                usage_left: 1,
                usage_right: 0
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }
             
              this.editSceneMode_prop = 100;
      
            }else if(!this.scene.usage_left && !this.scene.usage_right && this.editForm_scene.usage_stage === "left"){
              // 卒業公演0→1、上手0→1
              const response_prop = await axios.post('/api/props/'+ this.editForm_scene.prop_id, {
                method: 'usage_left_change',
                usage_guraduation: 1,
                usage_left: 1,
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }

              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;

            }else if(!this.scene.usage_left && this.editForm_scene.usage_stage === "right"){
              // 卒業公演0→1、下手0→1
              const response_prop = await axios.post('/api/props/'+ this.editForm_scene.prop_id, {
                method: 'usage_right_change',
                usage_guraduation: 1,
                usage_right: 1,
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;

            }else if(this.scene.usage_left && !this.editForm_scene.usage_stage){
              // 卒業公演0→1、上手1→0
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_1_left_0_change',
                id: this.scene.id,
                usage_guraduation: 1,
                usage_left: 0,
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }
              
              this.editSceneMode_prop = 100;

            }else if(this.scene.usage_right && !this.editForm_scene.usage_stage){
              // 卒業公演0→1、下手1→0
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_1_right_0_change',
                id: this.scene.id,
                usage_guraduation: 1,
                usage_right: 0,
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;

            }else{
              // 卒業公演0→1
              const response_prop = await axios.post('/api/props/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_change',
                usage_guraduation: 1
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;
            }
          }else{
            // 卒業公演1→0
            if(this.scene.usage_left && !this.editForm_scene.usage_stage){
              // 卒業公演1→0、上手1→0
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_0_left_0_change',
                id: this.scene.id,
                usage_guraduation: 0,
                usage_left: 0
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;

            }else if(this.scene.usage_right && !this.editForm_scene.usage_stage){
              // 卒業公演1→0、下手1→0
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_0_right_0_change',
                id: this.scene.id,
                usage_guraduation: 0,
                usage_right: 0
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;

            }else if(this.scene.usage_guraduation && !this.editForm_scene.usage_guraduation){
              // 卒業公演1→0
              const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
                method: 'usage_guraduation_0_change',
                id: this.scene.id,
                usage_guraduation: 0
              });

              if (response_prop.status === 422) {
                this.errors.error = response_prop.data.errors;
                return false;
              }
 
              if (response_prop.status !== 204) {
                this.$store.commit('error/setCode', response_prop.status);
                return false;
              }

              this.editSceneMode_prop = 100;
            }
          }
        }else if(this.editForm_scene.usage_guraduation){
          // 卒業公演1→1
          if(this.scene.usage_left && this.editForm_scene.usage_stage === "right"){
            // 上手→下手
            const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
              method: 'usage_left_to_right_change',
              id: this.scene.id,
              usage_left: 0,
              usage_right: 1
            });

            if (response_prop.status === 422) {
              this.errors.error = response_prop.data.errors;
              return false;
            }
 
            if (response_prop.status !== 204) {
              this.$store.commit('error/setCode', response_prop.status);
              return false;
            }

            this.editSceneMode_prop = 100;

          }else if(this.scene.usage_right && this.editForm_scene.usage_stage === "left"){
            // 下手→上手
            const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
              method: 'usage_right_to_left_change',
              id: this.scene.id,
              usage_left: 1,
              usage_right: 0
            });

            if (response_prop.status === 422) {
              this.errors.error = response_prop.data.errors;
              return false;
            }
 
            if (response_prop.status !== 204) {
              this.$store.commit('error/setCode', response_prop.status);
              return false;
            }

            this.editSceneMode_prop = 100;

          }else if(!this.scene.usage_left && this.editForm_scene.usage_stage === "left"){
            // 上手0→1
            const response_prop = await axios.post('/api/props/'+ this.editForm_scene.prop_id, {
              method: 'usage_left_change',
              usage_left: 1
            });

            if (response_prop.status === 422) {
              this.errors.error = response_prop.data.errors;
              return false;
            }
 
            if (response_prop.status !== 204) {
              this.$store.commit('error/setCode', response_prop.status);
              return false;
            }

            this.editSceneMode_prop = 100;

          }else if(!this.scene.usage_right && this.editForm_scene.usage_stage === "right"){
            // 下手0→1
            const response_prop = await axios.post('/api/props/'+ this.editForm_scene.prop_id, {
              method: 'usage_right_change',
              usage_right: 1
            });

            if (response_prop.status === 422) {
              this.errors.error = response_prop.data.errors;
              return false;
            }
 
            if (response_prop.status !== 204) {
              this.$store.commit('error/setCode', response_prop.status);
              return false;
            }

            this.editSceneMode_prop = 100;

          }else if(this.scene.usage_left && !this.editForm_scene.usage_stage){
            // 上手1→0
            const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
              method: 'usage_left_0_change',
              id: this.scene.id,
              usage_left: 0
            });

            if (response_prop.status === 422) {
              this.errors.error = response_prop.data.errors;
              return false;
            }
 
            if (response_prop.status !== 204) {
              this.$store.commit('error/setCode', response_prop.status);
              return false;
            }

            this.editSceneMode_prop = 100;

          }else if(this.scene.usage_right && !this.editForm_scene.usage_stage){
            // 下手1→0
            const response_prop = await axios.post('/api/props_deep/'+ this.editForm_scene.prop_id, {
              method: 'usage_right_0_change',
              id: this.scene.id,
              usage_right: 0
            });

            if (response_prop.status === 422) {
              this.errors.error = response_prop.data.errors;
              return false;
            }
 
            if (response_prop.status !== 204) {
              this.$store.commit('error/setCode', response_prop.status);
              return false;
            }

            this.editSceneMode_prop = 100;
          }
        }
      },
  
      // 削除confirmのモーダル表示 
      openModal_confirmDelete (id) {
        this.showContent_confirmDelete = true;
        this.postMessage_Delete = '本当に削除しますか？';
      },
      // 削除confirmのモーダル非表示_OKの場合
      async closeModal_confirmDelete_OK() {
        this.showContent_confirmDelete = false;
        this.$emit('close');
        await this.deletScene();
      },
      // 削除confirmのモーダル非表示_Cancelの場合
      closeModal_confirmDelete_Cancel() {
        this.showContent_confirmDelete = false;
      },
  
      // 削除する
      async deletScene() {
        const response = await axios.delete('/api/scenes/'+ this.scene.id);
  
        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }
  
        this.resetScene();
  
        // メッセージ登録
        this.$store.commit('message/setContent', {
          content: '使用シーンが1つ削除されました！',
          timeout: 6000
        });
  
        this.$emit('close');
      }
    }
  }
  </script>