{% extends "templates/base.volt" %}

{% block content %}
    <h1>Login</h1>

    {{ form('login') }}

        {{ text_field('name') }}
        {{ submit_button('Entrar') }}

    {{ end_form() }}
{% endblock %}

