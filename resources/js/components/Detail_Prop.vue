<template>
  <div v-bind:class="[overlay_class === 1 ? 'overlay' : 'overlay overlay-custom']" @click.self="$emit('close')">
    <div class="content content-detail panel" ref="content_detail_prop">
      <div class="area--detail-box">
        <!--- 閲覧/編集 -->
        <div class="form__button button--area--detail-box">
          <button type="button" v-show="tab === 1" class="button button--inverse" @click="alterTab"><i class="fas fa-edit fa-fw"></i>編集</button>
          <button type="button" v-show="tab === 2" class="button button--inverse" @click="alterTab"><i class="fas fa-eye fa-fw"></i>閲覧</button>
        </div>

        <div v-show="tab === 1">
          <div class="detail-box">
            <!-- 写真 -->
            <div>
              <div v-if="prop.url" class="detail-box--img">
                <img :src="prop.url" :alt="prop.name"></img>
              </div>
              <div v-else>
                <img :src="prop.url" :alt="prop.name"></img>
              </div>
            </div>
            <!-- 詳細 -->
            <div>
              <!--- 削除ボタン -->
              <div class="form__button">
                <button type="button" class="button button--inverse" @click="openModal_confirmDelete"><i class="fas fa-trash fa-fw"></i>削除</button>
              </div>
              <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
            
              <div>
                <h1>{{ prop.name }}</h1>
              </div>
              
              <div>所有者: <span v-if="prop.owner">{{ prop.owner.name }}</span></div>

              <div>個数: <span v-if="prop.quantity > 1">{{ prop.quantity }}</span></div>

              <div>ピッコロに持ってきたか: <span v-if="prop.location" class="usage-show"><i class="fas fa-check fa-fw"></i></span></div>

              <div>
                作るかどうか: 
                <span v-if="prop.handmade === 1" class="usage-show">完</span>
                <span v-else-if="prop.handmade === 2" class="usage-show">仕</span>
                <span v-else-if="prop.handmade === 3" class="usage-show">未</span>
              </div>

              <div>これで決定か: <span v-if="prop.decision" class="usage-show"><i class="fas fa-check fa-fw"></i></span></div>

              <div>
                <!-- 中間発表 -->
                <span v-if="prop.usage" class="usage-show">Ⓟ</span>
                <!-- 卒業公演 -->
                <span v-if="prop.usage_guraduation" class="usage-show">Ⓖ</span>
                <!-- 上手 -->
                <span v-if="prop.usage_left" class="usage-show">㊤</span>
                <!-- 下手 -->
                <span v-if="prop.usage_right" class="usage-show">㊦</span>
              </div>

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
                      <span class="prop-detail--scene-area">
                        <div>
                          <!-- 名前 -->
                          <span>{{ scene.character.name }}</span>
                          <!-- 何ページ -->
                          <span v-if="scene !== null && scene.first_page !== null && scene.final_page !== 1000">: p.{{ scene.first_page }} 
                            <span v-if="scene !== null && scene.final_page !== null && scene.final_page !== 1000"> ~ p.{{ scene.final_page}}</span>
                          </span>
                          <span v-if="scene !== null && scene.first_page === 1 && scene.final_page === 1000">
                            : 全シーン
                          </span>
                        </div>

                        <div>
                          <!-- 決定かどうか -->
                          <span v-if="scene.decision" class="usage-show">決定</span>

                          <!-- 使用状況 -->
                          <span v-if="scene.usage" class="usage-show">Ⓟ</span>
                          <span v-if="scene.usage_guraduation" class="usage-show">Ⓖ</span>
                          <span v-if="scene.usage_left" class="usage-show">㊤</span>
                          <span v-if="scene.usage_right" class="usage-show">㊦</span>
                        </div>
                        
                        <!-- メモ -->
                        <div>
                          <ul v-if="scene.scene_comments.length">
                            <li v-for="comment in scene.scene_comments">
                              <div>{{ comment.memo }}</div>
                            </li>
                          </ul>
                        </div>

                      </span>
                    </li>
                  </ol>
              </div> 
            </div>
          </div>
        </div>
    
        <!-- 編集 -->
        <div v-show="tab === 2" class="edit-area">
          <form class="detail-box"  @submit.prevent="confirmProp">
            <div>
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
            </div>         

            <!-- 詳細 -->
            <div>
              <!--- 送信ボタン -->
              <div class="form__button">
                <button type="submit" class="button button--inverse"><i class="fas fa-edit fa-fw"></i>編集</button>
              </div>

              <div>
                <label for="prop_name_edit">小道具名</label>
                <input type="text" id="prop_name_edit" class="form__item" v-model="editForm_prop.name" @input="handleNameInput" placeholder="小道具" required>
                <!-- <input type="text" name="furigana" id="prop_furigana_edit" class="form__item form__item--furigana" v-model="editForm_prop.kana" placeholder="ふりがな" required> -->
                <input type="text" name="furigana" :id="prop.id" class="form__item form__item--furigana" v-model="editForm_prop.kana" placeholder="ふりがな" required>
              </div>            
              
              <div>
                <label for="prop_owner_edit">所有者:</label> 
                <select id="prop_owner_edit" class="form__item" v-model="editForm_prop.owner_id">
                  <option disabled value="">持ち主一覧</option>
                  <option v-for="owner in optionOwners" v-bind:value="owner.id">
                    {{ owner.name }}
                  </option>
                </select>
              </div>

              <!-- 個数 -->
              <div class="checkbox-area--together">
                <label for="prop_quantity_edit">個数</label>
                <input type="number" id="prop_quantity_edit" class="form__item form__item--furigana" v-model="editForm_prop.quantity" placeholder="個数">
              </div>

              <!-- ピッコロに持ってきたか -->
              <div  class="checkbox-area--together">
                <label for="prop_location_edit" class="form__check__label">ピッコロに持ってきたか</label>
                <input type="checkbox" id="prop_location_edit" class="form__check__input" v-model="editForm_prop.location">
              </div>

              <!-- 作る必要があるか -->
              <div class="checkbox-area--together">
                <label for="prop_handmade_edit">作る必要がある</label>
                <input type="checkbox" id="prop_handmade_edit" v-model="editForm_prop.handmade"></input>

                <div class="checkbox-area--together">
                <!-- 作る必要があるなら -->
                  <input type="radio" id="prop_handmade_complete_edit" :disabled="!editForm_prop.handmade" value=1 v-model="editForm_prop.handmade_complete"></input>
                  <label for="prop_handmade_complete_edit">完成</label>

                  <input type="radio" id="prop_handmade_progress_edit" :disabled="!editForm_prop.handmade" value=2 v-model="editForm_prop.handmade_complete"></input>
                  <label for="prop_handmade_progress_edit">仕掛中</label>

                  <input type="radio" id="prop_handmade_unfinished_edit" :disabled="!editForm_prop.handmade" value=3 v-model="editForm_prop.handmade_complete"></input>
                  <label for="prop_handmade_unfinished_edit">未着手</label>
                </div>          
              </div>

              <!-- これで決定か -->
              <div  class="checkbox-area--together">
                <label for="prop_decision_edit" class="form__check__label">これで決定か</label>
                <input type="checkbox" id="prop_decision_edit" class="form__check__input" v-model="editForm_prop.decision">
              </div>

              <!-- 使用するか -->
              <div>
                <div  class="checkbox-area--together">
                  <label for="prop_usage_prop_edit" class="form__check__label">中間発表での使用</label>
                  <input type="checkbox" id="prop_usage_prop_edit" class="form__check__input" v-model="editForm_prop.usage">
                </div>
                
                <div  class="checkbox-area--together">
                  <label for="prop_usage_guraduation_prop_edit" class="form__check__label">卒業公演での使用</label>
                  <input type="checkbox" id="prop_usage_guraduation_scene_edit" class="form__check__input" v-model="editForm_prop.usage_guraduation" @change="selectGuraduation">
                </div>

                <div v-if="guradutaion_tag"  class="checkbox-area--together">
                  <input type="checkbox" id="prop_usage_left_prop_edit" class="form__check__input" value="left" v-model="editForm_prop.usage_left">
                  <label for="prop_usage_left_prop_edit" class="form__check__label">上手</label>

                  <input type="checkbox" id="prop_usage_right_prop_edit" class="form__check__input" value="right" v-model="editForm_prop.usage_right"></input>
                  <label for="prop_usage_right_prop_edit" class="form__check__label">下手</label>                
                </div>
              </div>

              <div>
                <label for="prop_comment_edit">メモ:</label>
                <ul v-if="editForm_prop.prop_comments.length" >
                  <li v-for="comment in editForm_prop.prop_comments">
                    <textarea class="form__item" v-model="comment.memo">{{ comment.memo }}</textarea>
                  </li>
                </ul>
                <div v-else>
                  <textarea id="prop_comment_edit" class="form__item" v-model="editForm_prop.memo" placeholder="メモ"></textarea>
                </div>
              </div>

              <!-- <div>
                <label>シーン:</label>
                <div v-if="editForm_prop.scenes.length && editForm_prop.scenes[0].id">
                  <ol>
                    <li v-for="(scene, index) in editForm_prop.scenes"> -->
                      <!-- 名前 -->
                      <!-- <label for="character_attr">登場人物</label>
                      <select class="form__item" v-model="scene.section" v-on:change="selected(index)">
                        <option disabled value="">登場人物属性</option>
                        <option v-for="(value, key) in optionCharacters">
                          {{ key }}
                        </option>
                      </select>
  
                      <select class="form__item" v-model="scene.character_id" required>
                        <option disabled value="">登場人物一覧</option>
                          <option v-if="selectedCharacters" v-for="characters in selectedCharacters[index]"
                           v-bind:value="characters.id">
                            {{ characters.name }}
                          </option>
                      </select> -->

                      <!-- ページ数 -->
                      <!-- <label>ページ数</label>
                      <div> p. <input class="form__item" v-model="scene.first_page">
                         ~ p. <input class="form__item" v-model="scene.final_page">
                      </div> -->

                      <!-- 使用するか -->
                      <!-- <div>
                        <label>中間発表での使用</label>
                        <input type="checkbox" class="form__check__input" v-model="scene.usage"></input>          
                      </div> -->
        
                      <!-- コメント -->
                      <!-- <label for="comment_scene">コメント</label>
                      <div>
                        <ul v-if="scene.scene_comments.length">
                          <li v-for="comment in scene.scene_comments">
                            <textarea class="form__item" v-model="comment.memo"></textarea>
                          </li>
                        </ul>
                        <div v-else>
                          <textarea class="form__item" v-model="scene.memo"></textarea>
                        </div>
                      </div>
                    </li>
                  </ol>
                </div>
  
                <div v-else> -->
                  <!-- 登場人物 -->
                  <!-- <label>登場人物</label>
                  <select class="form__item" v-model="editForm_prop.scenes[0].section" v-on:change="selected(0)">
                    <option disabled value="">登場人物属性</option>
                    <option v-for="(value, key) in optionCharacters">
                      {{ key }}
                    </option>
                  </select>

                  <select class="form__item" v-model="editForm_prop.scenes[0].character_id" required>
                    <option disabled value="">登場人物一覧</option>
                    <option v-if="selectedCharacters" v-for="characters in selectedCharacters"
                     v-bind:value="characters.id">
                      {{ characters.name }}
                    </option>
                  </select> -->

                  <!-- ページ数 -->
                  <!-- <label for="page">ページ数</label>
                  <small>例) 22, 24-25</small>
                  <input type="text"  id="page" class="form__item" v-model="editForm_prop.scenes[0].pages"> -->

                  <!-- 使用するか -->
                  <!-- <div class="form__check">
                    <label for="usage_scene" class="form__check__label">中間発表での使用</label>
                    <input type="checkbox" id="usage_scene" class="form__check__input" v-model="editForm_prop.scenes[0].usage" checked></input>          
                  </div> -->
        
                  <!-- コメント -->
                  <!-- <label for="comment_scene">コメント</label>
                  <textarea class="form__item" id="comment_scene" v-model="editForm_prop.scenes[0].scene_comments[0].memo"></textarea>
                </div>
              </div>  -->
            </div>
          </form>
          <confirmDialog_Edit :confirm_dialog_edit_message="postMessage_Edit" v-show="showContent_confirmEdit" @Cancel_Edit="closeModal_confirmEdit_Cancel" @OK_Edit="closeModal_confirmEdit_OK"/>
        </div>
      </div>
      
      <button type="button" @click="$emit('close')" class="button button--inverse button--area--detail-box">閉じる</button>
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
      // ページの並び順
      page_order: [], 
      // 編集データ
      editForm_prop: {
        id: null,
        name: null,
        kana: null,
        owner: {
          name: ''
        },
        owner_id: '',
        quantity: 1,
        location: 0,
        handmade: 0,
        handmade_complete: 1,
        decision: 0,
        url: '',
        public_id: '',
        usage: 0,
        usage_guraduation: 0,
        usage_left: 0,
        usage_right: 0,
        photo: 0,
        prop_comments: [],
        scenes: []
      },
      // 取得するデータ
      props: [],
      optionOwners: [],
      // 連動プルダウン
      selectedCharacters: [],
      optionCharacters: [],
      // タブ
      tab: 1,
      // 卒業公演
      guradutaion_tag: 0,
      // overlayのクラス
      overlay_class: 1,
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
      // ユニコード
      first_uni: 9312, // ①
      final_uni: 9331,  // ⑳
      // 編集範囲
      editPropMode_detail: "",
      editPropMode_memo: "",
      // 削除confirm
      showContent_confirmDelete: false,
      postMessage_Delete: ""
    }
  },
  watch: {
    postProp: {
      async handler(postProp) {
        if(this.postProp){
          this.overlay_class = 1;

          this.page_order[0] = 1000;
          for(let i=1; i < 100; i++ ){
            this.page_order[i] = i;
          }
          await this.fetchCharacters(); // 最初にしないと間に合わない
          await this.fetchProp();
          await this.fetchOwners();
          await this.fetchProps();

          // ふりがなのinput要素のidは省略可能
          // 使用シーン登録時のid=propと被るから
          const autokana_id = '#'+ this.prop.id;
          autokana = AutoKana.bind(autokana_id);
          autokana.input = this.prop.kana;

          const content_dom = this.$refs.content_detail_prop;
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
    editPropMode_memo: {
      async handler(editPropMode_memo){
        if(this.editPropMode_detail === 100 || this.editPropMode_memo === 100){
          this.resetProp();
          await this.fetchProp();

          // 調整
          this.$nextTick(() => {
            const content_dom = this.$refs.content_detail_prop;
            const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
            if(content_rect.top < 0){
              this.overlay_class = 0;
            }else{
              this.overlay_class = 1;
            }
          });

          // メッセージ登録
          this.$store.commit('message/setContent', {
            content: '小道具が変更されました！',
            timeout: 6000
          });
        }else if(this.editPropMode_detail || this.editPropMode_memo){
          await this.openModal_confirmEdit();
        }else if(this.editPropMode_detail === 0 && this.editPropMode_memo === 0){
          alert('元のデータと同じです！変更してください');
          this.editPropMode_detail = "";
          this.editPropMode_memo = "";
        }
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
      this.tab = 1;
      const response = await axios.get('/api/props/'+ this.postProp);

      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.prop = response.data;
      // this.editForm_prop = JSON.parse(JSON.stringify(this.prop)); // そのままコピーするとコピー元も変更される
      this.editForm_prop.id = this.prop.id;
      this.editForm_prop.name = this.prop.name;
      this.editForm_prop.kana = this.prop.kana;
      if(this.prop.owner_id){
        this.editForm_prop.owner_id = this.prop.owner_id;
        this.editForm_prop.owner.name = this.prop.owner.name;
      }else{
        this.editForm_prop.owner_id = '';
        this.editForm_prop.owner.name = '';
      }

      this.editForm_prop.quantity = this.prop.quantity;

      this.editForm_prop.location = this.prop.location;

      this.editForm_prop.handmade = this.prop.handmade; // 0: 作らない、1: 完成、2: 仕掛中、3: 未着手

      if(this.prop.handmade){
        this.editForm_prop.handmade_complete = this.prop.handmade;
      }else{
        this.editForm_prop.handmade_complete = 1;
      }

      this.editForm_prop.decision = this.prop.decision;

      this.editForm_prop.url = this.prop.url;
      this.editForm_prop.public_id = this.prop.public_id;

      this.editForm_prop.usage = this.prop.usage;
      this.editForm_prop.usage_guraduation = this.prop.usage_guraduation;
      this.editForm_prop.usage_left = this.prop.usage_left;
      this.editForm_prop.usage_right = this.prop.usage_right;

      this.editForm_prop.prop_comments = JSON.parse(JSON.stringify(this.prop.prop_comments));
      const scenes = this.prop.scenes;
      this.sort_Standard(scenes);
      console.log(this.prop.scenes);
      this.editForm_prop.scenes = JSON.parse(JSON.stringify(this.prop.scenes));

      if(this.prop.usage_guraduation){
        this.guradutaion_tag = 1;
      }else{
        this.guradutaion_tag = 0;
      }

      if(this.editForm_prop.public_id){
        this.editForm_prop.photo = 1; // 写真が登録されている（可能性：1のまま、0に変更（この時public_idは存在する）、写真バイナリ代入（この時public_idは存在する））
      }else{
        this.editForm_prop.photo = 0; // 写真が登録されていない（可能性：0のまま（この時pubic_idは存在しない）、写真バイナリ代入（この時public_idは存在しない））
      }
      this.preview = null;
      // if(!this.editForm_prop.scenes.length){
      //   this.editForm_prop.scenes[0] = Object.assign({}, this.editForm_prop.scenes[0], {character_id: null, section: '' , page: '', usage: '', scene_comments: []})
      //   this.editForm_prop.scenes[0].scene_comments[0] = Object.assign({}, this.editForm_prop.scenes[0].scene_comments[0], {memo: null})
      // }else{
      //   this.editForm_prop.scenes.forEach((scene,index) => {
      //     this.editForm_prop.scenes[index] = Object.assign({}, this.editForm_prop.scenes[index], {section: this.editForm_prop.scenes[index].character.section.section})
      //     this.selected(index)
      //   })
      // }
      this.editPropMode_detail = "";
      this.editPropMode_memo = "";
    },

    sort_Standard(array){
      array.sort((a, b) => {
        // 最初のページで並び替え
        if(a.first_page !== b.first_page){
          return a.first_page - b.first_page
        }
        // 最後のページで並び替え
        if(a.final_page !== b.final_page){
          return this.page_order.indexOf(a.final_page) - this.page_order.indexOf(b.final_page);
        }
        // 登場人物idで並び替え
        if(a.character_id !== b.character_id){
          return a.character_id - b.character_id;
        }
        return 0;
      });

      this.prop.scenes = array;
      console.log(array);
    },

    // 持ち主を取得
    async fetchOwners () {
      const response = await axios.get('/api/informations/owners');
      
      if (response.status !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.optionOwners = response.data;
    },

    // 登場人物を取得
    async fetchCharacters () {
      const response = await axios.get('/api/informations/characters');

      if (response.statu !== 200) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.characters = response.data;

      // 区分と登場人物をオブジェクトに変換する
      let sections = new Object();
      this.characters.forEach(function(section){
        sections[section.section] = section.characters
      });
      this.optionCharacters = sections;
    },

    // 連動プルダウン
    selected(index) {
      this.selectedCharacters[index] = this.optionCharacters[this.editForm_prop.scenes[index].section];
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
      this.editForm_prop.kana = autokana.getFurigana();
    },
    
    // タブ切り替え
    alterTab() {
      if(this.tab === 1){
        this.tab = 2;
      }else{
        this.tab = 1;
      }

      // 調整
      this.$nextTick(() => {
        const content_dom = this.$refs.content_detail_prop;
        const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
        if(content_rect.top < 0){
          this.overlay_class = 0;
        }else{
          this.overlay_class = 1;
        }
      });
    },

    // 写真を見せない
    deletePhoto(){
      this.editForm_prop.photo = 0;
      // 調整
      this.$nextTick(() => {
        const content_dom = this.$refs.content_detail_prop;
        const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
        if(content_rect.top < 0){
          this.overlay_class = 0;
        }else{
          this.overlay_class = 1;
        }
      });
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

      this.editForm_prop.photo = event.target.files[0];

      // 調整
      this.$nextTick(() => {
        const content_dom = this.$refs.content_detail_prop;
        const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
        if(content_rect.top < 0){
          this.overlay_class = 0;
        }else{
          this.overlay_class = 1;
        }
      });
    },

    // 画像をクリアするメソッド
    resetPhoto () {
      this.preview = null;
      this.editForm_prop.photo = 0;
      this.$el.querySelector('input[type="file"]').value = null;
      // 調整
      this.$nextTick(() => {
        const content_dom = this.$refs.content_detail_prop;
        const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
        if(content_rect.top < 0){
          this.overlay_class = 0;
        }else{
          this.overlay_class = 1;
        }
      });
    },

    // 卒業公演の使用にチェックが付いたか
    selectGuraduation() {
      if(!this.guradutaion_tag){
        this.guradutaion_tag = 1;
      }else{
        this.guradutaion_tag = 0;
        this.editForm_prop.usage_left = 0;
        this.editForm_prop.usage_right = 0;
      }
    },

    // 諸々リセット
    resetProp() {
      this.editForm_prop.id = null;
      this.editForm_prop.name = null;
      this.editForm_prop.kana = null;
      this.editForm_prop.owner.name = '';
      this.editForm_prop.owner_id = '';
      this.editForm_prop.quantity = 1;
      this.editForm_prop.location = 0;
      this.editForm_prop.handmade = 0;
      this.editForm_prop.handmade_complete = 1;
      this.editForm_prop.decision = 0;
      this.editForm_prop.url = '';
      this.editForm_prop.public_id = '';
      this.editForm_prop.usage = 0;
      this.editForm_prop.usage_guraduation = 0;
      this.editForm_prop.usage_left = 0;
      this.editForm_prop.usage_right = 0;
      this.editForm_prop.photo = 0;
      this.editForm_prop.prop_comments = [];
      this.editForm_prop.scenes = [];
      // 卒業公演
      this.guradutaion_tag = 0;

      this.preview = null;
      this.editForm_prop.photo = 0;
      this.$el.querySelector('input[type="file"]').value = null;

      if(this.val){
        // 調整
        this.$nextTick(() => {
          const content_dom = this.$refs.content_detail_prop;
          const content_rect = content_dom.getBoundingClientRect(); // 要素の座標と幅と高さを取得
          if(content_rect.top < 0){
            this.overlay_class = 0;
          }else{
            this.overlay_class = 1;
          }
        });
      }
    },

    // 全角→半角（数字）
    Zenkaku2hankaku_number(str) {
      return str.replace(/[０-９]/g, function(s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
      });

      let pattern_number = /^([0-9]\d*|0)$/; // 0~9の数字かどうか
      const chars = str.split('');
      let sets = '';
      chars.forEach((char, index) => {
        char.replace(/[０-９]/g, function(s) {
          const number = String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
          if(pattern_number.test(number)){
            sets = sets + number;
          }else{
            sets  = 0;
          }
        });
        if(index === chars.length-1){
          return sets;
        }
      });
    },

    // 全角→半角（アルファベット）
    Zenkaku2hankaku_alf(str) {
      return str.replace(/[ａ-ｚＡ-Ｚ]/g, function(s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
      });

      let pattern_alf = /^([A-Z]\d)$/; // 0~9の数字かどうか
      const chars = str.split('');
      let sets = '';
      chars.forEach((char, index) => {
        char.replace(/[ａ-ｚＡ-Ｚ]/g, function(s) {
          const number = String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
          if(pattern_number.test(number)){
            sets = sets + number;
          }else{
            sets  = 0;
          }
        });
        if(index === chars.length-1){
          return sets;
        }
      });
    },

    // 半角→全角（カタカナ）
    hunkaku2Zenkaku_str(str) {
      const kanaMap = {
        'ｶﾞ': 'ガ', 'ｷﾞ': 'ギ', 'ｸﾞ': 'グ', 'ｹﾞ': 'ゲ', 'ｺﾞ': 'ゴ',
        'ｻﾞ': 'ザ', 'ｼﾞ': 'ジ', 'ｽﾞ': 'ズ', 'ｾﾞ': 'ゼ', 'ｿﾞ': 'ゾ',
        'ﾀﾞ': 'ダ', 'ﾁﾞ': 'ヂ', 'ﾂﾞ': 'ヅ', 'ﾃﾞ': 'デ', 'ﾄﾞ': 'ド',
        'ﾊﾞ': 'バ', 'ﾋﾞ': 'ビ', 'ﾌﾞ': 'ブ', 'ﾍﾞ': 'ベ', 'ﾎﾞ': 'ボ',
        'ﾊﾟ': 'パ', 'ﾋﾟ': 'ピ', 'ﾌﾟ': 'プ', 'ﾍﾟ': 'ペ', 'ﾎﾟ': 'ポ',
        'ｳﾞ': 'ヴ', 'ﾜﾞ': 'ヷ', 'ｦﾞ': 'ヺ',
        'ｱ': 'ア', 'ｲ': 'イ', 'ｳ': 'ウ', 'ｴ': 'エ', 'ｵ': 'オ',
        'ｶ': 'カ', 'ｷ': 'キ', 'ｸ': 'ク', 'ｹ': 'ケ', 'ｺ': 'コ',
        'ｻ': 'サ', 'ｼ': 'シ', 'ｽ': 'ス', 'ｾ': 'セ', 'ｿ': 'ソ',
        'ﾀ': 'タ', 'ﾁ': 'チ', 'ﾂ': 'ツ', 'ﾃ': 'テ', 'ﾄ': 'ト',
        'ﾅ': 'ナ', 'ﾆ': 'ニ', 'ﾇ': 'ヌ', 'ﾈ': 'ネ', 'ﾉ': 'ノ',
        'ﾊ': 'ハ', 'ﾋ': 'ヒ', 'ﾌ': 'フ', 'ﾍ': 'ヘ', 'ﾎ': 'ホ',
        'ﾏ': 'マ', 'ﾐ': 'ミ', 'ﾑ': 'ム', 'ﾒ': 'メ', 'ﾓ': 'モ',
        'ﾔ': 'ヤ', 'ﾕ': 'ユ', 'ﾖ': 'ヨ',
        'ﾗ': 'ラ', 'ﾘ': 'リ', 'ﾙ': 'ル', 'ﾚ': 'レ', 'ﾛ': 'ロ',
        'ﾜ': 'ワ', 'ｦ': 'ヲ', 'ﾝ': 'ン',
        'ｧ': 'ァ', 'ｨ': 'ィ', 'ｩ': 'ゥ', 'ｪ': 'ェ', 'ｫ': 'ォ',
        'ｯ': 'ッ', 'ｬ': 'ャ', 'ｭ': 'ュ', 'ｮ': 'ョ',
        '｡': '。', '､': '、', 'ｰ': 'ー', '｢': '「', '｣': '」', '･': '・'
      };
      let reg = new RegExp('(' + Object.keys(kanaMap).join('|') + ')', 'g');
      return str.replace(reg, function(s){
        return kanaMap[s];
      }).replace(/ﾞ/g, '゛').replace(/ﾟ/g, '゜');
    },

    /** 文字列内のカタカナをひらがなに変換します。 */
    kata2Hira(str) {
      return str.replace(/[\u30A1-\u30FA]/g, ch =>
       String.fromCharCode(ch.charCodeAt(0) - 0x60)
      );
    },


    // 編集エラー
    confirmProp () {
      const regex_str = /[^ぁ-んー]/g; // ひらがな以外
      const regex_number = /[^0-9]/g; // 数字以外
      const regex_alf = /[^A-Z]/g; // アルファベット
      let kana = '';
      let kanas = [...this.editForm_prop.kana];
      let pattern_number = /^([0-9]\d*|0)$/; // 0~9の数字かどうか
      let pattern_alf = /^([A-Z]\d*)$/; // A~Zのアルファベットかどうか*いる
      let names = [...this.editForm_prop.name];
      let name_last = names[names.length-1];

      // kan正規表現
      if(this.first_uni <= name_last.charCodeAt(0) && name_last.charCodeAt(0) <= this.final_uni){
        // 囲み文字の処理
        const name_last_point_diff = name_last.charCodeAt(0)-this.first_uni + 1;
        name_last = name_last_point_diff;
      }else{
        // 囲み文字じゃなかった
        name_last = this.Zenkaku2hankaku_number(name_last);
        if(pattern_number.test(name_last)){
          // 数字だった
          for(let i = 2; i<names.length+1; i++){
            // 遡る
            let name_candidate = this.Zenkaku2hankaku_number(names[names.length-i]);
            if(pattern_number.test(name_candidate)){
              name_last = String(name_candidate) + String(name_last);
              name_last = Number(name_last);
            }else{
              break;
            }
          }
        }else{
          // 数字じゃなかった=文字だった
          name_last = this.Zenkaku2hankaku_alf(name_last);
          if(pattern_alf.test(name_last.toUpperCase())){
            // アルファベットだった
            name_last = name_last.toUpperCase();
            for(let i = 2; i<names.length+1; i++){
              // 遡る
              let name_candidate = this.Zenkaku2hankaku_alf(names[names.length-i]);
              if(pattern_alf.test(name_candidate)){
                name_last = name_candidate.toUpperCase() + name_last;
              }else{
                break;
              }
            }
          }else{
            // アルファベットじゃなかった=ひらがなかカタカナだった
            name_last = '';
          }
        }
      }

      kanas.forEach(a => {
        // 一文字ずつになっている
        const number = this.Zenkaku2hankaku_number(a);
        if(pattern_number.test(number)){
          // 数字だった
          kana = kana + number;
        }else{
          // 数字じゃなかった=文字だった
          const alf = this.Zenkaku2hankaku_alf(number);
          if(pattern_alf.test(alf.toUpperCase())){
            // アルファベットだった
            kana = kana + alf.toUpperCase();
          }else{
            // アルファベットじゃなかった=ひらがなかカタカナだった
            const str = this.hunkaku2Zenkaku_str(alf);
            kana = kana + this.kata2Hira(str);
          }
        }
      });
      if(name_last){
        if(kana.slice( eval('-'+String(name_last).length))!== String(name_last) ){
          // 最後のマークが名前と一致しない場合追加する
          kana = kana + String(name_last);
        }
      }
      this.editForm_prop.kana = kana;

      if(this.editForm_prop.quantity){
        let quantitys = [...this.editForm_prop.quantity];
        let correct_quantity = '';
        quantitys.forEach((quantity) => {
          let number = this.Zenkaku2hankaku_number(quantity);
          correct_quantity = String(correct_quantity) + String(number);
          correct_quantity = Number(correct_quantity);
        }, this);
        this.editForm_prop.quantity = correct_quantity;
      }else{
        this.editForm_prop.quantity = 1;
      }
      
      if(!this.editForm_prop.handmade){
        this.editForm_prop.handmade = 0;
      }else{
        this.editForm_prop.handmade = this.editForm_prop.handmade_complete;
      }

      if(this.prop.id === this.editForm_prop.id && (this.prop.name !== this.editForm_prop.name || this.prop.kana !== this.editForm_prop.kana || ((this.prop.owner_id !== this.editForm_prop.owner_id) || (!this.prop.owner_id && !this.editForm_prop.owner_id)) || this.prop.quantity !== this.editForm_prop.quantity || this.prop.location !== this.editForm_prop.location || this.prop.handmade !== this.editForm_prop.handmade || this.prop.decision !== this.editForm_prop.decision  || this.prop.usage !== this.editForm_prop.usage || this.prop.usage_guraduation !== this.editForm_prop.usage_guraduation || this.prop.usage_left !== this.editForm_prop.usage_left || this.prop.usage_right !== this.editForm_prop.usage_right) && ((this.prop.public_id && this.editForm_prop.photo === 1) || (!this.prop.public_id && !this.editForm_prop.photo))){
        // 怪しい
        // if(!this.prop.owner_id && !this.editForm_prop.owner_id){
        //   console.log('なんで');
        //   this.editPropMode_detail = 0;
        // }else{
          // 写真をアップデートしない
          this.editPropMode_detail = 1; // 'photo_non_update'
        // }        
      }else if(this.prop.id === this.editForm_prop.id && !this.prop.public_id && this.editForm_prop.photo && this.editForm_prop.photo !== 1){
        // 写真新規
        this.editPropMode_detail = 2; // 'photo_store'
      }else if(this.prop.id === this.editForm_prop.id && this.prop.public_id && !this.editForm_prop.photo){
        // 写真削除
        this.editPropMode_detail = 3; //'photo_delete'
      }else if(this.prop.id === this.editForm_prop.id && this.prop.public_id && this.editForm_prop.photo && this.editForm_prop.photo !== 1){
        // 写真アップデート
        this.editPropMode_detail = 4; //'photo_update'
      }else{
        this.editPropMode_detail = 0;
      }

      if(this.prop.id === this.editForm_prop.id && !this.prop.prop_comments.length && this.editForm_prop.memo){
        // メモ新規投稿
        this.editPropMode_memo = 1; // 'memo_store'
      }else if(this.prop.id === this.editForm_prop.id && this.prop.prop_comments.length){
        if(this.prop.id === this.editForm_prop.id && this.prop.prop_comments[0].id && !this.editForm_prop.prop_comments[0].memo){
          // メモ削除
          this.editPropMode_memo = 2; //'memo_delete'
        }else if(this.prop.id === this.editForm_prop.id && this.prop.prop_comments[0].id && this.prop.prop_comments[0].memo !== this.editForm_prop.prop_comments[0].memo){
          // メモアップデート
          this.editPropMode_memo = 3; // 'memo_update'
        }else{
          this.editPropMode_memo = 0;
        }
      }else{
        this.editPropMode_memo = 0;
      }
    },

    // 編集confirmのモーダル表示 
    openModal_confirmEdit () {
      this.showContent_confirmEdit = true;

      this.optionOwners.forEach((owner) => {
        if(owner.id === this.editForm_prop.owner_id){
          this.editForm_prop.owner.name = owner.name;
        }
      }, this);

      let location = '持ってきてない';
      let handmade = '作らない';
      let decision = 'してない';
      let usage = '';
      let usage_guraduation = '';
      let usage_left = '';
      let usage_right = '';

      if(this.editForm_prop.location) {
        location = '持ってきてる';
      }

      if(this.editForm_prop.handmade == 1){
        handmade = '作る: 完成';
      }else if(this.editForm_prop.handmade == 2){
        handmade = '作る: 仕掛中';
      }else if(this.editForm_prop.handmade == 3){
        handmade = '作る: 未着手';
      }

      if(this.editForm_prop.decision) {
        decision = 'してる';
      }

      if(this.editForm_prop.usage) { 
        usage = 'Ⓟ ';
      }
      if(this.editForm_prop.usage_guraduation) { 
        usage_guraduation = 'Ⓖ ';
      }
      if(this.editForm_prop.usage_left) {
        usage_left = '㊤ ';
      }
      if(this.editForm_prop.usage_right){
        usage_right = '㊦';
      }

      let memos = [];
      this.editForm_prop.prop_comments.forEach((memo, index) => {
        if(memo.memo && index !== this.editForm_prop.prop_comments.length - 1){
          memos.push(memo.memo+'\n　　　');
        }else if(memo.memo){
          memos.push(memo.memo);
        }
      }, this);

      // 写真は出ない
      let photo = '変更する';
      if(this.editPropMode_detail === 1 || this.editPropMode_detail === 0){
        photo = '変更しない';
      }

      this.postMessage_Edit = '以下のように編集します。\n小道具名：'+this.editForm_prop.name + '\nふりがな：'+this.editForm_prop.kana + '\n持ち主：'+this.editForm_prop.owner.name + '\n個数：'+this.editForm_prop.quantity +'\nピッコロに：'+location + '\n'+handmade + '\n決定：'+decision + '\n使用状況：'+usage+usage_guraduation+usage_left+usage_right + '\nメモ：'+memos + '\n写真：'+photo;
    },
    // 編集confirmのモーダル非表示_OKの場合
    async closeModal_confirmEdit_OK() {
      this.showContent_confirmEdit = false;
      if(this.editPropMode_detail){
        await this.editProp();
      }
      if(this.editPropMode_memo){
        await this.editProp_memo();
      }
    },
    // 編集confirmのモーダル非表示_Cancelの場合
    closeModal_confirmEdit_Cancel() {
      this.showContent_confirmEdit= false;
      this.editForm_prop.owner.name = "";
      this.editPropMode_detail = "";
      this.editPropMode_memo = "";
      if(this.editForm_prop.handmade){
        this.editForm_prop.handmade = true;
      }
    },

    // 基本情報を編集する
    async editProp () {
      if(this.editPropMode_detail === 1){
        // 写真は変更しない
        const response = await axios.post('/api/props/'+ this.prop.id, {
          method: 'photo_non_update',
          name: this.editForm_prop.name,
          kana: this.editForm_prop.kana,
          owner_id: this.editForm_prop.owner_id,
          quantity: this.editForm_prop.quantity,
          location: this.editForm_prop.location,
          handmade: this.editForm_prop.handmade,
          decision: this.editForm_prop.decision,
          usage: this.editForm_prop.usage,
          usage_guraduation: this.editForm_prop.usage_guraduation,
          usage_left: this.editForm_prop.usage_left,
          usage_right: this.editForm_prop.usage_right
        });

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }
        
        this.editPropMode_detail = 100;
        if(this.editPropMode_memo === 0){
          this.editPropMode_memo = 100;
        }

      }else if(this.editPropMode_detail === 2){
        // 写真新規投稿
        const formData = new FormData();
        formData.append('method', 'photo_store');
        formData.append('name', this.editForm_prop.name);
        formData.append('kana', this.editForm_prop.kana);
        formData.append('owner_id', this.editForm_prop.owner_id);
        formData.append('quantity', this.editForm_prop.quantity);
        formData.append('location', this.editForm_prop.location);
        formData.append('handmade', this.editForm_prop.handmade);
        formData.append('decision', this.editForm_prop.decision);
        formData.append('usage', this.editForm_prop.usage);
        formData.append('usage_guraduation', this.editForm_prop.usage_guraduation);
        formData.append('usage_left', this.editForm_prop.usage_left);
        formData.append('usage_right', this.editForm_prop.usage_right);
        formData.append('photo', this.editForm_prop.photo);
        const response = await axios.post('/api/props/'+ this.prop.id, formData);

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.editPropMode_detail = 100;
        if(this.editPropMode_memo === 0){
          this.editPropMode_memo = 100;
        }

      }else if(this.editPropMode_detail === 3){
        // 写真削除
        const response = await axios.post('/api/props/'+ this.prop.id, {
          method: 'photo_delete',
          name: this.editForm_prop.name,
          kana: this.editForm_prop.kana,
          owner_id: this.editForm_prop.owner_id,
          quantity: this.editForm_prop.quantity,
          location: this.editForm_prop.location,
          handmade: this.editForm_prop.handmade,
          decision: this.editForm_prop.decision,
          public_id: this.editForm_prop.public_id,
          usage: this.editForm_prop.usage,
          usage_guraduation: this.editForm_prop.usage_guraduation,
          usage_left: this.editForm_prop.usage_left,
          usage_right: this.editForm_prop.usage_right
        });

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.editPropMode_detail = 100;
        if(this.editPropMode_memo === 0){
          this.editPropMode_memo = 100;
        }

      }if(this.editPropMode_detail === 4){
        // 写真アップデート
        const formData = new FormData();
        formData.append('method', 'photo_update');
        formData.append('name', this.editForm_prop.name);
        formData.append('kana', this.editForm_prop.kana);
        formData.append('owner_id', this.editForm_prop.owner_id);
        formData.append('quantity', this.editForm_prop.quantity);
        formData.append('location', this.editForm_prop.location);
        formData.append('handmade', this.editForm_prop.handmade);
        formData.append('decision', this.editForm_prop.decision);
        formData.append('public_id', this.editForm_prop.public_id);
        formData.append('usage', this.editForm_prop.usage);
        formData.append('usage_guraduation', this.editForm_prop.usage_guraduation);
        formData.append('usage_left', this.editForm_prop.usage_left);
        formData.append('usage_right', this.editForm_prop.usage_right);
        formData.append('photo', this.editForm_prop.photo);
        const response = await axios.post('/api/props/'+ this.prop.id, formData);

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.editPropMode_detail = 100;
        if(this.editPropMode_memo === 0){
          this.editPropMode_memo = 100;
        }
      }
    },
    // メモを更新する
    async editProp_memo() {
      if(this.editPropMode_memo === 1){
        // メモ新規投稿
        const response = await axios.post('/api/prop_comments', {
          prop_id: this.editForm_prop.id,
          memo: this.editForm_prop.memo
        });

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 201) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.editPropMode_memo = 100;

      }else if(this.editPropMode_memo === 2){
        // メモ削除
        const response = await axios.delete('/api/prop_comments/'+ this.prop.prop_comments[0].id);

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.editPropMode_memo = 100;

      }else if(this.editPropMode_memo === 3){
        // メモアップデート
        const response = await axios.post('/api/prop_comments/'+ this.prop.prop_comments[0].id, {
          memo: this.editForm_prop.prop_comments[0].memo
        });

        if (response.status === 422) {
          this.errors.error = response.data.errors;
          return false;
        }

        if (response.status !== 204) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.editPropMode_memo = 100;

      }
    },

    // 削除confirmのモーダル表示 
    openModal_confirmDelete (id) {
      this.showContent_confirmDelete = true;
      this.postMessage_Delete = 'この小道具を削除すると、紐づけられてたこの小道具を使用するシーンも全て削除されます。\n本当に削除しますか？';
    },
    // 削除confirmのモーダル非表示_OKの場合
    async closeModal_confirmDelete_OK() {
      this.showContent_confirmDelete = false;
      this.$emit('close');
      await this.deletProp();
    },
    // 削除confirmのモーダル非表示_Cancelの場合
    closeModal_confirmDelete_Cancel() {
      this.showContent_confirmDelete = false;
    },

    // 削除する
    async deletProp() {
      const response = await axios.delete('/api/props/'+ this.prop.id);

      if (response.status === 422) {
        this.errors.error = response.data.errors;
        return false;
      }

      if (response.status !== 204) {
        this.$store.commit('error/setCode', response.status);
        return false;
      }

      this.prop = [];
      this.resetProp();

      // メッセージ登録
      this.$store.commit('message/setContent', {
        content: '小道具が1つ削除されました！',
        timeout: 6000
      });

      this.$emit('close');
    }
  }
}
</script>