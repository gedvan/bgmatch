
export class FecthError extends Error {
  constructor(response) {
    super(response.statusText);
    this.response = response
  }
}

export default {

  apiUrl: window.location.protocol + '//' + window.location.host + '/api',

  ludopediaUrl: 'https://www.ludopedia.com.br',

  categoriasJogos: [
    {value: 'P', text: 'Pesado'},
    {value: 'M', text: 'Médio'},
    {value: 'L', text: 'Leve'},
    {value: 'F', text: 'Party game'},
    {value: 'I', text: 'Infantil'},
  ],

  urlJogoLudopedia: function (jogo) {
    return this.ludopediaUrl + '/jogo/' + jogo.slug;
  },

  nomeCategoria: function (key) {
    const find = this.categoriasJogos.findIndex(option => option.value === key);
    return find > -1 ? this.categoriasJogos[find].text : '';
  },

  /**
   * Wrapper para a função window.fetch(). Inclui automaticamente o token (JWT) de autenticação nos cabeçalhos
   * e também já faz a verificação da resposta (response.ok), lançando uma exceção em caso de erro.
   *
   * @param {string} path
   * @param {object} [opts]
   * @returns {Promise<Response>}
   */
  fetch: function (path, opts = {}) {
    const url = this.apiUrl + path;
    const token = window.localStorage.getItem('token');
    if (token) {
      const auth = 'Bearer ' + token;
      if (opts.headers instanceof Headers) {
        opts.headers.append('Authorization', auth);
      }
      else if (typeof opts.headers === 'object') {
        opts.headers.Authorization = auth;
      }
      else {
        opts.headers = {'Authorization': auth}
      }
    }
    return window.fetch(url, opts).then(response => {
      if (!response.ok) {
        if (response.status === 401) {
          // TODO: deslogar o usuário
        }
        throw new FecthError(response);
      }
      return response;
    });
  }

}
