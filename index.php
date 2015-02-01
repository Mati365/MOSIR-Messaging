<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="pl">
  <head>
    <title> MOSIR </title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/glyphicons.css">
    <!-- Wypukłe przyciski, Flat UI domyślnie -->
    <!-- <link rel="stylesheet" href="css/bootstrap-theme.min.css"> -->
    <link rel="stylesheet" href="css/datepicker.css">
    <link rel="stylesheet" href="css/main/main.css">

    <script src="js/require-config.js"></script>
    <script data-main="../main.js" src="js/require.js"></script>
    <!-- WYŁĄCZENIE ANIMACJI -->
    <script>
      require(['jquery', 'bootbox'], function($) {
        $.fx.off = true;
      });
    </script>
  </head>
  <body style="overflow-y:scroll;">
    <div id='printable' style='display:none'></div>
    <?php
      include   'lib/app.php';
    ?>
    <div id="non-printable" class="container" style="width: 1110px !important;">
      <!-- PRZED LOGOWANIEM -->
      <div class="row" <?php $app->page()->anonymRequired(); ?>>
          <?php
            $note = $app->sql()->queryObject("select * from `".\BitFlag\Messages::RELEASE_NOTES."` order by `version` desc limit 1");
            if(isset($note)) {
          ?>
            <div class="col-xs-offset-4 col-xs-4" style="margin-top: 10px">
              <div class="alert alert-success" role="alert">
                <?php
                  echo "<center><strong>Wersja {$note->version}:</strong></center>";
                  if(!empty($note->opts))
                    echo "{$note->opts}<br>";

                  unset($note->opts, $note->version, $note->id);
                  $translate = [
                    "fixed"         =>  "Naprawiono:",
                    "added"         =>  "Dodano:",
                    "known_issues"  =>  "Znane błędy:"
                  ];
                  foreach (get_object_vars($note) as $key => $val) {
                    $rows = \json_decode($val, true);
                    if(!$rows)
                      continue;

                    echo "<strong>{$translate[$key]}</strong><ul>";
                    foreach($rows as $row)
                      echo "<li>{$row}</li>";
                    echo "</ul><br>";
                  }
                ?>
              </div>
            </div>
          <?php
            }
          ?>
          <div class="col-xs-offset-5 col-xs-2">
            <h4 class="form-signin-heading">Zaloguj:</h4>
            <div class="alert alert-danger fade in" style="display: none" id="login-alert"></div>
            <form method="POST" action="" id="login-form">
              <div class="input-group" style="margin-bottom: 5px">
                <input type="text" class="form-control" id="login" placeholder="Login">
              </div>
              <div class="input-group" style="margin-bottom: 5px">
                <input type="password" class="form-control" id="password" placeholder="Hasło">
              </div>
              <div class="pull-right">
                <button type="submit" class="btn btn-danger" id="login-btn"><span class="glyphicon glyphicon-log-in"></span> Loguj</button>
              </div>
            </form>
          </div>
      </div>
      <!-- CIAŁO -->
      <div class="col-md-12 row-fluid">
        <?php 
          if($app->user()) 
            include 'page/user.php';
         ?>
      </div>
      <!-- STOPKA -->
      <div class="col-md-12">
        <hr>
        <p class='text-center'>
          Wersja programu: <strong><?php echo App\App::$VERSION; ?></strong>
          &nbsp;&nbsp;Ostatnia modyfikacja:  <strong><?php echo date("F d Y H:i:s", getlastmod()); ?></strong>
          &nbsp;&nbsp;<strong>Autor programu:</strong> Mateusz Bagiński(cziken58@gmail.com)
        </p>
        <p class='text-center'>
            <strong>Ikony</strong> stworzone i wspierane przez <a href="http://glyphicons.com">glyphicons.com</a> 
        </p>
      </div>
    </div>
  </body>
</html>