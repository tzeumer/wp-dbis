<link href="<?php echo $this->tpl_dir.'default/css/dbis.css'; ?>" type="text/css" rel="stylesheet">
<script type="text/javascript" src="<?php echo $this->tpl_dir.'default/js/jquery-2.1.1.min.js'; ?>"></script>
<script type="text/javascript" rel="javascript">
function showSubList(obj) {
    $('.toggle_sublist').hide(300);
    $(obj).parent().children('.toggle_sublist').show();
    $('.dbis_category_toggle').text('[Zeigen]');
    if ($(obj).text() === '[Zeigen]') {$(obj).text('[Verbergen]')};
};
</script>

<div id="dbis_wrapper">
  <div id="dbis_header">
  <?php
      $menu = '<p>';
      $menu .= $this->link_home;
      $menu .= ' | '.$this->link_vanilla;
      $menu .= ' | '.$this->link_collections;
      $menu .= ' | '.$this->link_new;
      $menu .= ' | '.$this->link_free_dbs;
      $menu .= ' | '.$this->link_advanced_search;
      $menu .= '</p>';  
      $menu .= $this->form_search;
      
      echo $menu;
  ?>
  </div>
  <h2><?php echo $this->result_headline; ?></h2>
      
      