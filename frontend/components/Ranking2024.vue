<script>
import BGMatch from "../BGMatch";
import Marcador from "./Marcador.vue";
import Meeple from "./Meeple.vue";
import GraficoRanking2019 from "./GraficoRanking2019.vue";

export default {
  components: {
    Marcador,
    Meeple,
    GraficoRanking2019
  },

  props: {
    ano: Number
  },

  data() {
    return {
      tabelaPontuacao: [
        {"P": 10, "M": 7, "L": 4},  // 0: Posição 1
        {"P": 7,  "M": 4, "L": 2},  // 1: Posição 2
        {"P": 4,  "M": 2, "L": 1},  // 2: Posição 3
        {"P": 2,  "M": 1, "L": 1},  // 2: Posição 4
        {"P": 1,  "M": 1, "L": 1},  // 2: Posição 5
        {"P": 1,  "M": 1, "L": 1}   // 2: Posição 6
      ],
      trilha: [],
      partidas: [],
      jogadoresMes: [],
      jogadoresAno: [],
      dataLoaded: false,
      meses: [],
      mes: 0,
    }
  },

  created() {
    window.addEventListener('resize', this.atualizaTrilha);
  },

  destroyed() {
    window.removeEventListener('resize', this.atualizaTrilha);
  },

  mounted() {
    this.inicializaMeses();
    this.fetchDados();
  },

  computed: {
    categorias() {
      return BGMatch.categoriasJogos.filter(c => c.key !== 'Y');
    }
  },

  methods: {
    /**
     * Retorna a pontuação de uma determinada posição e categoria de jogos, de
     * acordo com a tabela de pontuação do ranking.
     *
     * @param posicao
     * @param categoria
     * @returns {number}
     */
    getPontuacao(posicao, categoria) {
      if (categoria === 'F' || categoria === 'I') {
        categoria = 'Y';
      }
      const p = posicao - 1;
      if (typeof this.tabelaPontuacao[p] === 'undefined' || typeof this.tabelaPontuacao[p][categoria] === 'undefined') {
        return 0;
      }
      return this.tabelaPontuacao[p][categoria];
    },

    inicializaMeses() {
      const hoje = new Date();
      const anoAtual = hoje.getUTCFullYear();
      const mesAtual = hoje.getUTCMonth();
      const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
        "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
      let ultimoConcluido = 0;

      this.meses = meses.map((mes, i) => {
        const concluido = anoAtual > this.ano || mesAtual > i;
        if (concluido) {
          ultimoConcluido = i;
        }
        return {
          nome: mes,
          partidas: [],
          concluido: concluido,
        }
      });
      this.mes = ultimoConcluido;
    },

    /**
     * Consulta os dados necessários no back-end para montar o componente.
     */
    fetchDados() {
      this.fetchJogadores()
        .then(() => this.fetchPartidas())
        .then(() => {
          this.atualizaPontosDoMes();
          this.atualizaPontosDoAno();
          //this.dataLoaded = true;
        })
    },

    /**
     * Consulta os jogadores cadastrados no BD.
     */
    fetchJogadores() {
      return BGMatch.fetch('/jogadores')
        .then(response => response.json())
        .then(jogadores => {
          this.jogadoresMes = jogadores.map(j => ({
            id: j.id,
            nome: j.nome,
            cor: j.cor,
            pontos: 0,
          }));
          this.jogadoresAno = jogadores.map(j => ({
            id: j.id,
            nome: j.nome,
            cor: j.cor,
            pontosTotal: 0,
            pontosMeses: new Array(12).fill(0),
          }));
        })
        .catch(error => {
          alert('Ocorreu um erro ao obter os dados dos jogadores.');
          if (process.env.NODE_ENV === 'development') {
            console.error(error);
          }
        });
    },

    /**
     * Consulta a lista de partida no ano.
     */
    fetchPartidas() {
      return BGMatch.fetch('/partidas-periodo/' + this.ano + '?sort=asc')
        .then(response => response.json())
        .then(partidas => {
          for (const partida of partidas) {
            // Data da partida no formato "MM-DD".
            const data = partida.data.substring(5);

            // Mes da partida como inteiro (0-indexed).
            const mes = parseInt(partida.data.substring(5, 7)) - 1;

            this.meses[mes].partidas.push(partida);
          }
        })
        .catch(error => {
          alert('Ocorreu um erro ao obter os dados do ranking.');
          if (process.env.NODE_ENV === 'development') {
            console.error(error);
          }
        });
    },

    mudaMes(mes) {
      this.mes = mes;
      this.atualizaPontosDoMes();
    },

    atualizaPontosDoMes() {
      for (const jogador of this.jogadoresMes) {
        jogador.pontos = 0;
      }
      for (const partida of this.meses[this.mes].partidas) {
        for (const jogadorPartida of partida.jogadores) {
          const pontos = this.getPontuacao(jogadorPartida.posicao, partida.jogo.categoria);
          this.jogadoresMes.find(j => j.id === jogadorPartida.id).pontos += pontos;
        }
      }
      this.jogadoresMes.sort((a, b) => b.pontos - a.pontos);

      this.atualizaTrilha();
    },

    /**
     * Atualiza a posição do jogador na trilha de pontos.
     */
    atualizaTrilha() {
      const trilha = this.$refs.trilha;
      const casas = [];
      for (const jogador of this.jogadoresMes) {
        const numCasa = jogador.pontos % 50;
        const casa = trilha.querySelector('.casa-' + numCasa);
        const marcador = trilha.querySelector(`.marcadores svg[data-id-jogador="${jogador.id}"]`);

        const left = casa.offsetLeft + ((casa.offsetWidth - marcador.clientWidth) / 2);
        marcador.style.left = left + 'px';

        let top = casa.offsetTop + ((casa.offsetHeight - marcador.clientHeight) / 2);
        const rep = casas.filter(c => c === numCasa).length;
        if (rep > 0) {
          top -= rep * 8;
        }
        marcador.style.top = top + 'px';

        casas.push(numCasa);
      }
    },

    atualizaPontosDoAno() {
      this.meses.filter(m => m.concluido).forEach((mes, m) => {
        const jogadores = [];
        for (const partida of mes.partidas) {
          for (const jogadorPartida of partida.jogadores) {
            const pontos = this.getPontuacao(jogadorPartida.posicao, partida.jogo.categoria);
            const jogador = jogadores.find(j => j.id === jogadorPartida.id);
            if (jogador) {
              jogador.pontos += pontos;
              jogador.partidas++;
              if (jogadorPartida.posicao === 1) {
                jogador.vitorias++;
              }
            } else {
              jogadores.push({id: jogadorPartida.id, pontos, vitorias: 0, partidas: 0, pv: 0});
            }
          }
        }

        // Ordena a lista dos jogadores do mês pela quantidade de pontos, em
        // seguida pelo número de vitórias e, por fim, pelo número de partidas
        // (menos partidas é melhor).
        jogadores.sort((a, b) => {
          if (b.pontos !== a.pontos) {
            return b.pontos - a.pontos;
          }
          if (b.vitorias !== a.vitorias) {
            return b.vitorias - a.vitorias;
          }
          return a.partidas - b.partidas;
        });

        for (let i = 0; i < 3; i++) {
          if (jogadores[i]) {
            const pontos = 3 - i;
            const jogador = this.jogadoresAno.find(j => j.id === jogadores[i].id);
            jogador.pontosTotal += pontos;
            jogador.pontosMeses[m] = pontos;
          }
        }
      });

      this.jogadoresAno.sort((a, b) => b.pontosTotal - a.pontosTotal);
    }
  }
}
</script>

