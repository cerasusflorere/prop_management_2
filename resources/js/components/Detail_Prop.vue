<template>
  <div id="overlay">
    <div id="content" class="panel">
        <div class="detail-box">
          <!-- 写真 -->
          <div>
            <img :src="val.url" :alt="val.name">
          </div>
          <!-- 詳細 -->
          <div>
            <div>
              <h1 style="display: inline">{{ val.name }}</h1>
              <div v-if="val.usage === 1"><i class="fas fa-tag"></i></div>
            </div>
            <div>所有者: {{ val.owner }}</div>

            <div>
              <label>メモ:</label>
              <ul v-if="comments_prop.length" >
                <li v-for="comment in comments_prop">
                  <div>{{ comment.memo }}</div>
                </li>
              </ul>
            </div>

            <div>
              <label>シーン:</label>
                <ol v-if="scenes.length">
                  <li v-for="scene in scenes">
                    <!-- 名前 -->
                    <span>{{ scene.name }}</span>
                    <!-- 何ページ -->
                    <span v-if="scene !== null && scene.first_page !== null"> : p. {{ scene.first_page }} 
                      <span v-if="scene !== null && scene.final_page !== null"> ~ p. {{ scene.final_page}}</span>
                    </span>
                    <!-- メモ -->
                    <div>
                      <ul v-for="comment in comments_scene">
                        <li v-if="comment.scene_id == scene.id">
                          <div>{{ comment.memo }}</div>
                        </li>
                      </ul>
                    </div>
                  </li>                  
                </ol>
            </div> 
          </div>
        </div>
        <button type="button" @click="$emit('close')" class="button button--inverse">閉じる</button>
    </div>
  </div>
</template>

<script>
export default {
  // モーダルとして表示
  name: 'detailProp',
  props: {
    val: {
      type: Object,
      required: true
    }
  },
  // データ
  data() {
    return {
      prop: [],
      // 小道具のid
      mono: '',
      // 小道具に関するメモ
      comments_prop_all: [ 
        { id: 1, prop_id: 1, memo: '変えるかも' }, 
        { id: 2, prop_id: 3, memo: '壊さないように' }, 
        { id: 3, prop_id: 3, memo: '要検討' }
      ],
      scenes_all: [ 
        { id: 1, name: 'アン', prop_id: 1, first_page: 1, final_page: null }, 
        { id: 2, name: 'ステファン', prop_id: 3, first_page: 20, final_page: 22 }, 
        { id: 3, name: 'ジョー', prop_id: 3, first_page: null, final_page: null }
      ],
      comments_scene_all: [
        { id: 1, scene_id: 1, memo: '毎回変える？'},
        { id: 2, scene_id: 3, memo: 'どこ？'},
      ],
      comments_prop: [],
      scenes: [],
      comments_scene: [],
    }
  },
  watch: {
    val: {
      immediate: true,
      handler(val){
        this.mono = this.val.id,
        this.scopeData_second(this.mono)
      }  
    },
    $route: {
      async handler () {
        await this.fetchProp()
      },
      immediate: true
    }
  },
  methods: {
    // 小道具の詳細を取得
    async fetchProp () {
      const response = await axios.get('/api/props/{id}')

      // if (response.statusText !== OK) {
      //   this.$store.commit('error/setCode', response.status)
      //   return false
      // }

      this.prop = response.data
    },
    // 該当する小道具に絞る
    async scopeData_first(id) {
      // 小道具のコメント絞り込み
      let comments_selected = []
      this.comments_prop_all.forEach(function(comment){
        if(comment.prop_id == id){
          comments_selected.push(comment)
        }
      })
      this.comments_prop = JSON.parse(JSON.stringify(comments_selected))
      // 使用シーン絞り込み
      let scenes_selected = []
      this.scenes_all.forEach(function(scene){
        if(scene.prop_id == id){
          scenes_selected.push(scene)
        }
      })
      this.scenes = JSON.parse(JSON.stringify(scenes_selected))
      return scenes_selected;
    },

    async scopeData_second(id) {
      let comments_selected = []
      let scenes_selected = await this.scopeData_first(id)
      let comments_all = JSON.parse(JSON.stringify(this.comments_scene_all))
      scenes_selected.forEach(function(scene) {
        comments_all.forEach(function(comment) {
          if(comment.scene_id == scene.id){
            comments_selected.push(comment)
          }
        })
      })
      this.comments_scene = JSON.parse(JSON.stringify(comments_selected))
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