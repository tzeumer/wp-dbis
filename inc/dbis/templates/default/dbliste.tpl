    <p><?php echo $this->link_dblink_sort; ?></p>
  <?php if (count($this->result_dbliste_legend)) { ?>
    <div id="dbis_legend">
      <h3><?php echo $this->cfg_lng['dbat_terms'] ?></h3>
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
          if (strrpos($category, 'Gewählte Datenbanken') !== false 
                  || strrpos($category, 'TOP-Datenbanken') !== false
                  || strrpos($category, 'Gesamtangebot') !== false
                  || strrpos($category, 'Suche') !== false) {
              $toggle = '';
              $list_class = 'dbis_category_sublist';              
          } else {
              $toggle = '<span class="dbis_category_toggle" onMouseUp="showSubList(this);">['.$this->cfg_lng['catToggle_show'].']</span>';
              $list_class = 'dbis_category_sublist toggle_sublist';
          }
          
          // Nasty language hack
          if (isset($this->cfg_lng['Gewählte Datenbanken'])) $category = str_replace('Gewählte Datenbanken', $this->cfg_lng['Gewählte Datenbanken'], $category);
          if (isset($this->cfg_lng['TOP-Datenbanken'])) $category = str_replace('TOP-Datenbanken', $this->cfg_lng['TOP-Datenbanken'], $category);
          if (isset($this->cfg_lng['Gesamtangebot'])) $category = str_replace('Gesamtangebot', $this->cfg_lng['Gesamtangebot'], $category); 
          if (isset($this->cfg_lng['Suche'])) $category = str_replace('Suche', $this->cfg_lng['Suche'], $category); 

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