<template>
  <div class="ranking ranking-2024">

    <h3>Pontuação Mensal</h3>

    <b-nav pills class="mb-3 nav-meses">
      <b-nav-item v-for="(m, i) in meses" :key="i" :active="i === mes" :disabled="m.partidas.length === 0 && m.concluido === false" v-on:click="mudaMes(i)">
        {{m.nome.substring(0, 1)}}<span class="three">{{m.nome.substring(1, 3)}}</span><span class="end">{{m.nome.substring(3)}}</span>
      </b-nav-item>
    </b-nav>

    <div class="trilha-pontos trilha-50" ref="trilha">
      <div v-for="n in Array(10).keys()" :key="n" :class="['grupo', 'grupo-' + n]">
        <div v-for="m in Array(5).keys()" :key="m" :class="['casa', `casa-${n * 5 + m}`, `und-${m}`]">
          {{n * 5 + m}}
        </div>
      </div>
      <div class="marcadores">
        <Marcador v-for="jogador in jogadoresMes" :key="jogador.id" :class="['marcador']"
                  :color="jogador.cor" :numero="jogador.pontos" :data-id-jogador="jogador.id"
                  v-b-popover.hover.click.top="`${jogador.nome} (${jogador.pontos})`"></Marcador>
      </div>
      <div class="info">
        <div class="row">
          <div class="col-lg">

            <table v-if="jogadoresMes.length > 0" class="table table-pontuacao">
              <colgroup>
                <col class="col-jogador">
              </colgroup>
              <thead>
              <tr>
                <th>Jogador</th><th class="text-center">Pontos</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="jogador in jogadoresMes" :key="jogador.id">
                <td>
                  <Marcador class="marcador" :color="jogador.cor"></Marcador>
                  {{ jogador.nome }}
                </td>
                <td class="text-center">{{ jogador.pontos }}</td>
              </tr>
              </tbody>
            </table>

          </div>
          <div class="col-lg">

            <table class="table">
              <colgroup>
                <col class="col-posicao">
              </colgroup>
              <thead>
              <tr>
                <th scope="col">Posição / Pontuação</th>
                <th scope="col" v-for="categoria in categorias" class="text-center">{{ categoria.label }}</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(pontuacao, index) in tabelaPontuacao">
                <td>{{ index + 1 }}º lugar</td>
                <td v-for="categoria in categorias" class="text-center">{{ pontuacao[categoria.key] }}</td>
              </tr>
              </tbody>
            </table>

          </div>
        </div>

        <grafico-ranking2019 v-if="dataLoaded" :partidas="partidas" :jogadores="jogadoresMes" periodo="semanal"></grafico-ranking2019>

      </div>
    </div>

    <h3>Pontuação Final</h3>

    <table v-if="jogadoresAno.length > 0" class="table table-bordered tabela-geral">
      <colgroup>
        <col class="col-jogador">
      </colgroup>
      <thead>
      <tr>
        <th scope="col" class="col-jogador">
          <span class="nome-jogador">Jogador</span>
        </th>
        <th scope="col" v-for="(m, i) in meses" :key="i" class="col-estrelas">
          {{m.nome.substring(0, 1)}}<span class="three">{{m.nome.substring(1, 3)}}</span><span class="end">{{m.nome.substring(3)}}</span>
        </th>
        <th scope="col" class="col-total">
          T<span class="three">otal</span>
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="jogador in jogadoresAno" :key="jogador.id">
        <td class="col-jogador">
          <Meeple class="meeple" :cor="jogador.cor"></Meeple>
          <span class="nome-jogador">{{ jogador.nome }}</span>
        </td>
        <td v-for="(m, i) in meses" :key="i" class="col-estrelas">
          <img v-for="(n, i) in Array(jogador.pontosMeses[i])" :key="i" src="/images/estrela.svg" class="estrela" alt="Estrela"/>
        </td>
        <td class="col-total">
          {{jogador.pontosTotal}}
        </td>
      </tr>
      </tbody>
    </table>

  </div>
</template>

<style scoped lang="scss">

</style>
