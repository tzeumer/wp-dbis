  <!--
  Take the default layout: 
  * $this->result_detail['default']
  
  Put stuff wherever you want
  * $this->result_detail['row']['detail_more_titles']['row_id']
  * $this->result_detail['row']['detail_more_titles']['heading']
  * $this->result_detail['row']['detail_more_titles']['content']
  * $this->result_detail['row']['detail_more_titles']['html']
  => detail_more_titles is the id from dbis. Possible id's
  * 'detail_more_titles', 'detail_start', 'detail_access', 'detail_hints', 
    'detail_content', 'detail_subjects', 'detail_keywords', 'detail_appearence', 
    'detail_db_types', 'detail_publisher', 'detail_remarks'
  -->
  <div id="dbis_detail">
  <?php
    echo $this->result_detail['default'];
  ?>
  </div>