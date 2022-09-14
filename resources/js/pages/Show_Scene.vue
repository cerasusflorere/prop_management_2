<template>
  <!-- 表示エリア -->
  <div>
    <!-- ダウンロードボタン -->
    <div>
      <button type="button" @click="downloadScenes">ダウンロード</button>
    </div>
    <table>
      <thead>
        <tr>
          <th></th>
          <th>何ページから</th>
          <th>何ページまで</th>
          <th>登場人物</th>
          <th>小道具名</th> 
          <th>中間発表</th>
          <th>卒業公演</th>
          <th>上手</th>
          <th>下手</th>         
          <th>メモ</th>
          <th>登録日時</th>
          <th>更新日時</th>
        </tr>
      </thead>
      <tbody v-if="showScenes.length">
        <tr v-for="(scene, index) in showScenes">
            <!-- index -->
            <td type="button" @click="openModal_sceneDetail(scene.id)">{{ index + 1 }}</td>
            <!-- 何ページから -->
            <td v-if="scene.first_page">{{ scene.first_page }}</td>
            <td v-else></td>
            <!-- 何ページまで -->
            <td v-if="scene.final_page">{{ scene.final_page }}</td>
            <td v-else></td>
            <!-- 登場人物 -->
            <td>{{ scene.character.name }}</td>
            <!-- 小道具名 -->
            <td>{{ scene.prop.name }}</td>
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
   
    <detailScene :postScene="postScene" v-show="showContent" @close="closeModal_sceneDetail" />
  </div>
</template>

<script>
  import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util';

  import detailScene from '../components/Detail_Scene.vue';
  import ExcelJS from 'exceljs';

  export default {
    // このページの上で表示するコンポーネント
    components: {
      detailScene
    },
    data() {
      return{
        // 取得するデータ
        scenes: [],
        // 表示するデータ
        showScenes: [],
        // シーン詳細
        showContent: false,
        postScene: ""
      }
    },
    watch: {
      $route: {
        async handler () {
          await this.fetchScenes()
        },
        immediate: true
      }
    },
    methods: {
      // 使用シーン一覧を取得
      async fetchScenes () {
        const response = await axios.get('/api/scenes');

        this.scenes = response.data; // オリジナルデータ
        this.showScenes = JSON.parse(JSON.stringify(this.scenes));
  
        if (response.statusText !== 'OK') {
          this.$store.commit('error/setCode', response.status);
          return false;
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