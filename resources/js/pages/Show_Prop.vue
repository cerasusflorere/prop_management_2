<template>
  <div>
    <!-- ボタンエリア -->
    <div>
      <!--表示切替ボタン-->
      <div v-show="tabProp === 1">
       <button type="button" @click="switchDisplay_prop"><i class="fas fa-th fa-fw"></i>写真ブロック</button>
      </div>
      <div v-show="tabProp === 2">
       <button type="button" @click="switchDisplay_prop"><i class="fas fa-list-ul fa-fw"></i>リスト</button>
      </div>
    </div>


    <!-- 表示エリア -->
    <div v-show="tabProp === 1">
      <!-- ダウンロードボタン -->
      <div>
        <button type="button" @click="downloadProps">ダウンロード</button>
      </div>
      <table>
        <thead>
          <tr>
            <th></th>
            <th>小道具名</th>
            <th>持ち主</th>
            <th>中間発表</th>
            <th>卒業公演</th>
            <th>上手</th>
            <th>下手</th>
            <th>メモ</th>
            <th>登録日時</th>
            <th>更新日時</th>
          </tr>
        </thead>
        <tbody v-if="showProps.length">
          <tr v-for="(prop, index) in showProps">
            <td>{{ index + 1 }}</td>
            <!-- 小道具名 -->
            <td type="button" @click="openModal_propDetail(prop.id)">{{ prop.name }}</td>
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

    <div v-show="tabProp === 2" class="photo-list">
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
              <!-- 中間発表 -->
              <div v-if="prop.usage">
                <i class="fas fa-tag"></i>
              </div>
              <!-- 卒業公演 -->
              <div v-if="prop.usage_guraduation">
                <i class="fas fa-tag"></i>
              </div>
              <!-- 上手 -->
              <div v-if="prop.usage_left">
                <i class="fas fa-tag"></i>
              </div>
              <!-- 下手 -->
              <div v-if="prop.right">
                <i class="fas fa-tag"></i>
              </div>
              <!-- メモ -->
              <div v-if="prop.prop_comments.length">
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
          await this.fetchProps()
        },
        immediate: true
      }
    },
    methods: {
      // 小道具一覧を取得
      async fetchProps () {
        const response = await axios.get('/api/props_all');
        
        this.props = response.data; // オリジナルデータ
        this.showProps = JSON.parse(JSON.stringify(this.props));
  
        if (response.statusText !== 'OK') {
          this.$store.commit('error/setCode', response.status);
          return false;
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