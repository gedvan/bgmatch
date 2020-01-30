<template>
  <div id="ranking">
    <h3>Ranking {{ $route.params.ano }}</h3>
    <div class="trilha-pontos">
      <div v-for="(x, d) in 10" :class="['dezena', 'dezena-' + d]">
        <div v-for="(y, u) in 10" :class="['casa', 'casa-' + u]">
          {{ (d * 10) + u }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {

  }
</script>

<style lang="scss">
  @import "../scss/includes";

  #ranking {
    h3 {
      margin: .5em 0;
    }
    .trilha-pontos {
      display: grid;
      grid-template-columns: 29px auto 29px;
      grid-template-rows: 29px auto auto auto auto 29px;
      grid-template-areas: "d0 d0 d0" "d9 c d1" "d8 c d2" "d7 c d3" "d6 c d4" "d5 d5 d5";

      .casa {
        background: #f7f6ee;
        color: #dddabe;
        border: 1px solid white;
        flex-grow: 1;
        min-width: 29px;
        min-height: 29px;
        display: flex;
        justify-content: center;
        align-items: center;
        &.casa-0, &.casa-5 {
          font-weight: bold;
          background: #f2ece1;
          color: #ccbe86;
        }
      }

      .dezena {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
      }
      .dezena-0 {
        grid-area: d0;
        flex-direction: row;
      }
      .dezena-1 {
        grid-area: d1;
        flex-direction: column;
      }
      .dezena-2 {
        grid-area: d2;
        flex-direction: column;
      }
      .dezena-3 {
        grid-area: d3;
        flex-direction: column;
      }
      .dezena-4 {
        grid-area: d4;
        flex-direction: column;
      }
      .dezena-5 {
        grid-area: d5;
        flex-direction: row-reverse;
      }
      .dezena-6 {
        grid-area: d6;
        flex-direction: column-reverse;
      }
      .dezena-7 {
        grid-area: d7;
        flex-direction: column-reverse;
      }
      .dezena-8 {
        grid-area: d8;
        flex-direction: column-reverse;
      }
      .dezena-9 {
        grid-area: d9;
        flex-direction: column-reverse;
      }

      @include media-breakpoint-up(sm) {
        grid-template-columns: 34px auto 34px;
        grid-template-rows: 34px auto auto auto auto 34px;

        .casa {
          min-width: 34px;
          min-height: 34px;
        }
      }

      @include media-breakpoint-up(md) {
        grid-template-columns: 34px auto auto 34px;
        grid-template-rows: 34px auto auto auto 34px;
        grid-template-areas: "d0 d0 d1 d1" "d9 c c d2" "d8 c c d3" "d7 c c d4" "d6 d6 d5 d5";

        .dezena-1 {
          flex-direction: row;
        }
        .dezena-6 {
          flex-direction: row-reverse;
        }
      }

      @include media-breakpoint-up(xl) {
        grid-template-columns: 37px auto auto auto 37px;
        grid-template-rows: 37px auto auto 37px;
        grid-template-areas: "d0 d0 d1 d2 d2" "d9 c c c d3" "d8 c c c d4" "d7 d7 d6 d5 d5";

        .casa {
          min-width: 37px;
          min-height: 37px;
        }

        .dezena-2 {
          flex-direction: row;
        }
        .dezena-7 {
          flex-direction: row-reverse;
        }
      }
    }
  }
</style>
