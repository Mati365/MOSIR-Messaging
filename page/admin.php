 <div class="container-fluid">
  <div class="row">
     <ul class="nav nav-tabs" id="dashboard_tabs">
      <li class="active">
        <a href="#admin-users-tab" data-toggle="tab">
          <span class="glyphicon glyphicon-user"></span>
          <b>Użytkownicy</b>
        </a>
      </li>
      <li>
        <a href="#admin-objects-tab" data-toggle="tab">
          <span class="glyphicon glyphicon-home"></span>
          <b>Obiekty</b>
        </a>
      </li>
      <li>
        <a href="#admin-zones-tab" data-toggle="tab">
          <span class="glyphicon glyphicon-shopping-cart"></span>
          <b>Strefy</b>
        </a>
      </li>
    </ul>
    <!-- Panele -->
    <div class="tab-content">
      <!-- Użytkownicy -->
      <div role="tabpanel" class="tab-pane fade active" style="opacity: 1.0; margin-top: 15px" id="admin-users-tab">
        <?php echo $app->settingsPage()->render(\BitFlag\Messages::USERS_TABLE); ?>
      </div>
      <!-- Obiekty -->
      <div role="tabpanel" class="tab-pane fade" id="admin-objects-tab" style="margin-top: 15px">
        <?php echo $app->settingsPage()->render(\BitFlag\Messages::OBJECTS_TABLE); ?>
      </div>
      <!-- Obiekty -->
      <div role="tabpanel" class="tab-pane fade" id="admin-zones-tab" style="margin-top: 15px">
        <?php echo $app->settingsPage()->render(\BitFlag\Messages::ZONES_TABLE); ?>
      </div>
    </div>
  </div>
</div>