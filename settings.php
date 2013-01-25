<div class="wrap">
<h2>Append Link on Copy Options</h2>
<p>Options relating to the Append Link on Copy Plugin.</p>
<form action="options.php" method="post">
<input name="Submit" type="submit" class="button button-primary action" value="<?php esc_attr_e('Save Changes'); ?>" />
<?php settings_fields('append_link_on_copy_options'); ?>
<?php do_settings_sections('append_link_on_copy_options'); ?>

<input name="Submit" type="submit" class="button button-primary action" value="<?php esc_attr_e('Save Changes'); ?>" />
</form>
</div>