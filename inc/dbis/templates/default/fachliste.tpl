  <ul id="dbis_fachliste">
    <?php
      foreach ($this->result_fachliste AS $subject) {
        echo '<li>'.$subject['link'].' ('.$subject['hits'].')</li>';
      }
    ?>
  </ul>