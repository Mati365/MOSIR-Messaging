<div role="tabpanel">
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <!-- Zakładki -->
    <li class="active">
      <a href="#messages-tab" data-toggle="tab">
        <span class='glyphicon glyphicon-save'></span> Odebrane
        <span class="badge" id="msg-inbox-badge"></span>
      </a>
    </li>
    <li>
      <a href="#sent-tab" data-toggle="tab">
        <span class='glyphicon glyphicon-send'></span> Wysłane
        <span class="badge" id="msg-outbox-badge"></span>
      </a>
    </li>
    <li>
      <a href="#send-tab" data-toggle="tab">
        <span class='glyphicon glyphicon-fire'></span> Zgłoszenie
      </a>
    </li>
    <?php if($app->admin()) { ?>
      <li>
        <a href="#admin-tab" data-toggle="tab">
          <span class='glyphicon glyphicon-wrench'></span> Admin
        </a>
      </li>
    <?php } ?>

    <!-- Info o zalogowaniu -->
    <li class="pull-right" style="padding: 2px">
      <span class="col-xs-0 col-md-*">Zalogowano jako:</span> 
      <!-- Single button -->
      <span class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle inbox-header-btn" data-toggle="dropdown" aria-expanded="false">
          <?php echo '<b>'.$app->user()->login.'</b>'; ?> 
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li>
            <a href="javascript:;" id="user-info-btn">
              <i class="glyphicon glyphicon-question-sign"></i>   
              O użytkowniku
            </a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="javascript:;" id="change-password-btn">
              <i class="glyphicon glyphicon-lock"></i>   
              Zmień hasło
            </a>
          </li>
        </ul>
      </span>
      <button type="submit" class="btn btn-danger inbox-header-btn" style="margin-left: 8px;" id="logout-btn">
        <span class='glyphicon glyphicon-log-out'></span>
        Wyloguj
      </button>
      <!-- Informacje o zalogowanym -->
      <script>
        /* Używane tylko do renderingu */
        require(['login'], function(_) {
          _.user_info = {
            login     :  '<?php echo $app->user()->login; ?>',
            name      :  '<?php echo $app->user()->name; ?>',
            surname   :  '<?php echo $app->user()->surname; ?>'
          };
        });
      </script>
    </li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <!-- Panel skrzynki odpowiedzi -->
    <div role="tabpanel" class="tab-pane fade active" style="opacity: 1.0" id="messages-tab">
      <?php $app['Core\Inbox']->render(\BitFlag\InboxFlag::RENDER_RECEIVED, 'msg-inbox', 'inbox-row'); ?>
    </div>

    <!-- Panel skrzynki odpowiedzi -->
    <div role="tabpanel" class="tab-pane fade" id="sent-tab">
      <?php $app['Core\Inbox']->render(\BitFlag\InboxFlag::RENDER_SENT, 'msg-outbox', 'inbox-row'); ?>
    </div>

    <!-- Panel wysyłania wiadomości -->
    <div role="tabpanel" class="tab-pane fade" id="send-tab">
      <?php include 'page/send_message.php'; ?>
    </div>

    <!-- Panel administracyjny -->
    <?php 
      if($app->admin()) {
        echo '<div role="tabpanel" class="tab-pane fade" id="admin-tab">';
          include 'page/admin.php';
        echo '</div>';
      }
    ?>
  </div>
</div>
<!-- SZABLONY -->
<div style="display:none">
  <!-- INFORMACJE O UŻYTKOWNIKU -->
  <div id="user-info-template">
    <div class="row" style="margin-bottom: 10px;">
      <div class="col-xs-4 col-xs-offset-4" id="user-avatar">
      </div>
    </div>
    <div class="row">
      <div class="col-xs-4"><label>Typ konta:</label></div>
      <div class="col-xs-*" style="font-weight: bold"><?php echo $app->user()->type==\BitFlag\UserType::WRITER?'Z ograniczeniami':'Bez ograniczeń'; ?></div>
    </div>
    <?php {
      function renderOpts($vals) {
        global $app;
        foreach($vals as $label => $user_field) {
          $val = $app->user()->$user_field[0];
          echo "<div class='row'>
                  <div class='col-xs-4'><label>{$label}:</label></div>
                  <div class='col-xs-*'>{$val}</div>
                </div>".
                ($user_field[1]?'<hr style="margin-top:5px;margin-bottom:15px;">':'');
        }
      }
      renderOpts([
        'Login'      =>  ['login',   false ],
        'Rejestracja'=>  ['reg_date',true  ],
        'Imię'       =>  ['name',    false ],
        'Nazwisko'   =>  ['surname', false ],
        'Stanowisko' =>  ['job',     false ]
      ]);
    } ?>
  </div>
</div>
<!-- 
  W razie pierwszego logowania użytkownika
  dialog o zmianie hasła zostaje wyrenderowany
-->
<?php 
  $settings = $app->settings();
  if(!$settings['first_login']) {
?>
    <script>
      require(['jquery', 'dom_ready'], function($, _dom_ready) {
        _dom_ready(function() {
          /* Jeśli logowany pierwszy raz */
          $('#change-password-btn').click();
        });
      });
    </script>
<?php
    $settings['first_login']=1;
  }
?>