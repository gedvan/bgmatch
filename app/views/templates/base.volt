<!doctype html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>BG Match</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {% block stylesheets %}
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
        <link rel="stylesheet/less" href="/css/styles.less">
        {% endblock %}
    </head>
    <body>
        <div id="all" class="container">
            <div class="row">

                <header id="header" class="col-sm-3">
                    <h1>BG Match</h1>
                    <div id="userinfo">
                      {% if loggedUser %}
                      Olá, {{ loggedUser.username }}!
                      <a href="/logout">Sair</a>
                      {% else %}
                      <a href="/login">Login</a>
                      {% endif %}
                    </div>
                </header>

                <main id="main" class="col-sm-9">
                  <div id="messages">
                    {{ flashSession.output() }}
                  </div>

                  {% block content %}{% endblock %}
                </main>

            </div>
        </div>

        {% block scripts %}
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/2.5.1/less.min.js"></script>
        {% endblock %}
    </body>
</html>
