  <?php if (count($this->result_dbliste_legend)) { ?>
    <div id="dbis_legend">
      <h3>Zugriffsbedingungen</h3>
      <ul>
      <?php
        foreach ($this->result_dbliste_legend AS $entry) {
          echo '<li class="dbis_access_'.$entry['access_id'].'">'.$entry['text'].'</li>';
          //<img src="'.$this->tpl_dir.'default/img/dbis-list_'.$entry['access_id'].'.png">
        }
      ?>
      </ul>
    </div>
  <?php } ?>
  
    <div id="dbis_databases">
      <ul>
      <?php
        foreach ($this->result_dbliste AS $category => $entries) {
          if (strrpos($category, 'Gewählte Datenbanken') !== false || strrpos($category, 'TOP-Datenbanken') !== false) {
              $toggle = '';
              $list_class = 'dbis_category_sublist';
          } else {
              $toggle = '<span class="dbis_category_toggle" onMouseUp="showSubList(this);">[ZEIGEN]</span>';
              $list_class = 'dbis_category_sublist toggle_sublist';
          }
          echo '<li class="dbis_category">'.$category.$toggle.'<ul class="'.$list_class.'">';
  
          foreach ($entries AS $index => $db) {
            echo '<li class="dbis_access_'.$db['access_id'].'">'.$db['link'].'</li>';
            //<img src="'.$this->tpl_dir.'default/img/dbis-list_'.$db['access_id'].'.png">
          }
  
          echo '</ul></li>';
        }
      ?>
      </ul>
    </ul>
    </div>   
  </div>