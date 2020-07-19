<!DOCTYPE html>
<html>
<head>
  <meta charset="ISO-8859-1">
  <meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1">
  <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css" integrity="sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ" crossorigin="anonymous">
  <style>
   body {
    margin: 20px;
   }
  </style>
</head>
<body>
<a href="{home-url}" class="navbar-brand" title="{home-title}">
    <img height="100" src="{home-logo}" alt="{home-title}">
</a>
<form enctype="multipart/form-data" action="" method="POST" class="pure-form">
    <fieldset>
        <legend>GOOGLE GSUITE - EXTERNAL CONTACTS</legend>
        Select CSV file: <input name="file" type="file" />
        <input type="submit" class="pure-button pure-button-primary" value="UPLOAD"/>
    </fieldset>
</form>