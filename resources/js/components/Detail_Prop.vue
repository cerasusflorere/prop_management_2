<template>
  <div id="overlay">
    <div id="content" class="panel">
        <div class="detail-box">
          <!-- 写真 -->
          <div>
            <img :src="prop.url" :alt="prop.name">
          </div>
          <!-- 詳細 -->
          <div>
            <div>
              <h1 style="display: inline">{{ prop.name }}</h1>
              <div v-if="prop.usage === 1"><i class="fas fa-tag"></i></div>
            </div>
            <div>所有者: <span v-if="prop.owner">{{ prop.owner.name }}</span></div>

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
                    <!-- 名前 -->
                    <span>{{ scene.character.name }}</span>
                    <!-- 何ページ -->
                    <span v-if="scene !== null && scene.first_page !== null"> : p. {{ scene.first_page }} 
                      <span v-if="scene !== null && scene.final_page !== null"> ~ p. {{ scene.final_page}}</span>
                    </span>
                    <!-- メモ -->
                    <div>
                      <ul v-if="scene.scene_comments.length">
                        <li v-for="comment in scene.scene_comments">
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
import { OK, UNPROCESSABLE_ENTITY } from '../util'

export default {
  // モーダルとして表示
  name: 'detailProp',
  props: {
    val: {
      type: Number,
      required: true
    }
  },
  // データ
  data() {
    return {
      prop: []
    }
  },
  watch: {
    val: {
      async handler(val) {
        await this.fetchProp()
      },
      immediate: true,
    }
  },
  methods: {
    // 小道具の詳細を取得
    async fetchProp () {
      const response = await axios.get('/api/props/'+ this.val)

      if (response.statusText !== 'OK') {
        this.$store.commit('error/setCode', response.status)
        return false
      }

      this.prop = response.data
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