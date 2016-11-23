<div class="s-detail animated delay9 fadeInUp" itemscope itemtype="http://schema.org/Book" vocab="http://schema.org/" typeof="Book">

  <!-- Book Cover
  ============================================= -->
  <div class="cover">
    <?php echo $image ?>
  </div>

  <!-- Title
  ============================================= -->
  <h3 class="s-detail-type"><?php echo $gmd_name ?></h3>
  <h4 class="s-detail-title" itemprop="name" property="name"><?php echo $title ?></h4>
  <?php if($sysconf['social_shares']) { echo $social_shares; } ?>
  <br>
  <div class="s-detail-author" itemprop="author" property="author" itemscope itemtype="http://schema.org/Person">
  <?php echo  $authors ?>
  <br>
  </div>
    <div>
      <em>&nbsp;</em>
      <br><br><br>
    </div>
  <!-- Availability
  ============================================= -->
  <h3><i class="fa fa-check-circle-o"></i> <?php echo __('Availability'); ?></h3>
  <?php echo ($availability) ? $availability : '<p class="s-alert">'.__('No copy data').'</p>'; ?>
  <br>

  <!-- Item Details
  ============================================= -->
  <h3><i class="fa fa-circle-o"></i> <?php echo __('Detail Information'); ?></h3>
  <div class="row">
  <div class="col-lg-6">
  <table class="s-table">
    <tbody>
      <!-- ============================================= -->
      <tr>
        <th><?php echo __('Call Number'); ?></th>
        <td>
          <div><?php echo ($call_number) ? $call_number : '-'; ?></div>
        </td>
      </tr>
      <!-- ============================================= -->
      <tr>
        <th><?php echo __('Publisher'); ?></th>
        <td>
          <span itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope><?php echo $publisher_name ?></span><?php if(!empty($publish_year)):?>: <span itemprop="datePublished" property="datePublished"><?php echo $publish_year ?></span><?php endif; ?>
        </td>
      </tr>
      <!-- ============================================= -->
      <tr>
        <th><?php echo __('Classification'); ?></th>
        <td>
          <div><?php echo ($classification) ? $classification : '-'; ?></div>
        </td>
      </tr>
      <!-- ============================================= -->
    </tbody>
  </table>
  </div>
  <div class="col-lg-6">
  <table class="s-table">
    <tbody>
    <?php 
    include_once MDLBS.'bibliography'.DS.'custom_fields.inc.php'; 
    $biblio_custom = $this->db->query('SELECT * FROM biblio_custom WHERE biblio_id=' . intval($biblio_id))->fetch_assoc();
    ?>
    <?php foreach ($biblio_custom_fields as $custom_field): ?>
      <!-- ============================================= -->
      <tr>
        <th><?php echo $custom_field['label']; ?></th>
        <td>
          <div><?php
          $value = '-';
          if($custom_field['type'] === 'choice') {
              foreach($custom_field['data'] as $data) {
                  if($data[0] == $biblio_custom[$custom_field['dbfield']]) {
                      $value = $data[1];
                  }
              }
          } else {
            if($biblio_custom[$custom_field['dbfield']] !== '' && !is_null($biblio_custom[$custom_field['dbfield']])) {
                $value = $biblio_custom[$custom_field['dbfield']];
            }
          }
          echo $value;
            ?></div>
        </td>
      </tr>
      <!-- ============================================= -->
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  </div>

  <!-- Related biblio data
  ============================================= -->
  <h3><i class="fa fa-circle-o"></i> <?php echo __('Other version/related'); ?></h3>
  <?php echo ($related) ? $related : '<p class="s-alert">'.__('No other version available').'</p>'; ?>
  <br>

  <?php if ($file_att) : ?>
  <!-- Attachment
  ============================================= -->
  <h3><i class="fa fa-arrow-circle-o-down"></i> <?php echo __('File Attachment'); ?></h3>
  <div itemprop="associatedMedia">
    <div class="s-download">
      <?php echo $file_att; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Comment
  ============================================= -->
  <?php if(isset($_SESSION['mid']) && $sysconf['comment']['enable']) : ?>
  <h3><i class="fa fa-comments-o"></i> <?php echo __('Comments'); ?></h3>
  <?php echo showComment($biblio_id); ?>
  <?php endif; ?>

</div>