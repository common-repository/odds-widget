<?php add_thickbox(); ?>
<h2>Widgets</h2>
<?php
global $wpdb;
$sql = "SELECT * FROM $wpdb->prefix"."ow_widgets WHERE status = 1 ORDER BY updated DESC";
$rows = $wpdb->get_results($sql);
if ($rows) {
?>
<table class="widefat">
	<thead>
		<tr>
			<th>Name</th>
			<th>Shortcode</th>
			<th>Date Created</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($rows as $row) { ?>
		<tr id="widget<?php echo $row->id; ?>">
			<td><a href="admin.php?page=ow-preview&id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a></td>
			<td><code>[oddswidget id="<?php echo $row->id; ?>"]</code></td>
			<td><?php echo date('d M Y H:i', strtotime($row->created)); ?></td>
			<td><a href="admin.php?page=ow-new-widget&id=<?php echo $row->id; ?>">Edit</a> | <a class="ow-delete" data-id="<?php echo $row->id; ?>" data-code="<?php echo $row->code; ?>">Delete</a></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<br />
<a href="admin.php?page=ow-new-widget" class="button-primary">New Widget</a>
<?php } else { ?>
<p>You don't currently have any widgets, click the button below to create a new one.</p>
<a href="admin.php?page=ow-new-widget" class="button-primary">New Widget</a>
<?php } ?>