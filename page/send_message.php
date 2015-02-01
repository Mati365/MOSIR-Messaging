 <div class="row" style="margin-top: 20px;">
  <div class="col-xs-12" style="margin: 0px; padding: 0px;">
    <div class="col-xs-2 form-group">
      <!-- OBIEKT -->
      <div class="col-xs-12 form-group">
        <label for="email">
          <span class="glyphicon glyphicon-home"></span> Obiekt:
        </label>
        <select class="form-control" id="mail-object">
          <?php 
            echo $app->page()->listLockedObjects(
                          $app->settings()['object_filter']);
          ?>
        </select>
      </div>

      <!-- REGION -->
      <div class="col-xs-12 form-group">
        <label for="email" id="zone">
          <span class="glyphicon icon-g-globe"></span> Strefa:
        </label>
        <select class="form-control" id="mail-zone">
        </select>
      </div>

      <!-- PRZYCISK WYSŁANIA -->
      <div class="col-xs-12 form-group">
        <button type="submit" class="btn btn-danger" id="send-msg-btn">
          <span class="glyphicon glyphicon-upload"></span> Wyślij wiad.
        </button>
      </div>
    </div>

    <!-- POLE ZGŁOSZENIOWE -->
    <div class="col-xs-6 form-group">
      <!-- EKRAN WYSYŁANIA -->
      <label for="comment">
        <span class="glyphicon glyphicon-envelope"></span> Zgłoszenie:
      </label>
      <div id="report-comment-box">
        <!-- <textarea class="form-control" rows="8" id="mail-comment"></textarea> -->
        <div contenteditable="true" id="mail-comment" class="col-md-12 form-control"></div>
        
        <!-- COFANIE ZMIAN/WKLEJANIE -->
        <span class="pull-right btn-group">
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('undo');">
            <span class="glyphicon icon-g-unshare"></span>
          </button>
        </span>

        <span class="pull-right btn-group" style="margin-right: 5px">
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('insertimage', 0, prompt('Adres URL grafiki(np. ifotos)'));">
            <span class="glyphicon icon-g-picture"></span>
          </button>
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('insertunorderedlist');">
            <span class="glyphicon icon-g-list"></span>
          </button>
        </span>

        <!-- POGRUBIENIE itp -->
        <span class="pull-right btn-group" style="margin-right: 5px">
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('bold');">
            <span class="glyphicon glyphicon-bold"></span>
          </button>
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('italic');">
            <span class="glyphicon glyphicon-italic"></span>
          </button>
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('underline');">
            <span class="glyphicon icon-g-text-underline"></span>
          </button>
        </span>

        <!-- JUSTIFY -->
        <span class="pull-right btn-group" style="margin-right: 5px">
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('justifyleft');">
            <span class="glyphicon glyphicon-align-left"></span>
          </button>
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('justifycenter');">
            <span class="glyphicon glyphicon-align-center"></span>
          </button>
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('justifyright');">
            <span class="glyphicon glyphicon-align-right"></span>
          </button>
          <button type="button" class="btn btn-default message-editor-button" 
                onclick="document.execCommand('justifyfull');">
            <span class="glyphicon glyphicon-align-justify"></span>
          </button>
        </span>
      </div>
    </div>
    <!-- POLE ODBIORCY -->
    <div class="col-xs-4 form-group">
      <div class="col-xs-12 form-group">
        <label>
          <span class="glyphicon glyphicon-user"></span> Odbiorca:
        </label>
        <select class="form-control" id="mail-receiver" size="5">
        <?php
          echo $app->page()->listUsers(0);
        ?>
        </select>
      </div>

      <div class="col-xs-12 form-group">
        <label>
          <span class="glyphicon glyphicon-folder-open "></span> Grupy:
        </label>
        <select class="form-control" id="mail-group">
        <?php
          echo $app->page()->listSqlEnum([], \BitFlag\Messages::GROUPS_TABLE);
        ?>
        </select>
      </div>

    </div>
  </div>
</div>