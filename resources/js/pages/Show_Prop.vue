<template>
  <div>
    <!-- ボタンエリア -->
    <div>
      <!--表示切替ボタン-->
      <div v-show="tabProp === 1" class="button-area--showhow">
       <button type="button" @click="switchDisplay_prop" class="button button--inverse"><i class="fas fa-th fa-fw"></i>写真ブロック</button>
      </div>
      <div v-show="tabProp === 2" class="button-area--showhow">
       <button type="button" @click="switchDisplay_prop" class="button button--inverse"><i class="fas fa-list-ul fa-fw"></i>リスト</button>
      </div>
    </div>


    <!-- 表示エリア -->
    <div v-show="tabProp === 1">
      <div v-if="!sizeScreen" class="PC">
        <!-- ダウンロードボタン -->
        <div class="button-area--download">
          <button type="button" @click="downloadProps" class="button button--inverse"><i class="fas fa-download fa-fw"></i>ダウンロード</button>
        </div>
        <table>
          <thead>
            <tr>
              <th class="th-non"></th>
              <th>小道具名</th>
              <th>持ち主</th>
              <th>中間</th>
              <th>卒業</th>
              <th>上手</th>
              <th>下手</th>
              <th class="th-memo">メモ</th>
              <th>登録日時</th>
              <th>更新日時</th>
            </tr>
          </thead>
          <tbody v-if="showProps.length">
            <tr v-for="(prop, index) in showProps">
              <td class="td-color">{{ index + 1 }}</td>
              <!-- 小道具名 -->
              <td type="button" class="list-button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</td>
              <!-- 持ち主 -->
              <td v-if="prop.owner">{{ prop.owner.name }}</td>
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
          小道具は登録されていません。 
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
                {{ prop.name}}
              </div>
              <!-- 持ち主 -->
              <div v-if="prop.owner">
                {{ prop.owner.name }}
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
        小道具は登録されていません。
      </div>
    </div> 
    <detailProp :postProp="postProp" v-show="showContent" @close="closeModal_propDetail" /> 
  </div>
</template>

<script>
  import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util';

  import detailProp from '../components/Detail_Prop.vue';
  import ExcelJS from 'exceljs';

  export default {
    // このページの上で表示するコンポーネント
    components: {
      detailProp
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
        postProp: ""
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

        this.showProps.forEach((prop, index) => {
          let datas = [];
          datas.push(prop.name);

          if(prop.owner){
            datas.push(prop.owner.name);
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