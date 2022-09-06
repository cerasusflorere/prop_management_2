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
          <th>メモ</th>
          <th>使用状況</th>
          <th>登録日時</th>
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
            <!-- メモ -->
            <td v-if="scene.scene_comments.length">
              <div v-for="memo in scene.scene_comments"> {{ memo.memo }}</div>
            </td>
            <td v-else></td>
            <!-- 使用状況 -->
            <td v-if="scene.usage"><i class="fas fa-check fa-fw"></i></td>
            <td v-else></td>            
            <!-- 登録日時 -->
            <td>{{ scene.created_at }}</td>
        </tr>
      </tbody>      
    </table>
   
    <detailScene :postScene="postScene" v-show="showContent" @close="closeModal_sceneDetail" />
  </div>
</template>

<script>
  import { OK, CREATED, UNPROCESSABLE_ENTITY } from '../util'

  import detailScene from '../components/Detail_Scene.vue'

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
        const response = await axios.get('/api/scenes')
  
        if (response.statusText !== 'OK') {
          this.$store.commit('error/setCode', response.status)
          return false
        }

        this.scenes = response.data // オリジナルデータ
        this.showScenes = JSON.parse(JSON.stringify(this.scenes));
      },

      // 使用シーン詳細のモーダル表示 
      openModal_sceneDetail (id) {
        this.showContent = true
        this.postScene = id;
      },
      // 使用シーン詳細のモーダル非表示
      async closeModal_sceneDetail() {
        this.showContent = false
        await this.fetchScenes()
      },

      // ダウンロード
      downloadScenes() {
        const response = axios.post('/api/scenes_list', this.showScenes);
      }
    }
  }  
</script>

<style>
  table {
    margin: auto;
    width: 95%;
    border-collapse: collapse;    
  }

  table th, table td {
    border: solid 1px black; /*実線 1px 黒*/
    text-align: center;
  }

  table th {/*table内のthに対して*/
    position: sticky;
    top: 3.9rem;
    padding: 0.5em;/*上下左右10pxずつ*/
    color: #169b62;/*文字色 緑*/
    background: #ddefe3;/*背景色*/
  }

  table td {/*table内のtdに対して*/
    padding: 0.3em 0.5em;/*上下3pxで左右10px*/
  }
</style>