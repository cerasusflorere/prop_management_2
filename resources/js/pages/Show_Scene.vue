<template>
  <div>
    <!-- ボタンエリア -->
    <div class="button-area">
      <!-- 表示切替ボタン -->

      <div v-if="scenes.length" class="button-area--small">
        <div class="button-area--together-left">
          <!-- 検索 -->
          <div class="button-area--small-small">
            <button type="button" @click="openModal_searchScene(Math.random())" class="button button--inverse button--small"><i class="fas fa-search fa-fw"></i>検索</button>
          </div>
          <searchScene :postSearch="postSearch" v-show="showContent_search" @close="closeModal_searchScene" />

          <!-- 選択 -->
          <div class="button-area--small-small">
            <button type="button" @click="showCheckBox" class="button button--inverse button--small button--choice"><i class="fas fa-check-square fa-fw"></i>選択</button>
          </div>
          
          <!-- 選択編集 -->
          <div v-if="choice_flag" class="button-area--small-small">
            <button type="button" @click="openModal_customEdit" class="button button--inverse button--small button--choice"><i class="fas fa-edit fa-fw"></i>選択編集</button>
          </div>
          <customDialog_Edit :custom_dialog_edit_message="postMessage_CustomEdit" v-show="showContent_customEdit" @Cancel_CustomEdit="closeModal_customEdit_Cancel" @OK_CustomEdit="closeModal_customEdit_OK"/>
          <confirmDialog_Edit :confirm_dialog_edit_message="postMessage_Edit" v-show="showContent_confirmEdit" @Cancel_Edit="closeModal_confirmEdit_Cancel" @OK_Edit="closeModal_confirmEdit_OK"/>

          <!-- 選択削除実行 -->
          <div v-if="choice_flag" class="button-area--small-small">
            <button type="button" @click="openModal_confirmDelete" class="button button--inverse button--small button--choice"><i class="fas fa-trash-alt fa-fw"></i>選択削除</button>
          </div>
          <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
        </div>

          <!-- ダウンロードボタン -->
          <!-- リスト表示かつPCかつデータがある時 -->
          <div v-if="!sizeScreen && showScenes.length" class="button-area--small-small">
            <button type="button" @click="downloadScenes" class="button button--inverse button--small"><i class="fas fa-download fa-fw"></i>ダウンロード</button>
          </div>
        </div>      
      </div>

    <div v-if="!sizeScreen && showScenes.length" class="PC">
      <table>
        <thead>
          <tr>
            <th v-if="choice_flag" class="th-non">
                <input type="checkbox" class="checkbox-delete" @click="choiceDeleteAllScenes"></input>
              </th>
            <th class="th-non"></th>
            <th>ページ数</th>
            <th>登場人物</th>
            <th>小道具名</th>
            <th>決定か</th>
            <th>中間発表</th>
            <th>卒業公演</th>
            <th>上手</th>
            <th>下手</th>
            <th>セットする人</th>        
            <th class="th-memo">メモ</th>
            <th>登録日時</th>
            <th>更新日時</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(scene, index) in showScenes">
            <td v-if="choice_flag">
                <input type="checkbox" class="checkbox-delete" v-model="choice_ids[scene.id]"></input>
              </td>
              <!-- index -->
              <td type="button" class="list-button td-color" @click="openModal_sceneDetail(scene.id)">{{ index + 1 }}</td>
              <!-- 何ページから -->
              <td v-if="scene.first_page && scene.final_page != 1000">p.{{ scene.first_page }}<span v-if="scene.final_page"> ~ p.{{ scene.final_page }}</span></td>
              <td v-if="scene.first_page == 1 && scene.final_page == 1000">全シーン</td>
              <td v-if="!scene.first_page"></td>
              <!-- 登場人物 -->
              <td>{{ scene.character.name }}</td>
              <!-- 小道具名 -->
              <td type="button" class="list-button" @click="openModal_propDetail(scene.prop.id)">{{ scene.prop.name }}</td>
              <!-- これで決定か -->
              <td v-if="scene.decision"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td> 
              <!-- 中間発表 -->
              <td v-if="scene.usage"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td> 
              <!-- 卒業公演 -->
              <td v-if="scene.usage_guraduation"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- 上手 -->
              <td v-if="scene.usage_left"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- 下手 -->
              <td v-if="scene.usage_right"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- セットする人 -->
              <td v-if="scene.setting">{{ scene.setting.name }}</td>
              <td v-else></td>
              <!-- メモ -->
              <td v-if="scene.scene_comments.length">
                <div v-for="memo in scene.scene_comments"> {{ memo.memo }}</div>
              </td>
              <td v-else></td>
              <!-- 登録日時 -->
              <td>{{ scene.created_at }}</td>
              <!-- 更新日時 -->
              <td>{{ scene.updated_at }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="!showScenes.length">
        使用シーンは登録されていません。
      </div>
    </div>

    <div v-else class="phone">
        <div v-if="showScenes.length">
          <table>
            <div v-for="(scene, index) in showScenes">
              <tr v-show="index === 0" v-if="choice_flag">
                <th class="th-non">
                  <input type="checkbox" class="checkbox-delete" @click="choiceDeleteAllScenes"></input>
                </th>
                <td></td>
              </tr>
              <tr>
                <!-- index -->
                <th class="th-non">
                  <input type="checkbox" v-if="choice_flag" class="checkbox-delete" v-model="choice_ids[scene.id]"></input>
                </th>
                <td type="button" class="list-button td-color" @click="openModal_sceneDetail(scene.id)">{{ index + 1 }}</td>
              </tr>
              <tr>
                <!-- ページ数 -->
                <th>ページ数</th>
                <td v-if="scene.first_page && scene.final_page != 1000">p.{{ scene.first_page }}<span v-if="scene.final_page"> ~ p.{{ scene.final_page }}</span></td>
                <td v-if="scene.first_page == 1 && scene.final_page == 1000">全シーン</td>
                <td v-if="!scene.first_page"></td>
              </tr>
              <tr>
                <!-- 登場人物 -->
                <th>登場人物</th>
                <td>{{ scene.character.name }}</td>
              </tr>
              <tr>
                <!-- 小道具 -->
                <th>小道具</th>
                <td type="button" class="list-button" @click="openModal_propDetail(scene.prop.id)">{{ scene.prop.name }}</td>
              </tr>
              <tr>
                <!-- これで決定か -->
                <th>決定か</th>
                <td v-if="scene.decision"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 中間発表 -->
                <th>中間</th>
                <td v-if="scene.usage"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 卒業公演 -->
                <th>卒業</th>
                <td v-if="scene.usage_guraduation"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 上手 -->
                <th>上手</th>
                <td v-if="scene.usage_left"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 下手 -->
                <th>下手</th>
                <td v-if="scene.usage_right"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 誰がセットするか-->
                <th>セットする人</th>
                <td v-if="scene.setting">{{ scene.setting.name }}</td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- メモ -->
                <th>メモ</th>
                <td v-if="scene.scene_comments.length">
                  <div v-for="memo in scene.scene_comments"> {{ memo.memo }}</div>
                </td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 登録日時 -->
                <th>登録日時</th>
                <td>{{ scene.created_at }}</td>
              </tr>
              <tr>
                <!-- 更新日時 -->
                <th>更新日時</th>
                <td>{{ scene.updated_at }}</td>
              </tr>
            </div>
          </table>
        </div>

        <div v-else>
          使用シーンは登録されていません。 
        </div>
      </div>

    
   
    <detailScene :postScene="postScene" v-show="showContent" @close="closeModal_sceneDetail" />
    <detailProp :postProp="postProp" v-show="showContent_prop" @close="closeModal_propDetail" /> 
  </div>
</template>

<script>
  import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util';

  import detailScene from '../components/Detail_Scene.vue';
  import detailProp from '../components/Detail_Prop.vue';
  import searchScene from '../components/Search_Scene.vue';
  import customDialog_Edit from '../components/Custom_Dialog_Edit.vue';
  import confirmDialog_Edit from '../components/Confirm_Dialog_Edit.vue';
  import confirmDialog_Delete from '../components/Confirm_Dialog_Delete.vue';
  import ExcelJS from 'exceljs';

  export default {
    // このページの上で表示するコンポーネント
    components: {
      detailScene,
      detailProp,
      searchScene,
      customDialog_Edit,
      confirmDialog_Edit,
      confirmDialog_Delete
    },
    data() {
      return{
        // 画面サイズ取得
        sizeScreen: 1, // 0:パソコン, 1:　スマホ
        // 取得するデータ
        scenes: [],
        // 表示するデータ
        showScenes: [],
        // シーン詳細
        showContent: false,
        postScene: "",
        // 小道具詳細
        showContent_prop: false,
        postProp: "",
        custom_sort: null,
        custom_name: null,
        custom_refine: null,
        // シーン検索カスタム
        showContent_search: false,
        postSearch: "",
        // ページの並び順
        page_order: [], 
        // 選択ボタン
        choice_flag: false,
        // 選択
        choice_ids: [],
        choice_many: 0,
        // 編集custom
        showContent_customEdit: false,
        postMessage_CustomEdit: "",
        edit_custom: null,
        yes_no: null,
        // 編集confirm
        showContent_confirmEdit: false,
        postMessage_Edit: "",
        // 削除confirm
        showContent_confirmDelete: false,
        postMessage_Delete: ""
      }
    },
    watch: {
      $route: {
        async handler () {
          await this.fetchScenes();
          if (window.matchMedia('(max-width: 989px)').matches) {
            //スマホ処理
            this.sizeScreen = 1;
          } else {
            //PC処理
            this.sizeScreen = 0;
          }
        },
        immediate: true
      }
    },
    methods: {
      // 使用シーン一覧を取得
      async fetchScenes () {
        const response = await axios.get('/api/scenes');
  
        if (response.status !== 200) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }
        
        this.scenes = response.data; // オリジナルデータ
        this.showScenes = JSON.parse(JSON.stringify(this.scenes));

        this.page_order[0] = 1000;
        for(let i=1; i < 100; i++ ){
          this.page_order[i] = i;
        }
        
        this.scenes.forEach((scene) => {
          this.choice_ids.push(false);
        }, this);

        if(this.custom_sort || this.custom_name || this.custom_refine){
          await this.closeModal_searchScene(this.custom_sort, this.custom_name, this.custom_refine);
        }else{
          this.sort_Standard(this.showScenes);
        }        
      },

      sort_Standard(array){
        const regex_str = /[^ぁ-んー]/g; // ひらがな以外
        const regex_number = /[^0-9]/g; // 数字以外
        const regex_alf = /[^A-Z]/g; // アルファベット
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
          // kanaで並び替え
          if(a.prop.kana !== b.prop.kana){
            const a_str = a.prop.kana.replace(regex_str, "");
            const b_str = b.prop.kana.replace(regex_str, "");
            let a_number = a.prop.kana.replace(regex_number, "");
            let b_number = b.prop.kana.replace(regex_number, "");
            const a_alf = a.prop.kana.replace(regex_alf, "");
            const b_alf = b.prop.kana.replace(regex_alf, "");

            if(a_str !== b_str){
              let sort_str = a_str;
              if([...b_str].length < [...a_str].length){
                sort_str = b_str;
              } 
              for(let i=0; i < [...sort_str].length; i++){
                if(a_str.codePointAt(i) !== b_str.codePointAt(i)){
                  if(a_str.codePointAt(i) > b_str.codePointAt(i)){
                    return 1;
                  }else if(a_str.codePointAt(i) < b_str.codePointAt(i)){
                    return -1;
                  }
                }          
              }
            }

            if(a_number !== b_number){
              if(!a_number){
                a_number = 0;
              }
              if(!b_number){
                b_number = 0;
              }

              if(parseInt(a_number) > parseInt(b_number)){
                return 1;
              }else if(parseInt(a_number) < parseInt(b_number)){
                return -1;
              }
            }

            if(a_alf !== b_alf){
              if(a_alf.codePointAt(0) > b_alf.codePointAt(0)){
                return 1;
              }else if(a_alf.codePointAt(0) < b_alf.codePointAt(0)){
                return -1;
              }else{
                return 0;
              }
            }
          }
          return 0;
        });

        this.showScenes = array;
      },
      
      // エスケープ処理
      h(unsafeText){
        if(typeof unsafeText !== 'string'){
            return unsafeText;
        }
        return unsafeText.replace(
          /[&'`"<>]/g, 
          function(match) {
            return {
              '&': '&amp;',
              "'": '&#x27;',
              '`': '&#x60;',
              '"': '&quot;',
              '<': '&lt;',
              '>': '&gt;',
            }[match]
          } 
        );
      },

      // 検索カスタムのモーダル表示 
      openModal_searchScene (number) {
        this.showContent_search = true;
        this.postSearch = number;
      },
      // 検索カスタムのモーダル非表示
      closeModal_searchScene(sort, name, refine) {
        this.showContent_search = false;
        if(sort !== null && refine !== null){

          this.custom_sort = sort;
          this.custom_name = name;
          this.custom_refine = refine;
          let array_original = this.scenes.filter((a) => eval(refine));
          let array = [];

          if(this.h(name.input)){
            if(name.scope === "memo_together"){
              // メモを含めて検索
              array = array_original.filter((a) => {
                if(a.prop.name.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }else if(a.prop.kana.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }else if(a.prop.prop_comments[0]){
                  if(a.prop.prop_comments[0].memo.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1){
                    return a;
                  }                  
                }
              });
            }else{
              // 小道具名のみで検索
              array = array_original.filter((a) => {
                if(a.prop.name.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }else if(a.prop.kana.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }
              });
            }
          }else{
            // 入力検索しない
            array = array_original;
          }

          const regex_str = /[^ぁ-んー]/g; // ひらがな以外
          const regex_number = /[^0-9]/g; // 数字以外
          const regex_alf = /[^A-Z]/g; // アルファベット
          
          if(sort === "character"){
            array.sort((a, b) => {
              // 登場人物idで並び替え
              if(a.character_id !== b.character_id){
                return a.character_id - b.character_id;
              }
              // 最初のページで並び替え
              if(a.first_page !== b.first_page){
                return a.first_page - b.first_page
              }
              // 最後のページで並び替え
              if(a.final_page !== b.final_page){
                return this.page_order.indexOf(a.final_page) - this.page_order.indexOf(b.final_page);
              }

              // kanaで並び替え
              if(a.prop.kana !== b.prop.kana){
                const a_str = a.prop.kana.replace(regex_str, "");
                const b_str = b.prop.kana.replace(regex_str, "");
                let a_number = a.prop.kana.replace(regex_number, "");
                let b_number = b.prop.kana.replace(regex_number, "");
                const a_alf = a.prop.kana.replace(regex_alf, "");
                const b_alf = b.prop.kana.replace(regex_alf, "");

                if(a_str !== b_str){
                  let sort_str = a_str;
                  if([...b_str].length < [...a_str].length){
                    sort_str = b_str;
                  } 
                  for(let i=0; i < [...sort_str].length; i++){
                    if(a_str.codePointAt(i) !== b_str.codePointAt(i)){
                      if(a_str.codePointAt(i) > b_str.codePointAt(i)){
                        return 1;
                      }else if(a_str.codePointAt(i) < b_str.codePointAt(i)){
                        return -1;
                      }
                    }          
                  }
                }

                if(a_number !== b_number){
                  if(!a_number){
                    a_number = 0;
                  }
                  if(!b_number){
                    b_number = 0;
                  }

                  if(parseInt(a_number) > parseInt(b_number)){
                    return 1;
                  }else if(parseInt(a_number) < parseInt(b_number)){
                    return -1;
                  }
                }

                if(a_alf !== b_alf){
                  if(a_alf.codePointAt(0) > b_alf.codePointAt(0)){
                    return 1;
                  }else if(a_alf.codePointAt(0) < b_alf.codePointAt(0)){
                    return -1;
                  }else{
                    return 0;
                  }
                }
              }

              return 0;
            });

            this.showScenes = array;
          }else if(sort === "prop"){
            array.sort((a, b) => {
              // kanaで並び替え
              if(a.prop.kana !== b.prop.kana){
                const a_str = a.prop.kana.replace(regex_str, "");
                const b_str = b.prop.kana.replace(regex_str, "");
                let a_number = a.prop.kana.replace(regex_number, "");
                let b_number = b.prop.kana.replace(regex_number, "");
                const a_alf = a.prop.kana.replace(regex_alf, "");
                const b_alf = b.prop.kana.replace(regex_alf, "");

                if(a_str !== b_str){
                  let sort_str = a_str;
                  if([...b_str].length < [...a_str].length){
                    sort_str = b_str;
                  } 
                  for(let i=0; i < [...sort_str].length; i++){
                    if(a_str.codePointAt(i) !== b_str.codePointAt(i)){
                      if(a_str.codePointAt(i) > b_str.codePointAt(i)){
                        return 1;
                      }else if(a_str.codePointAt(i) < b_str.codePointAt(i)){
                        return -1;
                      }
                    }          
                  }
                }

                if(a_number !== b_number){
                  if(!a_number){
                    a_number = 0;
                  }
                  if(!b_number){
                    b_number = 0;
                  }

                  if(parseInt(a_number) > parseInt(b_number)){
                    return 1;
                  }else if(parseInt(a_number) < parseInt(b_number)){
                    return -1;
                  }
                }

                if(a_alf !== b_alf){
                  if(a_alf.codePointAt(0) > b_alf.codePointAt(0)){
                    return 1;
                  }else if(a_alf.codePointAt(0) < b_alf.codePointAt(0)){
                    return -1;
                  }else{
                    return 0;
                  }
                }
              }

              // 登場人物idで並び替え
              if(a.character_id !== b.character_id){
                return a.character_id - b.character_id;
              }
              // 最初のページで並び替え
              if(a.first_page !== b.first_page){
                return a.first_page - b.first_page
              }
              // 最後のページで並び替え
              if(a.final_page !== b.final_page){
                return this.page_order.indexOf(a.final_page) - this.page_order.indexOf(b.final_page);
              }

              return 0;
            });

            this.showScenes = array;

          }else if(sort === "created_at"){
            array.sort((a, b) => {
              if(a.created_at !== b.created_at){
                // 一致不一致はnew Dateせずに
                return new Date(a.created_at) - new Date(b.created_at);
              }

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

              // kanaで並び替え
              if(a.prop.kana !== b.prop.kana){
                console.log(a.prop.kana);
                console.log(b.prop.kana);
                const a_str = a.prop.kana.replace(regex_str, "");
                const b_str = b.prop.kana.replace(regex_str, "");
                let a_number = a.prop.kana.replace(regex_number, "");
                let b_number = b.prop.kana.replace(regex_number, "");
                const a_alf = a.prop.kana.replace(regex_alf, "");
                const b_alf = b.prop.kana.replace(regex_alf, "");

                if(a_str !== b_str){
                  let sort_str = a_str;
                  if([...b_str].length < [...a_str].length){
                    sort_str = b_str;
                  } 
                  for(let i=0; i < [...sort_str].length; i++){
                    if(a_str.codePointAt(i) !== b_str.codePointAt(i)){
                      if(a_str.codePointAt(i) > b_str.codePointAt(i)){
                        return 1;
                      }else if(a_str.codePointAt(i) < b_str.codePointAt(i)){
                        return -1;
                      }
                    }          
                  }
                }

                if(a_number !== b_number){
                  if(!a_number){
                    a_number = 0;
                  }
                  if(!b_number){
                    b_number = 0;
                  }

                  if(parseInt(a_number) > parseInt(b_number)){
                    return 1;
                  }else if(parseInt(a_number) < parseInt(b_number)){
                    return -1;
                  }
                }

                if(a_alf !== b_alf){
                  if(a_alf.codePointAt(0) > b_alf.codePointAt(0)){
                    return 1;
                  }else if(a_alf.codePointAt(0) < b_alf.codePointAt(0)){
                    return -1;
                  }else{
                    return 0;
                  }
                }
              }
              
              return 0;
            });

            this.showScenes = array;
          }else if(sort === "updated_at"){
            array.sort((a, b) => {
              if(a.updated_at !== b.updated_at){
                return new Date(a.updated_at) - new Date(b.updated_at);
              }
              
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

              // kanaで並び替え
              if(a.prop.kana !== b.prop.kana){
                const a_str = a.prop.kana.replace(regex_str, "");
                const b_str = b.prop.kana.replace(regex_str, "");
                let a_number = a.prop.kana.replace(regex_number, "");
                let b_number = b.prop.kana.replace(regex_number, "");
                const a_alf = a.prop.kana.replace(regex_alf, "");
                const b_alf = b.prop.kana.replace(regex_alf, "");

                if(a_str !== b_str){
                  let sort_str = a_str;
                  if([...b_str].length < [...a_str].length){
                    sort_str = b_str;
                  } 
                  for(let i=0; i < [...sort_str].length; i++){
                    if(a_str.codePointAt(i) !== b_str.codePointAt(i)){
                      if(a_str.codePointAt(i) > b_str.codePointAt(i)){
                        return 1;
                      }else if(a_str.codePointAt(i) < b_str.codePointAt(i)){
                        return -1;
                      }
                    }          
                  }
                }

                if(a_number !== b_number){
                  if(!a_number){
                    a_number = 0;
                  }
                  if(!b_number){
                    b_number = 0;
                  }

                  if(parseInt(a_number) > parseInt(b_number)){
                    return 1;
                  }else if(parseInt(a_number) < parseInt(b_number)){
                    return -1;
                  }
                }

                if(a_alf !== b_alf){
                  if(a_alf.codePointAt(0) > b_alf.codePointAt(0)){
                    return 1;
                  }else if(a_alf.codePointAt(0) < b_alf.codePointAt(0)){
                    return -1;
                  }else{
                    return 0;
                  }
                }
              }
              
              return 0;
            });

            this.showScenes = array;
          }else{
            this.sort_Standard(array);
          }          
        }      
      },

      // 使用シーン詳細のモーダル表示 
      openModal_sceneDetail (id) {
        this.showContent = true;
        this.postScene = id;
      },
      // 使用シーン詳細のモーダル非表示
      async closeModal_sceneDetail() {
        this.showContent = false;
        await this.fetchScenes();
      },

      // 小道具詳細のモーダル表示 
      openModal_propDetail (id) {
        this.showContent_prop = true;
        this.postProp = id;
      },
      // 小道具詳細のモーダル非表示
      async closeModal_propDetail() {
        this.showContent_prop = false;
        await this.fetchScenes();
      },

      // 選択ボタン出現
      showCheckBox() {
        if(this.choice_flag){
          this.choice_flag = false;
          this.choice_many = 0;
          this.scenes.forEach((scene) => {
            this.$set(this.choice_ids, scene.id, false);
          }, this);
        }else{
          this.choice_flag = true;
        }
      },

      // 選択（全選択）
      choiceDeleteAllScenes() {
        if(!this.choice_many){
          this.choice_many = 1;
          this.showScenes.forEach((scene) => {
            // リアクティブにするため
            this.$set(this.choice_ids, scene.id, true);
          }, this);
        }else{
          this.choice_many = 0;
          this.showScenes.forEach((scene) => {
            this.$set(this.choice_ids, scene.id, false);
          }, this);
        }
      },

      // 編集customのモーダル表示 
      openModal_customEdit () {
        this.showContent_customEdit = true;
        this.postMessage_CustomEdit = '使用シーンの編集項目について選択してください。';
      },
      // 編集customのモーダル非表示_OKの場合
      async closeModal_customEdit_OK(edit_custom_flag) {
        if(edit_custom_flag !== null){
          this.showContent_customEdit = false;
          this.$emit('close');
          const yes = edit_custom_flag.indexOf('yes');
          const no = edit_custom_flag.indexOf('no');
          if(yes !== -1){
            // yes
            this.yes_no = 1;
            this.edit_custom =  edit_custom_flag.replace('_yes', '');
          }else if(no !== -1){
            // no
            this.yes_no = 0;
            this.edit_custom = edit_custom_flag.replace('_no', '');
          }
          this.openModal_confirmEdit();
        }        
      },
      // 編集customのモーダル非表示_Cancelの場合
      closeModal_customEdit_Cancel() {
        this.showContent_customEdit = false;
      },

      // 編集confirmのモーダル表示 
      openModal_confirmEdit () {
        this.showContent_confirmEdit = true;
        let edit_scenes_name = '';
        let edit_custom_show;
        let yes_no_show;
        if(this.scenes.length === this.showScenes.length && this.choice_many){
          edit_scenes_name ='全て\n';
        }
        this.showScenes.forEach((scene, index) => {
          if(this.choice_ids[scene.id]){
            if(scene.first_page === 1 && scene.final_page === 1000){
              edit_scenes_name = edit_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + ': 全シーン' + '\n';
            }else if(scene.first_page && scene.final_page){
              edit_scenes_name = edit_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + ': p.' + scene.first_page + '~ ' + scene.final_page + '\n';
            }else if(scene.first_page){
              edit_scenes_name = edit_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + ': p.' + scene.first_page + '\n';
            }else{
              edit_scenes_name = edit_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + '\n';
            }
          }
        }, this);

        if(this.edit_custom === 'decision'){
          edit_custom_show = '決定して'
        }else if(this.edit_custom === 'usage'){
          edit_custom_show = '中間発表で使用して';
        }else if(this.edit_custom === 'usage_guraduation'){
          edit_custom_show = '卒業公演で使用して';
        }else if(this.edit_custom === 'usage_left'){
          edit_custom_show = '上手で使用して';
        }else if(this.edit_custom === 'usage_right'){
          edit_custom_show = '下手で使用して';
        }else{
          edit_custom_show = 'セットする人を削除す';
        }
        
        if(this.yes_no === 1){
          yes_no_show = 'る';
        }else{
          yes_no_show = 'ない';
        }

        this.postMessage_Edit = '以下の使用シーンを' + edit_custom_show + yes_no_show + 'と変更します。\n本当に変更しますか？\n' + edit_scenes_name;
      },
      // 編集confirmのモーダル非表示_OKの場合
      async closeModal_confirmEdit_OK() {
        this.showContent_confirmEdit = false;
        this.$emit('close');
        await this.EditProps();
      },
      // 編集confirmのモーダル非表示_Cancelの場合
      closeModal_confirmEdit_Cancel() {
        this.showContent_confirmEdit = false;
        this.openModal_customEdit();
      },

      // 選択編集(実行)
      async EditProps() {
        let scene_ids = [];
        let prop_ids_dupli = [];
        let method = this.edit_custom;
        let yes_no;
        this.showScenes.forEach((scene) => {
          if(this.choice_ids[scene.id]){
            scene_ids.push(scene.id);
          }
        });
        this.showScenes.forEach((scene) => {
          if(this.choice_ids[scene.id]){
            prop_ids_dupli.push(scene.prop_id);
          }
        });
        const prop_ids_set = new Set(prop_ids_dupli);
        const prop_ids = [...prop_ids_set];
        if(this.yes_no === 1){
          yes_no = 1;
        }else{
          yes_no = null;
        }
        const response = await axios.post('/api/scenes_many/' + scene_ids, {
          method: method,
          yes_no: yes_no,
          prop_ids: prop_ids
        });
        await this.fetchScenes();
        // 選択削除閉じる
        this.showCheckBox();
      },

      // 削除confirmのモーダル表示 
      openModal_confirmDelete (id) {
        this.showContent_confirmDelete = true;
        let delete_scenes_name = '';
        if(this.scenes.length === this.showScenes.length && this.choice_many){
          delete_scenes_name ='全て\n';
        }
        this.showScenes.forEach((scene, index) => {
          if(this.choice_ids[scene.id]){
            if(scene.first_page === 1 && scene.final_page === 1000){
              delete_scenes_name = delete_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + ': 全シーン' + '\n';
            }else if(scene.first_page && scene.final_page){
              delete_scenes_name = delete_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + ': p.' + scene.first_page + '~ ' + scene.final_page + '\n';
            }else if(scene.first_page){
              delete_scenes_name = delete_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + ': p.' + scene.first_page + '\n';
            }else{
              delete_scenes_name = delete_scenes_name + '・' + scene.character.name + ': ' + scene.prop.name + '\n';
            }
          }
        }, this);
        this.postMessage_Delete = '本当に削除しますか？\n' + delete_scenes_name;
      },
      // 削除confirmのモーダル非表示_OKの場合
      async closeModal_confirmDelete_OK() {
        this.showContent_confirmDelete = false;
        this.$emit('close');
        await this.deleteProps();
      },
      // 削除confirmのモーダル非表示_Cancelの場合
      closeModal_confirmDelete_Cancel() {
        this.showContent_confirmDelete = false;
      },

      // 選択削除（実行）
      async deleteScenes() {
        let ids = [];
        this.showScenes.forEach((scene) => {
          if(this.choice_ids[scene.id]){
            ids.push(scene.id);
          }
        });
        const response = await axios.delete('/api/scenes_many/' + ids);
        await this.fetchScenes();
        // 選択削除閉じる
        this.showCheckBox();
      },

      // // ダウンロード
      // downloadScenes() {
      //   const response = axios.post('/api/scenes_list', this.showScenes);
      // }

      // ダウンロード
      async downloadScenes() {
        // ①初期化
        const workbook = new ExcelJS.Workbook(); // workbookを作成
        workbook.addWorksheet('Sheet1'); // worksheetを追加
        const worksheet = workbook.getWorksheet('Sheet1'); // 追加したworksheetを取得

        // ②データを用意
        // 各列のヘッダー
        worksheet.columns = [
          { header: '何ページから', key: 'first_page', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '何ページまで', key: 'final_page', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '登場人物', key: 'character', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '小道具', key: 'prop', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '中間発表', key: 'usage', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '卒業公演', key: 'usage_guraduation', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '上手', key: 'usage_left', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '下手', key: 'usage_right', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: 'メモ', key: 'memo', width: 24, style: { alignment: {vertical: "middle", horizontal: "center" }}},
        ];

        worksheet.views = [
          {state: 'frozen', xSplit: 0, ySplit: 1, activeCell: 'A1'}  // ウィンドウ固定
        ];
        const font =  { color: { argb: '169b62' }}; // 文字
        const fill =  { type: 'pattern', pattern:'solid', fgColor: { argb:'ddefe3' }}; // 背景色
        worksheet.getCell('A1').font = font;
        worksheet.getCell('A1').fill = fill;
        worksheet.getCell('B1').font = font;
        worksheet.getCell('B1').fill = fill;
        worksheet.getCell('C1').font = font;
        worksheet.getCell('C1').fill = fill;
        worksheet.getCell('D1').font = font;
        worksheet.getCell('D1').fill = fill;
        worksheet.getCell('E1').font = font;
        worksheet.getCell('E1').fill = fill;
        worksheet.getCell('F1').font = font;
        worksheet.getCell('F1').fill = fill;
        worksheet.getCell('G1').font = font;
        worksheet.getCell('G1').fill = fill;
        worksheet.getCell('H1').font = font;
        worksheet.getCell('H1').fill = fill;
        worksheet.getCell('I1').font = font;
        worksheet.getCell('I1').fill = fill;

        this.showScenes.forEach((scene, index) => {
          let datas = [];
          datas.push(scene.first_page);
          datas.push(scene.final_page);

          datas.push(scene.character.name);

          datas.push(scene.prop.name);

          if(scene.usage){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(scene.usage_guraduation){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(scene.usage_left){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(scene.usage_right){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(scene.scene_comments.length){
            scene.scene_comments.forEach((comment, index_comment) => {
              if(index_comment){
                const remove_data = datas.splice(datas.length-1, datas.length-1, datas[datas.length-1]+'<br>'+comment.memo);
              }else{
                datas.push(comment.memo);
              }
            })
          }

          //行を取得
          let sheet_row = worksheet.getRow( index + 2 ) ;
 
          //列を取得し値を設定
          datas.forEach((data, index_data) => {
            sheet_row.getCell( index_data + 1 ).value = data ;
          })
 
          
        })

        // ③ファイル生成
        const uint8Array = await workbook.xlsx.writeBuffer(); // xlsxの場合
        const blob = new Blob([uint8Array], { type: 'application/octet-binary' });
        const a = document.createElement('a');
        a.href = (window.URL || window.webkitURL).createObjectURL(blob);
        const today = this.formatDate(new Date());
        const filename = 'Scenes_list_' + 'all' + '_' + today + '.xlsx';
        a.download = filename;
        a.click();
        a.remove();
        
      },

      // 日付をyyyy-mm-ddで返す
      formatDate(dt) {
        const y = dt.getFullYear();
        const m = ('00' + (dt.getMonth()+1)).slice(-2);
        const d = ('00' + dt.getDate()).slice(-2);
        return (y + '-' + m + '-' + d);
      }
    }
  }  
</script>