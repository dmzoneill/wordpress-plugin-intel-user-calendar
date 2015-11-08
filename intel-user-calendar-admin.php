<?php

if( isset( $_GET[ 'tab' ] ) ) 
{
  $active_tab = $_GET[ 'tab' ];
} 
else 
{
	$active_tab = 'simple';
}

?>

<div class="wrap">
  <div id="icon-themes" class="icon32"></div>
  <h2>Intel User Calendar</h2>

  <h2 class="nav-tab-wrapper">
    <a href="<?php echo add_query_arg( array('tab' => 'simple'), $_SERVER['REQUEST_URI'] ); ?>" class="nav-tab <?php echo $active_tab == 'simple' ? 'nav-tab-active' : ''; ?>">Simple</a>
    <a href="<?php echo add_query_arg( array('tab' => 'help'), $_SERVER['REQUEST_URI'] ); ?>" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
  </h2>

  <form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <?php wp_nonce_field( 'save_sll_settings','save_the_sll' ); ?>

    <?php if( $active_tab == "simple" ): ?>
    <h3>Settings</h3>
    <p>Configure basic settings of the plug-in</p>
    <table class="form-table">
      <tbody>
      <tr>
          <th scope="row" valign="top">American Public Holidays</th>
          <td>
          <textarea rows="20" cols="50" name="<?php echo $this->get_field_name('american_holidays'); ?>">
<?php echo join('\n', (array) Intel_User_Calendar_Plugin::getInstance()->get_setting('american_holidays') ); ?> 
          </textarea><br/>
          one date per line eg. YYYY/MM/DD
        </td>
      </tr>			
      </tbody>
    </table>
    <p><input class="button-primary" type="submit" value="Save Settings" /></p>    	
    <?php else: ?>
    <h3>Help</h3>
    <p>Contact david.m.oneill@intel.com for more information</p>
    <?php endif; ?>
  </form>
</div>

