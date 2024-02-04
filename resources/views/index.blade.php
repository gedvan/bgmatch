<!doctype html>
<html lang="pt-br">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="theme-color" content="#00BAA7">

  <base href="/">

  <title>{{ config('app.name') }}</title>
</head>
<body>

<div id="app"></div>

<script src="main.js{{ $build ? '?'.$build : '' }}"></script>

</body>
</html>
