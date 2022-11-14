<template>
  <div>
    <!-- ボタンエリア -->
    <div class="button-area">
      <!--表示切替ボタン-->
      <div v-show="tabProp === 1" class="button-area--showhow">
        <button type="button" @click="switchDisplay_prop" class="button button--inverse"><i class="fas fa-th fa-fw"></i>写真ブロック</button>
      </div>
      <div v-show="tabProp === 2" class="button-area--showhow">
        <button type="button" @click="switchDisplay_prop" class="button button--inverse"><i class="fas fa-list-ul fa-fw"></i>リスト</button>
      </div>

      <div v-if="props.length" class="button-area--small">
        <di class="button-area--together-left">
          <!-- 検索 -->
          <div class="button-area--small-small">
            <button type="button" @click="openModal_searchProp(Math.random())" class="button button--inverse button--small"><i class="fas fa-search fa-fw"></i>検索</button>
          </div>
          <searchProp :postSearch="postSearch" v-show="showContent_search" @close="closeModal_searchProp" />

          <!-- 選択 -->
          <div class="button-area--small-small">
            <button type="button" @click="showCheckBox" class="button button--inverse button--small button--choice"><i class="fas fa-check-square fa-fw"></i>選択</button>
          </div>

          <!-- 選択削除実行 -->
          <div v-if="delete_flag" class="button-area--small-small">
            <button type="button" @click="openModal_confirmDelete" class="button button--inverse button--small button--choice"><i class="fas fa-trash-alt fa-fw"></i>選択削除</button>
          </div>
          <confirmDialog_Delete :confirm_dialog_delete_message="postMessage_Delete" v-show="showContent_confirmDelete" @Cancel_Delete="closeModal_confirmDelete_Cancel" @OK_Delete="closeModal_confirmDelete_OK"/>
        </di>
        
        <!-- ダウンロードボタン -->
        <!-- リスト表示かつPCかつデータがある時 -->
        <div v-show="tabProp === 1" v-if="!sizeScreen && showProps.length" class="button-area--small-small">
          <button type="button" @click="downloadProps" class="button button--inverse button--small"><i class="fas fa-download fa-fw"></i>ダウンロード</button>
        </div>
      </div> 
    </div>


    <!-- 表示エリア -->
    <div v-show="tabProp === 1">
      <div v-if="!sizeScreen" class="PC">
        <table v-if="showProps.length">
          <thead>
            <tr>
              <th v-if="delete_flag" class="th-non">
                <input type="checkbox" class="checkbox-delete" @click="choiceDeleteAllProps"></input>
              </th>
              <th class="th-non"></th>
              <th>小道具名</th>
              <th>持ち主</th>
              <th>ピッコロ</th>
              <th>決定</th>
              <th>中間</th>
              <th>卒業</th>
              <th>上手</th>
              <th>下手</th>
              <th class="th-memo">メモ</th>
              <th>登録日時</th>
              <th>更新日時</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(prop, index) in showProps">
              <td v-if="delete_flag">
                <input type="checkbox" class="checkbox-delete" v-model="delete_ids[prop.id]"></input>
              </td>
              <td class="td-color">{{ index + 1 }}</td>
              <!-- 小道具名 -->
              <td type="button" class="list-button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</td>
              <!-- 持ち主 -->
              <td v-if="prop.owner">{{ prop.owner.name }}</td>
              <td v-else></td>
              <!-- ピッコロに持ってきたか -->
              <td v-if="prop.location"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- これで決定か -->
              <td v-if="prop.decision"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- 中間発表-->
              <td v-if="prop.usage"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- 卒業公演-->
              <td v-if="prop.usage_guraduation"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- 上手-->
              <td v-if="prop.usage_left"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- 下手-->
              <td v-if="prop.usage_right"><i class="fas fa-check fa-fw"></i></td>
              <td v-else></td>
              <!-- メモ -->
              <td v-if="prop.prop_comments.length">
                <div v-for="memo in prop.prop_comments"> {{ memo.memo }}</div>
              </td>
              <td v-else></td>
              <!-- 登録日時 -->
              <td>{{ prop.created_at }}</td>
              <!-- 更新日時 -->
              <td>{{ prop.updated_at }}</td>
            </tr> 
          </tbody>      
        </table>

        <div v-if="!showProps.length">
          小道具は登録されていません。 
        </div>
      </div>

      <div v-else class="phone">
        <div v-if="showProps.length">
          <table>
            <div v-for="(prop, index) in showProps">
              <tr>
                <th class="th-non"></th>
                <td class="td-color">{{ index + 1 }}</td> 
              </tr>
              <tr>
                <!-- 小道具名 -->
                <th>小道具名</th>
                <td type="button" class="list-button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</td>
              </tr>
              <tr>
                <!-- 持ち主 -->
                <th>持ち主</th>
                <td v-if="prop.owner">{{ prop.owner.name }}</td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- ピッコロに持ってきたか -->
                <th>ピッコロにあるか</th>
                <td v-if="prop.location"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- これで決定か -->
                <th>決定か</th>
                <td v-if="prop.decision"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 中間発表 -->
                <th>中間</th>
                <td v-if="prop.usage"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 卒業公演 -->
                <th>卒業</th>
                <td v-if="prop.usage_guraduation"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 上手 -->
                <th>上手</th>
                <td v-if="prop.usage_left"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 下手 -->
                <th>下手</th>
                <td v-if="prop.usage_right"><i class="fas fa-check fa-fw"></i></td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- メモ -->
                <th>メモ</th>
                <td v-if="prop.prop_comments.length">
                  <div v-for="memo in prop.prop_comments"> {{ memo.memo }}</div>
                </td>
                <td v-else></td>
              </tr>
              <tr>
                <!-- 登録日時 -->
                <th>登録日時</th>
                <td>{{ prop.created_at }}</td>
              </tr>
              <tr>
                <!-- 更新日時 -->
                <th>更新日時</th>
                <td>{{ prop.updated_at }}</td>
              </tr>
            </div>
          </table>
        </div>

        <div v-else>
          該当の小道具は登録されていません。 
        </div>
      </div>
    </div>

    <div v-show="tabProp === 2">
      <div class="grid" v-if="showProps.length">
        <div class="grid__item" v-for="prop in showProps">
          <div class="photo">
            <figure class="photo__wrapper" type="button" @click="openModal_propDetail(prop.id)">
              <img
                class="photo__image"
                :src="prop.url"
                :alt="prop.name"
              >
            </figure>
            <div>
              <!-- 小道具名 -->
              <div>
                {{ prop.name }}
              </div>
              <!-- 持ち主 -->
              <div v-if="prop.owner">
                {{ prop.owner.name }}
              </div>
              <!-- ピッコロに持ってきたか -->
              <div>
                <span class="usage-show">ピッコロにあるか:</span>
                <span v-if="prop.location" class="usage-show"><i class="fas fa-check fa-fw"></i></span>
              </div>

              <!-- これで決定か -->
              <div>
                <span class="usage-show">これで決定か:</span>
                <span v-if="prop.decision" class="usage-show"><i class="fas fa-check fa-fw"></i></span>
              </div>

              <div>
                <!-- 中間発表 -->
                <span v-if="prop.usage" class="usage-show">Ⓟ</span>
                <!-- 卒業公演 -->
                <span v-if="prop.usage_guraduation" class="usage-show">Ⓖ</span>
                <!-- 上手 -->
                <span v-if="prop.usage_left" class="usage-show">㊤</span>
                <!-- 下手 -->
                <span v-if="prop.right" class="usage-show">㊦</span>
              </div>
              
              <!-- メモ -->
              <div v-if="prop.prop_comments.length">
                <span>メモ: </span>
                <div v-for="memo in prop.prop_comments">
                  {{ memo.memo }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        該当の小道具は登録されていません。
      </div>
    </div> 
    <detailProp :postProp="postProp" v-show="showContent" @close="closeModal_propDetail" /> 
  </div>
</template>

<script>
  import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util';

  import detailProp from '../components/Detail_Prop.vue';
  import searchProp from '../components/Search_Prop.vue';
  import confirmDialog_Delete from '../components/Confirm_Dialog_Delete.vue'
  import ExcelJS from 'exceljs';

  export default {
    // このページの上で表示するコンポーネント
    components: {
      detailProp,
      searchProp,
      confirmDialog_Delete
    },
    data() {
      return{
        // 画面サイズ取得
        sizeScreen: 1, // 0:パソコン, 1: スマホ
        // タブ切り替え
        tabProp: 1,
        // 取得するデータ
        props: [],
        // 表示するデータ
        showProps: [],
        // 小道具詳細
        showContent: false,
        postProp: "",
        // 小道具検索カスタム
        showContent_search: false,
        postSearch: "",
        custom_sort: null,
        custom_name: null,
        custom_refine: null,        
        // 選択削除ボタン
        delete_flag: false,
        // 選択削除
        delete_ids: [],
        delete_many: 0,
        // 削除confirm
        showContent_confirmDelete: false,
        postMessage_Delete: ""
      }
    },
    watch: {
      $route: {
        async handler () {
          await this.fetchProps();
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
      // 小道具一覧を取得
      async fetchProps () {
        const response = await axios.get('/api/props_all');
  
        if (response.status !== 200) {
          this.$store.commit('error/setCode', response.status);
          return false;
        }

        this.props = response.data; // オリジナルデータ
        this.showProps = JSON.parse(JSON.stringify(this.props));
        
        this.props.forEach((prop) => {
          this.delete_ids.push(false);
        }, this);

        if(this.custom_sort || this.custom_name || this.custom_refine){
          await this.closeModal_searchProp(this.custom_sort, this.custom_name, this.custom_refine);
        }
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
      openModal_searchProp (number) {
        this.showContent_search = true;
        this.postSearch = number;
      },
      // 検索カスタムのモーダル非表示
      async closeModal_searchProp(sort, name, refine) {
        this.showContent_search = false;
        if(sort !== null && refine !== null){
          this.custom_sort = sort;
          this.custom_name = name;
          this.custom_refine = refine;
          let array_original = this.props.filter((a) => eval(refine));
          let array = [];

          if(this.h(name.input)){
            if(name.scope === "memo_together"){
              // メモを含めて検索
              array = array_original.filter((a) => {
                if(a.name.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }else if(a.kana.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }else if(a.prop_comments[0]){
                  if(a.prop_comments[0].memo.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1){
                    return a;
                  }                  
                }
              });
            }else{
              // 小道具名のみで検索
              array = array_original.filter((a) => {
                if(a.name.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }else if(a.kana.toLocaleLowerCase().indexOf(this.h(name.input).toLocaleLowerCase()) !== -1) {
                  return a;
                }
              });
            }
          }else{
            // 入力検索しない
            array = array_original;
          }
          
          if(sort === "owner"){
            array.sort((a, b) => a.owner_id - b.owner_id);
          }else if(sort === "created_at"){
            array.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
          }else if(sort === "updated_at"){
            array.sort((a, b) => new Date(a.updated_at) - new Date(b.updated_at));
          }else{
            array.sort((a, b) => a.kana - b.kana);
          }

          this.showProps = array;
        }      
      },
      
      // 表示切替
      switchDisplay_prop() {
        if(this.tabProp === 1){
          this.tabProp = 2;
        }else{
          this.tabProp = 1;
        }
      },

      // 小道具詳細のモーダル表示 
      openModal_propDetail (id) {
        this.showContent = true;
        this.postProp = id;
      },
      // 小道具詳細のモーダル非表示
      async closeModal_propDetail() {
        this.showContent = false;
        await this.fetchProps();
      },

      // 選択削除ボタン出現
      showCheckBox() {
        if(this.delete_flag){
          this.delete_flag = false;
          this.delete_many = 0;
          this.props.forEach((prop) => {
            this.$set(this.delete_ids, prop.id, false);
          }, this);
        }else{
          this.delete_flag = true;
        }
      },

      // 選択削除（全選択）
      choiceDeleteAllProps() {
        if(!this.delete_many){
          this.delete_many = 1;
          this.showProps.forEach((prop) => {
            // リアクティブにするため
            this.$set(this.delete_ids, prop.id, true);
          }, this);
        }else{
          this.delete_many = 0;
          this.showProps.forEach((prop) => {
            this.$set(this.delete_ids, prop.id, false);
          }, this);
        }
      },

      // 削除confirmのモーダル表示 
      openModal_confirmDelete (id) {
        this.showContent_confirmDelete = true;
        let delete_props_name = '';
        if(this.props.length === this.showProps.length && this.delete_many){
          delete_props_name ='全て\n';
        }
        this.showProps.forEach((prop, index) => {
          if(this.delete_ids[prop.id]){
            delete_props_name = delete_props_name + '・' + prop.name + '\n';
          }
        }, this);
        this.postMessage_Delete = '以下の小道具を削除すると、紐づけられてたこの小道具を使用するシーンも全て削除されます。\n本当に削除しますか？\n' + delete_props_name;
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
      async deleteProps() {
        let ids = [];
        this.showProps.forEach((prop) => {
          if(this.delete_ids[prop.id]){
            ids.push(prop.id);
          }
        });
        console.log(ids);
        const response = await axios.delete('/api/props_many/' + ids);
        await this.fetchProps();
        // 選択削除閉じる
        this.showCheckBox();
      },


      // ダウンロード
      // downloadProps() {
      //   const response = axios.post('/api/props_list', this.showProps);
      // }
      // ダウンロード
      async downloadProps() {
        // ①初期化
        const workbook = new ExcelJS.Workbook(); // workbookを作成
        workbook.addWorksheet('Sheet1'); // worksheetを追加
        const worksheet = workbook.getWorksheet('Sheet1'); // 追加したworksheetを取得

        // ②データを用意
        // 各列のヘッダー
        worksheet.columns = [
          { header: '小道具名', key: 'name', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '持ち主', key: 'owner', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: 'ピッコロ', key: 'location', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
          { header: '決定', key: 'decision', width: 12, style: { alignment: {vertical: "middle", horizontal: "center" }}},
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

        this.showProps.forEach((prop, index) => {
          let datas = [];
          datas.push(prop.name);

          if(prop.owner){
            datas.push(prop.owner.name);
          }else{
            datas.push(null);
          }

          if(prop.location){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(prop.decision){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(prop.usage){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(prop.usage_guraduation){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(prop.usage_left){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(prop.usage_right){
            datas.push('〇');
          }else{
            datas.push(null);
          }

          if(prop.prop_comments.length){
            prop.prop_comments.forEach((comment, index_comment) => {
              if(index_comment){
                const remove_data = datas.splice(datas.length-1, datas.length-1, datas[datas.length-1]+'<br>'+comment.memo)
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
        const filename = 'Props_list_' + 'all' + '_' + today + '.xlsx';
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